<?php
session_start();
$title = "Order Confirmation";
include 'includes/header.php';

$order_number = isset($_SESSION['order_number']) ? $_SESSION['order_number'] : '';
$order_success = isset($_SESSION['order_success']) ? $_SESSION['order_success'] : false;

if (!$order_success || empty($order_number)) {
    header('Location: index.php');
    exit;
}

// Clear order session data
unset($_SESSION['order_success']);
unset($_SESSION['order_number']);
?>

<div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container px-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="bg-white rounded shadow-lg p-5 text-center">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                        style="width: 100px; height: 100px;">
                        <svg width="60" height="60" fill="none" stroke="#28a745" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    
                    <h2 class="fw-bold mb-3" style="color: #333;">Order Placed Successfully!</h2>
                    <p class="text-muted mb-4">Thank you for your purchase. Your order has been received and is being processed.</p>
                    
                    <div class="bg-light rounded p-4 mb-4">
                        <p class="text-muted mb-2 small">Order Number</p>
                        <h4 class="fw-bold mb-0" style="color: #667eea;"><?php echo htmlspecialchars($order_number); ?></h4>
                    </div>
                    
                    <p class="text-muted small mb-4">
                        You will receive an email confirmation shortly. You can track your order status in your account.
                    </p>
                    
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="index.php" class="btn btn-primary">
                            Continue Shopping
                        </a>
                        <a href="products.php" class="btn btn-outline-primary">
                            View Products
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Clear client cart and refresh badge once confirmation page loads
document.addEventListener('DOMContentLoaded', function() {
    localStorage.removeItem('cart');
    if (typeof updateCartBadge === 'function') {
        updateCartBadge();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
