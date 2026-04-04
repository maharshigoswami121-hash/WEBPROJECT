<?php
session_start();
include 'Database/db.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['login_error'] = 'Please login to complete your purchase.';
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

// Helper to short-circuit with an error and bounce back to checkout
$fail = function (string $message) {
    $_SESSION['order_error'] = $message;
    header('Location: checkout.php');
    exit;
};

try {
    // Required fields
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $postal = trim($_POST['postal'] ?? '');
    $card_number = trim($_POST['card_number'] ?? '');
    $card_expiry = trim($_POST['card_expiry'] ?? '');
    $card_cvv = trim($_POST['card_cvv'] ?? '');
    $card_name = trim($_POST['card_name'] ?? '');

    $required = [$firstname, $lastname, $email, $address, $city, $province, $postal, $card_number, $card_expiry, $card_cvv, $card_name];
    if (in_array('', $required, true)) {
        $fail('Please complete all required shipping and payment fields.');
    }

    $cart_items = json_decode($_POST['cart_items'] ?? '[]', true);
    if (!is_array($cart_items) || count($cart_items) === 0) {
        $fail('Your cart is empty. Please add items before checkout.');
    }

    // Totals
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $price = isset($item['price']) ? (float) $item['price'] : 0;
        $qty = isset($item['quantity']) ? (int) $item['quantity'] : 1;
        $subtotal += $price * max(1, $qty);
    }
    $shipping = $subtotal > 50 ? 0 : 9.99;
    $tax = $subtotal * 0.08;
    $total = $subtotal + $shipping + $tax;

    $user_id = (int) ($_SESSION['user']['id'] ?? 0);
    $order_number = 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    $shipping_address = "{$address}, {$city}, {$province} {$postal}";
    $notes = json_encode(['subtotal' => $subtotal, 'shipping' => $shipping, 'tax' => $tax], JSON_UNESCAPED_SLASHES);

    mysqli_begin_transaction($conn);

    // Insert order
    $orderSql = "INSERT INTO orders (user_id, order_number, total_amount, status, payment_method, shipping_address, notes)
                 VALUES (?, ?, ?, 'pending', 'card', ?, ?)";
    $orderStmt = mysqli_prepare($conn, $orderSql);
    mysqli_stmt_bind_param($orderStmt, 'isdss', $user_id, $order_number, $total, $shipping_address, $notes);
    mysqli_stmt_execute($orderStmt);
    $order_id = mysqli_insert_id($conn);
    mysqli_stmt_close($orderStmt);

    // Insert order items
    $itemSql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price, product_name, product_price, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $itemStmt = mysqli_prepare($conn, $itemSql);

    foreach ($cart_items as $item) {
        $product_id = isset($item['id']) ? (int) $item['id'] : null;
        $name = $item['name'] ?? 'Item';
        $price = isset($item['price']) ? (float) $item['price'] : 0;
        $qty = max(1, isset($item['quantity']) ? (int) $item['quantity'] : 1);
        $lineTotal = $price * $qty;

        mysqli_stmt_bind_param(
            $itemStmt,
            'iiiddsdd',
            $order_id,
            $product_id,
            $qty,
            $price,
            $lineTotal,
            $name,
            $price,
            $lineTotal
        );
        mysqli_stmt_execute($itemStmt);
    }
    mysqli_stmt_close($itemStmt);

    // Clear saved cart rows for this user (if any)
    if ($user_id > 0) {
        $clearCartSql = "DELETE FROM cart WHERE user_id = ?";
        if ($clearStmt = mysqli_prepare($conn, $clearCartSql)) {
            mysqli_stmt_bind_param($clearStmt, 'i', $user_id);
            mysqli_stmt_execute($clearStmt);
            mysqli_stmt_close($clearStmt);
        }
    }

    mysqli_commit($conn);

    $_SESSION['order_success'] = true;
    $_SESSION['order_number'] = $order_number;
    header('Location: order-confirmation.php');
    exit;
} catch (Throwable $e) {
    if (mysqli_errno($conn)) {
        mysqli_rollback($conn);
    }
    $fail('Error placing order. Please try again.');
}
