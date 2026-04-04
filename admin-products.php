<?php
session_start();
include 'Database/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: admin-login.php');
    exit;
}

$title = "Manage Products";
include 'includes/header.php';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM products WHERE id = $id";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success_message'] = 'Product deleted successfully.';
    } else {
        $_SESSION['error_message'] = 'Error deleting product.';
    }
    header('Location: admin-products.php');
    exit;
}

// Fetch all products - handle missing created_at column
$products_columns_query = "SHOW COLUMNS FROM products";
$products_columns_result = mysqli_query($conn, $products_columns_query);
$has_created_at = false;
if ($products_columns_result) {
    while ($col = mysqli_fetch_assoc($products_columns_result)) {
        if ($col['Field'] === 'created_at') {
            $has_created_at = true;
            break;
        }
    }
}

$orderBy = $has_created_at ? "ORDER BY created_at DESC" : "ORDER BY id DESC";
$query = "SELECT * FROM products $orderBy";
$result = mysqli_query($conn, $query);
$products = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

$success = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>

<div class="min-vh-100" style="background-color: #f8f9fa;">
    <!-- Admin Header -->
    <div class="bg-white border-bottom shadow-sm">
        <div class="container-fluid px-4 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-0" style="color: #667eea;">Manage Products</h4>
                </div>
                <div>
                    <a href="admin-dashboard.php" class="btn btn-outline-secondary btn-sm me-2">Back to Dashboard</a>
                    <a href="admin-product-add.php" class="btn btn-primary btn-sm">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="d-inline-block me-1">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Product
                    </a>
                </div>
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

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($products)): ?>
                    <div class="p-5 text-center">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="text-muted mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <h5 class="text-muted">No products found</h5>
                        <p class="text-muted mb-0">Get started by adding your first product.</p>
                        <a href="admin-product-add.php" class="btn btn-primary mt-3">Add Product</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4">ID</th>
                                    <th scope="col">Image</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Stock</th>
                                    <th scope="col" class="pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td class="ps-4 fw-semibold" style="color: #667eea;">#<?php echo $product['id']; ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($product['image_url'] ?? ''); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;"
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'60\' height=\'60\'%3E%3Crect fill=\'%23ddd\' width=\'60\' height=\'60\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                                    </td>
                                    <td>
                                        <div class="fw-medium"><?php echo htmlspecialchars($product['name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 50)); ?>...</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            <?php echo htmlspecialchars($product['category']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">$<?php echo number_format($product['price'], 2); ?></span>
                                    </td>
                                    <td>
                                        <?php $stock = $product['stock'] ?? 0; ?>
                                        <?php if ($stock > 0): ?>
                                            <span class="badge bg-success"><?php echo $stock; ?> in stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Out of stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4">
                                        <div class="d-flex gap-2">
                                            <a href="admin-product-edit.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <a href="admin-products.php?delete=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this product?');">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

