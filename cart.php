<?php
session_start();
if (!isset($_SESSION['user'])) {
    $_SESSION['login_error'] = 'Please login to view your cart.';
    header('Location: login.php');
    exit;
}

$title = "Shopping Cart";
include 'includes/header.php';

// Cart items will be loaded from localStorage via JavaScript
?>

<div class="min-vh-100">
    <!-- Breadcrumb -->
    <div class="bg-light border-bottom">
        <div class="container px-4 py-3">
            <div class="d-flex align-items-center gap-2" style="font-size: 0.875rem;">
                <a href="index.php" class="text-decoration-none" style="color: #667eea;">Home</a>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="text-muted">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-dark fw-medium">Shopping Cart</span>
            </div>
        </div>
    </div>

    <div class="container px-4 py-5">
        <!-- Empty Cart Message (hidden by default, shown by JS if cart is empty) -->
        <div id="empty-cart-message" class="text-center py-5" style="display: none;">
            <svg width="120" height="120" fill="none" stroke="#667eea" viewBox="0 0 24 24" class="mb-4 opacity-50">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h2 class="fw-bold mb-3" style="color: #333;">Your cart is empty</h2>
            <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
            <a href="products.php" class="btn btn-primary btn-lg px-5">
                Continue Shopping
            </a>
        </div>

        <!-- Cart Content (shown when cart has items) -->
        <div id="cart-content" class="row g-4" style="display: none;">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold mb-0" style="color: #333;">Shopping Cart</h2>
                    <span class="text-muted" id="cart-item-count">0 item(s)</span>
                </div>

                <div class="bg-white rounded border" id="cart-items-container">
                    <!-- Cart items will be populated by JavaScript -->
                    <div class="p-4 text-center text-muted">
                        <p>Loading cart...</p>
                    </div>
                </div>

                <!-- Continue Shopping -->
                <div class="mt-4">
                    <a href="products.php" class="text-decoration-none" style="color: #667eea;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="d-inline me-1">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="bg-white rounded border p-4" id="order-summary" style="position: sticky; top: 20px;">
                    <h3 class="fw-bold mb-4" style="color: #333;">Order Summary</h3>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold" id="subtotal">$0.00</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping</span>
                        <span class="fw-semibold" id="shipping">$0.00</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Tax</span>
                        <span class="fw-semibold" id="tax">$0.00</span>
                    </div>
                    
                    <div id="free-shipping-alert" class="alert alert-info small mb-3" style="display: none;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="d-inline me-1">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span id="free-shipping-text"></span>
                    </div>
                    
                    <div class="border-top pt-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold" style="font-size: 1.2rem; color: #333;">Total</span>
                            <span class="fw-bold" style="font-size: 1.5rem; color: #667eea;" id="total">
                                $0.00
                            </span>
                        </div>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary w-100 btn-lg mb-3">
                        Proceed to Checkout
                    </a>
                    
                    <div class="text-center">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='30'%3E%3Ctext x='10' y='20' font-family='Arial' font-size='12' fill='%23666'%3ESecure Checkout%3C/text%3E%3C/svg%3E" 
                             alt="Secure Checkout" 
                             class="img-fluid opacity-50">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load cart from localStorage
document.addEventListener('DOMContentLoaded', function() {
    loadCart();
    updateCartBadge();
});

