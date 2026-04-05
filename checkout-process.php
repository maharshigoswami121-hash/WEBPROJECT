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

// Error helper
function fail($msg) {
    $_SESSION['order_error'] = $msg;
    header('Location: checkout.php');
    exit;
}

// Get POST data
$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$province = trim($_POST['province'] ?? '');
$postal_code = trim($_POST['postal_code'] ?? '');

$card_number = trim($_POST['card_number'] ?? '');
$card_expiry = trim($_POST['card_expiry'] ?? '');
$card_cvv = trim($_POST['card_cvv'] ?? '');
$card_name = trim($_POST['card_name'] ?? '');

// ✅ Better validation
if ($firstname === '') fail('First name is required');
if ($lastname === '') fail('Last name is required');
if ($email === '') fail('Email is required');
if ($address === '') fail('Address is required');
if ($city === '') fail('City is required');
if ($province === '') fail('Province is required');
if ($postal_code === '') fail('Postal code is required');
if ($card_number === '') fail('Card number is required');
if ($card_expiry === '') fail('Expiry date is required');
if ($card_cvv === '') fail('CVV is required');
if ($card_name === '') fail('Cardholder name is required');

// Cart
$cart_items = json_decode($_POST['cart_items'] ?? '[]', true);

if (!$cart_items || count($cart_items) === 0) {
    fail('Cart is empty');
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = $subtotal > 50 ? 0 : 9.99;
$tax = $subtotal * 0.08;
$total = $subtotal + $shipping + $tax;

// Insert Order
$user_id = $_SESSION['user']['id'];
$order_number = 'ORD-' . time();

$shipping_address = "$address, $city, $province $postal_code";

mysqli_begin_transaction($conn);

$orderSql = "INSERT INTO orders (user_id, order_number, total_amount, status, shipping_address)
VALUES (?, ?, ?, 'pending', ?)";

$stmt = mysqli_prepare($conn, $orderSql);
mysqli_stmt_bind_param($stmt, "isds", $user_id, $order_number, $total, $shipping_address);
mysqli_stmt_execute($stmt);

$order_id = mysqli_insert_id($conn);

// Insert items
$itemSql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price)
VALUES (?, ?, ?, ?)";

$itemStmt = mysqli_prepare($conn, $itemSql);

foreach ($cart_items as $item) {
    mysqli_stmt_bind_param(
        $itemStmt,
        "iiid",
        $order_id,
        $item['id'],
        $item['quantity'],
        $item['price']
    );
    mysqli_stmt_execute($itemStmt);
}

mysqli_commit($conn);

// Success
$_SESSION['order_success'] = true;
$_SESSION['order_number'] = $order_number;

header("Location: order-confirmation.php");
exit;