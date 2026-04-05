<?php
session_start();
if (!isset($_SESSION['user'])) {
    $_SESSION['login_error'] = 'Please login to proceed with checkout.';
    header('Location: login.php');
    exit;
}

$title = "Checkout";
include 'includes/header.php';

$order_error = $_SESSION['order_error'] ?? '';
unset($_SESSION['order_error']);
?>

<div class="container py-5">

<?php if ($order_error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($order_error); ?></div>
<?php endif; ?>

<form id="checkoutForm" action="checkout-process.php" method="POST">

<h4>Shipping Information</h4>

<input type="text" name="firstname" placeholder="First Name" class="form-control mb-2" required>
<input type="text" name="lastname" placeholder="Last Name" class="form-control mb-2" required>
<input type="email" name="email" placeholder="Email" class="form-control mb-2" required>
<input type="text" name="address" placeholder="Address" class="form-control mb-2" required>
<input type="text" name="city" placeholder="City" class="form-control mb-2" required>

<select name="province" class="form-control mb-2" required>
    <option value="">Choose Province</option>
    <option>ON</option>
    <option>QC</option>
    <option>BC</option>
</select>

<!-- ✅ FIXED HERE -->
<input type="text" name="postal_code" placeholder="Postal Code" class="form-control mb-2" required>

<h4 class="mt-4">Payment Information</h4>

<input type="text" name="card_number" placeholder="Card Number" class="form-control mb-2" required>
<input type="text" name="card_expiry" placeholder="MM/YY" class="form-control mb-2" required>
<input type="text" name="card_cvv" placeholder="CVV" class="form-control mb-2" required>
<input type="text" name="card_name" placeholder="Cardholder Name" class="form-control mb-2" required>

<input type="hidden" name="cart_items" id="cart_items_input">

<button type="submit" class="btn btn-primary mt-3">Place Order</button>

</form>

</div>

<script>
document.querySelector("form").addEventListener("submit", function() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    document.getElementById('cart_items_input').value = JSON.stringify(cart);
});
</script>

<?php include 'includes/footer.php'; ?>