function loadCart() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const container = document.getElementById('cart-items-container');
    const emptyMessage = document.getElementById('empty-cart-message');
    const cartContent = document.getElementById('cart-content');
    
    if (cart.length === 0) {
        // Show empty cart message
        if (emptyMessage) emptyMessage.style.display = 'block';
        if (cartContent) cartContent.style.display = 'none';
        return;
    }
    
    // Hide empty message and show cart content
    if (emptyMessage) emptyMessage.style.display = 'none';
    if (cartContent) cartContent.style.display = 'flex';
    
    let html = '';
    let subtotal = 0;
    
    cart.forEach((item, index) => {
        const itemPrice = Number.isFinite(item.price) ? item.price : 0;
        const itemOriginal = Number.isFinite(item.originalPrice) ? item.originalPrice : 0;
        const itemTotal = itemPrice * item.quantity;
        subtotal += itemTotal;
        
        html += `
            <div class="p-4 ${index < cart.length - 1 ? 'border-bottom' : ''}">
                <div class="row align-items-center">
                    <div class="col-12 col-md-3 mb-3 mb-md-0">
                        <a href="product-detail.php?id=${item.id}" class="text-decoration-none">
                            <img src="${item.image}" alt="${item.name}" class="w-100 rounded" style="height: 120px; object-fit: cover;">
                        </a>
                    </div>
                    <div class="col-12 col-md-5 mb-3 mb-md-0">
                        <h4 class="fw-semibold mb-2" style="color: #333;">
                            <a href="product-detail.php?id=${item.id}" class="text-decoration-none" style="color: #333;">${item.name}</a>
                        </h4>
                        <p class="text-muted small mb-2">Item #${item.id}</p>
                        <p class="fw-bold mb-0" style="color: #667eea; font-size: 1.1rem;">
                            $${itemPrice.toFixed(2)}
                            ${itemOriginal>itemPrice ? `<span class="text-muted small text-decoration-line-through">$${itemOriginal.toFixed(2)}</span>` : ''}
                        </p>
                    </div>
                    <div class="col-12 col-md-2 mb-3 mb-md-0">
                        <label class="form-label small text-muted mb-1">Quantity</label>
                        <div class="input-group" style="width: 120px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(${item.id}, -1)" style="border-color: #dee2e6;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                            </button>
                            <input type="number" class="form-control text-center" value="${item.quantity}" min="1" id="qty-${item.id}" onchange="updateQuantity(${item.id}, 0, this.value)" style="border-color: #dee2e6;">
                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(${item.id}, 1)" style="border-color: #dee2e6;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-md-2 text-md-end">
                        <p class="fw-bold mb-2" style="color: #333; font-size: 1.1rem;">$${itemTotal.toFixed(2)}</p>
                        <button class="btn btn-link text-danger p-0 small" onclick="removeItem(${item.id})" style="text-decoration: none;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="d-inline me-1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    updateOrderSummary(subtotal);
}

function updateQuantity(itemId, change, newValue) {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const item = cart.find(i => i.id === itemId);
    
    if (!item) return;
    
    if (newValue !== undefined) {
        item.quantity = Math.max(1, parseInt(newValue) || 1);
    } else {
        item.quantity = Math.max(1, item.quantity + change);
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart();
    updateCartBadge();
}

function removeItem(itemId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const newCart = cart.filter(item => item.id !== itemId);
        localStorage.setItem('cart', JSON.stringify(newCart));
        loadCart();
        updateCartBadge();
    }
}

function updateOrderSummary(subtotal) {
    const shipping = subtotal > 50 ? 0 : 9.99;
    const tax = subtotal * 0.08;
    const total = subtotal + shipping + tax;
    
    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('shipping').innerHTML = shipping === 0 ? '<span class="text-success">FREE</span>' : '$' + shipping.toFixed(2);
    document.getElementById('tax').textContent = '$' + tax.toFixed(2);
    document.getElementById('total').textContent = '$' + total.toFixed(2);
    
    // Update free shipping alert
    const freeShippingAlert = document.getElementById('free-shipping-alert');
    const freeShippingText = document.getElementById('free-shipping-text');
    if (subtotal < 50 && freeShippingAlert && freeShippingText) {
        const needed = (50 - subtotal).toFixed(2);
        freeShippingText.textContent = 'Add $' + needed + ' more for free shipping!';
        freeShippingAlert.style.display = 'block';
    } else if (freeShippingAlert) {
        freeShippingAlert.style.display = 'none';
    }
    
    const cartLength = JSON.parse(localStorage.getItem('cart') || '[]').length;
    const cartItemCount = document.getElementById('cart-item-count');
    if (cartItemCount) {
        cartItemCount.textContent = cartLength + ' item(s)';
    }
}

</script>

<?php include 'includes/footer.php'; ?>
