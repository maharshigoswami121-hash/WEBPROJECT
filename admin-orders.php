<?php
session_start();
include 'Database/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: admin-login.php');
    exit;
}

$title = "View Orders";
include 'includes/header.php';

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int) $_POST['order_id'];
    $status = escapeInput($_POST['status']);
    $update_query = "UPDATE orders SET status = '$status' WHERE id = $order_id";
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success_message'] = 'Order status updated successfully.';
    } else {
        $_SESSION['error_message'] = 'Error updating order status.';
    }
    header('Location: admin-orders.php');
    exit;
}

// Fetch all orders - handle missing created_at column
$order_columns_query = "SHOW COLUMNS FROM orders";
$order_columns_result = mysqli_query($conn, $order_columns_query);
$has_created_at = false;
if ($order_columns_result) {
    while ($col = mysqli_fetch_assoc($order_columns_result)) {
        if ($col['Field'] === 'created_at') {
            $has_created_at = true;
            break;
        }
    }
    mysqli_data_seek($order_columns_result, 0);
}

$orderBy = $has_created_at ? "ORDER BY o.created_at DESC" : "ORDER BY o.id DESC";
$query = "SELECT o.*, u.first_name, u.last_name, u.email as user_email 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          $orderBy";
$result = mysqli_query($conn, $query);
$orders = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Get order items
        $items_query = "SELECT * FROM order_items WHERE order_id = " . $row['id'];
        $items_result = mysqli_query($conn, $items_query);
        $row['items'] = [];
        if ($items_result) {
            while ($item = mysqli_fetch_assoc($items_result)) {
                $row['items'][] = $item;
            }
        }
        $orders[] = $row;
    }
}

$success = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

$status_colors = [
    'pending' => 'warning',
    'processing' => 'info',
    'shipped' => 'primary',
    'delivered' => 'success',
    'cancelled' => 'danger'
];
?>

<div class="min-vh-100" style="background-color: #f8f9fa;">
    <div class="bg-white border-bottom shadow-sm">
        <div class="container-fluid px-4 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold mb-0" style="color: #667eea;">View Orders</h4>
                <a href="admin-dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        class="text-muted mb-3">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <h5 class="text-muted">No orders found</h5>
                    <p class="text-muted mb-0">Orders will appear here once customers place them.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-4">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-1">Order #<?php echo htmlspecialchars($order['order_number']); ?></h5>
                                <p class="text-muted mb-0 small">
                                    <strong>Customer:</strong>
                                    <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                    (<?php echo htmlspecialchars($order['user_email']); ?>)
                                </p>
                                <p class="text-muted mb-0 small">
                                    <strong>Date:</strong> <?php
                                    if (isset($order['created_at']) && !empty($order['created_at'])) {
                                        echo date('M d, Y H:i', strtotime($order['created_at']));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <div class="mb-2">
                                    <span class="badge bg-<?php echo $status_colors[$order['status']]; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                                <div>
                                    <strong class="fs-5" style="color: #667eea;">
                                        $<?php echo number_format($order['total_amount'], 2); ?>
                                    </strong>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <?php foreach ($status_colors as $status => $color): ?>
                                            <option value="<?php echo $status; ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($status); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </div>
                        </div>

                        <div class="border-top pt-3">
                            <h6 class="fw-semibold mb-3">Order Items:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order['items'] as $item):
                                            // Get product name - try from order_items first, then from products table
                                            $product_name = isset($item['product_name']) ? $item['product_name'] : 'Product #' . $item['product_id'];
                                            if (empty($item['product_name']) && isset($item['product_id'])) {
                                                $prod_query = "SELECT name FROM products WHERE id = " . (int) $item['product_id'];
                                                $prod_result = mysqli_query($conn, $prod_query);
                                                if ($prod_result && mysqli_num_rows($prod_result) > 0) {
                                                    $prod_row = mysqli_fetch_assoc($prod_result);
                                                    $product_name = $prod_row['name'];
                                                }
                                            }

                                            // Get product price - try from order_items first, then use unit_price
                                            $product_price = isset($item['product_price']) ? $item['product_price'] : (isset($item['unit_price']) ? $item['unit_price'] : 0);

                                            // Get subtotal - try from order_items first, then calculate
                                            $subtotal = isset($item['subtotal']) ? $item['subtotal'] : (isset($item['total_price']) ? $item['total_price'] : ($product_price * $item['quantity']));
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product_name); ?></td>
                                                <td>$<?php echo number_format($product_price, 2); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td class="text-end">$<?php echo number_format($subtotal, 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-semibold mb-2">Shipping Address:</h6>
                                    <p class="mb-0 small">
                                        <?php
                                        $shipping_name = '';
                                        if (isset($order['shipping_firstname']) && isset($order['shipping_lastname'])) {
                                            $shipping_name = htmlspecialchars($order['shipping_firstname'] . ' ' . $order['shipping_lastname']);
                                        } elseif (isset($order['shipping_name'])) {
                                            $shipping_name = htmlspecialchars($order['shipping_name']);
                                        } else {
                                            $shipping_name = 'N/A';
                                        }
                                        echo $shipping_name;
                                        ?><br>
                                        <?php echo htmlspecialchars($order['shipping_address'] ?? $order['shipping_address1'] ?? 'N/A'); ?><br>
                                        <?php
                                        $city = $order['shipping_city'] ?? 'N/A';
                                        $province = $order['shipping_province'] ?? $order['shipping_state'] ?? 'N/A';
                                        $postal = $order['shipping_postal_code'] ?? $order['shipping_postal'] ?? 'N/A';
                                        echo htmlspecialchars($city . ', ' . $province . ' ' . $postal);
                                        ?>
                                    </p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <p class="mb-1 small"><strong>Subtotal:</strong>
                                        $<?php echo number_format($order['subtotal'] ?? 0, 2); ?></p>
                                    <p class="mb-1 small"><strong>Shipping:</strong>
                                        $<?php echo number_format($order['shipping'] ?? 0, 2); ?></p>
                                    <p class="mb-1 small"><strong>Tax:</strong>
                                        $<?php echo number_format($order['tax'] ?? 0, 2); ?></p>
                                    <p class="mb-0"><strong>Total:</strong> <span class="fs-5"
                                            style="color: #667eea;">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>