<?php
session_start();
$title = "Product Details";
require_once 'Database/db.php';
include 'includes/header.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$productError = '';
$placeholderDetail = 'data:image/svg+xml,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" width=\"600\" height=\"400\"%3E%3Crect fill=\"%23f1f3f5\" width=\"600\" height=\"400\"/%3E%3Ctext x=\"50%25\" y=\"50%25\" text-anchor=\"middle\" dy=\".3em\" fill=\"%23999\"%3ENo Image%3C/text%3E%3C/svg%3E';
$reviews = [];
$averageRating = 0;
$reviewsCount = 0;
$reviewSubmitMessage = '';
$reviewSubmitError = '';

function normalizeProductImageDetail($path)
{
    global $placeholderDetail;
    if (empty($path)) {
        return $placeholderDetail;
    }
    $path = trim($path);
    $path = str_replace('\\', '/', $path);
    if (str_starts_with($path, './')) {
        $path = substr($path, 2);
    }
    if (preg_match('~^(https?:)?//~i', $path) || str_starts_with($path, 'data:')) {
        return $path;
    }
    $projectRoot = realpath(__DIR__);
    $absolute = $path;
    if (!str_starts_with($absolute, $projectRoot)) {
        $absolute = $projectRoot . '/' . ltrim($path, '/');
    }
    if (file_exists($absolute)) {
        if (str_starts_with($absolute, $projectRoot)) {
            $relative = ltrim(str_replace($projectRoot, '', $absolute), '/');
            return $relative;
        }
        return ltrim($path, '/');
    }
    $basename = basename($path);
    if ($basename && file_exists($projectRoot . '/images/' . $basename)) {
        return 'images/' . $basename;
    }
    return $placeholderDetail;
}

function ensureReviewsTable($conn)
{
    static $checked = false;
    if ($checked) {
        return true;
    }
    $checked = true;
    $createSql = "CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        user_id INT NULL,
        user_name VARCHAR(255) NOT NULL,
        rating TINYINT NOT NULL DEFAULT 5,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_product (product_id),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    try {
        mysqli_query($conn, $createSql);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

// Fetch product from DB (flexible columns)
try {
    $availableCols = [];
    if ($cols = mysqli_query($conn, "SHOW COLUMNS FROM products")) {
        while ($col = mysqli_fetch_assoc($cols)) {
            $availableCols[$col['Field']] = true;
        }
    }
    $imageCol = isset($availableCols['image_url']) ? 'image_url' : (isset($availableCols['image']) ? 'image' : '');
    $stockCol = isset($availableCols['stock']) ? 'stock' : (isset($availableCols['stock_quantity']) ? 'stock_quantity' : '');
    $ratingCol = isset($availableCols['rating']) ? 'rating' : '';
    $reviewsCol = isset($availableCols['reviews']) ? 'reviews' : '';
    $discountCol = isset($availableCols['discount']) ? 'discount' : '';
    $originalCol = isset($availableCols['original_price']) ? 'original_price' : '';

    $selectFields = ['id'];
    foreach (['name', 'category', 'description', 'price'] as $field) {
        if (isset($availableCols[$field])) $selectFields[] = $field;
    }
    if ($imageCol) $selectFields[] = $imageCol;
    if ($stockCol) $selectFields[] = $stockCol;
    if ($ratingCol) $selectFields[] = $ratingCol;
    if ($reviewsCol) $selectFields[] = $reviewsCol;
    if ($discountCol) $selectFields[] = $discountCol;
    if ($originalCol) $selectFields[] = $originalCol;
    $select = implode(', ', $selectFields);

    $stmt = mysqli_prepare($conn, "SELECT $select FROM products WHERE id = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && ($row = mysqli_fetch_assoc($result))) {
            $imageValRaw = '';
            if ($imageCol && !empty($row[$imageCol])) {
                $imageValRaw = $row[$imageCol];
            } elseif (isset($availableCols['image']) && !empty($row['image'])) {
                $imageValRaw = $row['image'];
            }
            $imageVal = normalizeProductImageDetail($imageValRaw);
            $stockVal = $stockCol && isset($row[$stockCol]) ? (int)$row[$stockCol] : 0;
            $discountVal = $discountCol && isset($row[$discountCol]) ? (int)$row[$discountCol] : 0;
            $originalVal = $originalCol && isset($row[$originalCol]) ? (float)$row[$originalCol] : null;
            $basePrice = $originalVal && $originalVal > 0 ? $originalVal : (isset($row['price']) ? (float)$row['price'] : 0);
            $finalPrice = $discountVal > 0 ? $basePrice * (1 - $discountVal / 100) : (isset($row['price']) ? (float)$row['price'] : 0);
            $product = [
                'id' => (int)$row['id'],
                'name' => $row['name'] ?? 'Product',
                'category' => $row['category'] ?? 'Category',
                'description' => $row['description'] ?? 'No description available.',
                'price' => isset($row['price']) ? (float)$row['price'] : 0,
                'price_base' => $basePrice,
                'price_final' => $finalPrice,
                'discount' => $discountVal,
                'original_price' => $originalVal,
                'image' => $imageVal,
                'rating' => $ratingCol && isset($row[$ratingCol]) ? (int)$row[$ratingCol] : 4,
                'reviews' => $reviewsCol && isset($row[$reviewsCol]) ? (int)$row[$reviewsCol] : 0,
                'inStock' => $stockVal > 0,
                'stockQty' => $stockVal > 0 ? $stockVal : 0
            ];
        }
    }
} catch (Throwable $e) {
    $productError = 'Unable to load product details right now.';
}

// Fallback query if dynamic select failed or returned nothing
if (!$product && $productId > 0) {
    $fallbackRes = @mysqli_query($conn, "SELECT * FROM products WHERE id = $productId LIMIT 1");
    if ($fallbackRes && ($row = mysqli_fetch_assoc($fallbackRes))) {
        $imageVal = '';
        if (!empty($row['image_url'])) {
            $imageVal = $row['image_url'];
        } elseif (!empty($row['image'])) {
            $imageVal = $row['image'];
        }
        $imageVal = normalizeProductImageDetail($imageVal);
        $stockVal = isset($row['stock']) ? (int)$row['stock'] : (isset($row['stock_quantity']) ? (int)$row['stock_quantity'] : 0);
        $discountVal = isset($row['discount']) ? (int)$row['discount'] : 0;
        $originalVal = isset($row['original_price']) ? (float)$row['original_price'] : null;
        $basePrice = $originalVal && $originalVal > 0 ? $originalVal : (isset($row['price']) ? (float)$row['price'] : 0);
        $finalPrice = $discountVal > 0 ? $basePrice * (1 - $discountVal / 100) : (isset($row['price']) ? (float)$row['price'] : 0);
        $product = [
            'id' => (int)($row['id'] ?? $productId),
            'name' => $row['name'] ?? 'Product',
            'category' => $row['category'] ?? 'Category',
            'description' => $row['description'] ?? 'No description available.',
            'price' => isset($row['price']) ? (float)$row['price'] : 0,
            'price_base' => $basePrice,
            'price_final' => $finalPrice,
            'discount' => $discountVal,
            'original_price' => $originalVal,
            'image' => $imageVal,
            'rating' => isset($row['rating']) ? (int)$row['rating'] : 4,
            'reviews' => isset($row['reviews']) ? (int)$row['reviews'] : 0,
            'inStock' => $stockVal > 0,
            'stockQty' => $stockVal > 0 ? $stockVal : 0
        ];
    } elseif ($fallbackRes === false) {
        $productError = 'Error loading product: ' . mysqli_error($conn);
    }
}

if (!$product) {
    ?>  
    <?php
    include 'includes/footer.php';
    exit;
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user'])) {
        $reviewSubmitError = 'Please log in to add a review.';
    } else {
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $comment = trim($_POST['comment'] ?? '');
        $rating = max(1, min(5, $rating));
        $user = $_SESSION['user'];
        $userName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        if ($userName === '') {
            $userName = $user['email'] ?? 'Customer';
        }
        if (ensureReviewsTable($conn)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO reviews (product_id, user_id, user_name, rating, comment) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $userId = isset($user['id']) ? (int)$user['id'] : null;
                mysqli_stmt_bind_param($stmt, 'iisis', $productId, $userId, $userName, $rating, $comment);
                if (mysqli_stmt_execute($stmt)) {
                    $reviewSubmitMessage = 'Thanks for your feedback!';
                } else {
                    $reviewSubmitError = 'Unable to save review.';
                }
                mysqli_stmt_close($stmt);
            } else {
                $reviewSubmitError = 'Unable to prepare review statement.';
            }
        } else {
            $reviewSubmitError = 'Reviews are currently unavailable.';
        }
    }
}

// Load reviews for this product
if (ensureReviewsTable($conn)) {
    // Aggregate
    if ($aggStmt = mysqli_prepare($conn, "SELECT COALESCE(AVG(rating),0) as avg_rating, COUNT(*) as review_count FROM reviews WHERE product_id = ?")) {
        mysqli_stmt_bind_param($aggStmt, 'i', $productId);
        mysqli_stmt_execute($aggStmt);
        $aggRes = mysqli_stmt_get_result($aggStmt);
        if ($aggRes && ($agg = mysqli_fetch_assoc($aggRes))) {
            $averageRating = (float)$agg['avg_rating'];
            $reviewsCount = (int)$agg['review_count'];
        }
        mysqli_stmt_close($aggStmt);
    }

    // Latest reviews
    if ($revStmt = mysqli_prepare($conn, "SELECT user_name, rating, comment, created_at FROM reviews WHERE product_id = ? ORDER BY created_at DESC")) {
        mysqli_stmt_bind_param($revStmt, 'i', $productId);
        mysqli_stmt_execute($revStmt);
        $revRes = mysqli_stmt_get_result($revStmt);
        if ($revRes) {
            while ($row = mysqli_fetch_assoc($revRes)) {
                $reviews[] = $row;
            }
        }
        mysqli_stmt_close($revStmt);
    }
}
?>

<style>
    .product-hero {
        background: linear-gradient(180deg, #f6f7fb 0%, #ffffff 100%);
    }
    .product-media {
        min-height: 420px;
        background: radial-gradient(circle at 20% 20%, rgba(102, 126, 234, 0.08), transparent 45%), #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
    }
    .product-media img {
        max-height: 520px;
        object-fit: contain;
    }
    .price-tile {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
    }
    .pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(102, 126, 234, 0.12);
        color: #4c51bf;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .feature-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 12px;
        background: #fff;
        height: 100%;
    }
    .feature-card svg {
        color: #667eea;
    }
    .description-box {
        border-left: 4px solid #667eea;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px 18px;
    }
    @media (max-width: 768px) {
        .product-media { min-height: 280px; }
        h1.product-title { font-size: 2rem; }
    }
</style>

<div class="min-vh-100">
    <!-- Breadcrumb -->
    <div class="bg-light border-bottom">
        <div class="container px-4 py-3">
            <div class="d-flex align-items-center gap-2" style="font-size: 0.875rem;">
                <a href="index.php" class="text-decoration-none" style="color: #667eea;">Home</a>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="text-muted">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <a href="products.php" class="text-decoration-none" style="color: #667eea;">Products</a>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="text-muted">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-dark fw-medium"><?php echo htmlspecialchars($product['name']); ?></span>
            </div>
        </div>
    </div>

    <div class="container px-4 py-5">
        <div class="row g-4 align-items-start">
            <!-- Product Image -->
            <div class="col-lg-5">
                <div class="product-media p-4 text-center d-flex align-items-center justify-content-center">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                        class="img-fluid"
                        onerror='this.src="data:image/svg+xml,%3Csvg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;600&quot; height=&quot;400&quot;%3E%3Crect fill=&quot;%23f1f3f5&quot; width=&quot;600&quot; height=&quot;400&quot;/%3E%3Ctext x=&quot;50%25&quot; y=&quot;50%25&quot; text-anchor=&quot;middle&quot; dy=&quot;.3em&quot; fill=&quot;%23999&quot;%3ENo Image%3C/text%3E%3C/svg%3E";'>
                </div>
                <div class="row row-cols-1 row-cols-md-3 g-3 mt-3">
                    <div class="col">
                        <div class="feature-card d-flex align-items-start gap-2">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <div class="fw-semibold">Warranty</div>
                                <small class="text-muted">1-year limited coverage</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="feature-card d-flex align-items-start gap-2">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m0 0l-6 6m6-6l6 6" />
                            </svg>
                            <div>
                                <div class="fw-semibold">Fast Delivery</div>
                                <small class="text-muted">Dispatches in 1-2 days</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="feature-card d-flex align-items-start gap-2">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16h6M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4-.8L3 20l1.2-3A7.9 7.9 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <div>
                                <div class="fw-semibold">Support</div>
                                <small class="text-muted">Chat with tech experts</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-lg-7">
                <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                    <span class="pill-badge">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        <?php echo htmlspecialchars($product['category']); ?>
                    </span>
                    <?php if ($product['inStock']): ?>
                        <span class="badge bg-success bg-opacity-10 text-success">In stock <?php if ($product['stockQty']) echo '(' . $product['stockQty'] . ')'; ?></span>
                    <?php else: ?>
                        <span class="badge bg-danger bg-opacity-10 text-danger">Out of stock</span>
                    <?php endif; ?>
                    <span class="text-muted small">SKU #<?php echo $product['id']; ?></span>
                </div>

                <h1 class="fw-bold mb-3 product-title" style="color: #1f2937;"><?php echo htmlspecialchars($product['name']); ?></h1>

                <!-- Rating -->
                <?php
                $ratingValue = $averageRating > 0 ? $averageRating : (isset($product['rating']) ? (float)$product['rating'] : 0);
                $rating = max(0, min(5, (int)round($ratingValue)));
                $reviewsDisplay = $reviewsCount ?: (isset($product['reviews']) ? (int)$product['reviews'] : 0);
                ?>
                <div class="d-flex align-items-center gap-2 mb-4">
                    <div class="d-flex align-items-center gap-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $rating): ?>
                                <svg width="20" height="20" fill="#fbbf24" stroke="currentColor" viewBox="0 0 24 24" class="text-warning">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            <?php else: ?>
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="text-muted">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="text-muted">(<?php echo $reviewsDisplay; ?> reviews)</span>
                </div>

                <!-- Price -->
                <div class="price-tile mb-4">
                    <div class="d-flex align-items-baseline gap-3 mb-1">
                        <span class="display-6 fw-bold" style="color: #667eea;">$<?php echo number_format($product['price_final'] ?? $product['price'], 2); ?></span>
                        <?php if (!empty($product['price_base']) && ($product['price_base'] > ($product['price_final'] ?? $product['price']))): ?>
                            <span class="text-muted small text-decoration-line-through">$<?php echo number_format($product['price_base'], 2); ?></span>
                        <?php endif; ?>
                        <span class="text-muted small">VAT included</span>
                    </div>
                    <?php if ($product['inStock']): ?>
                        <div class="text-success small d-flex align-items-center gap-2">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Ships within 48 hours
                        </div>
                    <?php else: ?>
                        <div class="text-danger small">Out of stock</div>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="description-box mb-4">
                    <h5 class="fw-bold mb-2" style="color: #1f2937;">Overview</h5>
                    <p class="text-muted mb-0" style="line-height: 1.8;"><?php echo htmlspecialchars($product['description'] ?: 'No description provided.'); ?></p>
                </div>

                <!-- Add to Cart -->
                <div class="mb-4">
                    <div class="d-flex gap-3 align-items-center mb-3">
                        <label class="fw-semibold" style="color: #333;">Quantity</label>
                        <div class="input-group" style="width: 160px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="changeQty(-1)">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </button>
                            <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="<?php echo $product['stockQty'] ?: 999; ?>">
                            <button class="btn btn-outline-secondary" type="button" onclick="changeQty(1)">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-lg w-100 mb-2"
                        onclick="addToCart(<?php echo $product['id']; ?>, <?php echo $product['price_final'] ?? $product['price']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', '<?php echo $product['image']; ?>', <?php echo $product['price_base'] ?? $product['price']; ?>)"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;"
                        <?php echo !$product['inStock'] ? 'disabled' : ''; ?>>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="d-inline me-2">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Add to Cart
                    </button>
                    <a href="cart.php" class="btn btn-outline-primary btn-lg w-100">
                        View Cart
                    </a>
                </div>

                <!-- Reviews -->
                <div class="border-top pt-4 mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold mb-0" style="color: #1f2937;">Customer Reviews</h4>
                        <span class="badge bg-light text-dark">
                            <?php echo $reviewsDisplay; ?> review<?php echo $reviewsDisplay === 1 ? '' : 's'; ?> · Avg <?php echo number_format($ratingValue, 1); ?>/5
                        </span>
                    </div>

                    <?php if (!empty($reviewSubmitMessage)): ?>
                        <div class="alert alert-success py-2"><?php echo htmlspecialchars($reviewSubmitMessage); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($reviewSubmitError)): ?>
                        <div class="alert alert-danger py-2"><?php echo htmlspecialchars($reviewSubmitError); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($reviews)): ?>
                        <div class="list-group mb-4">
                            <?php foreach ($reviews as $rev): ?>
                                <div class="list-group-item border-0 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="fw-semibold"><?php echo htmlspecialchars($rev['user_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars(date('M j, Y', strtotime($rev['created_at']))); ?></small>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 my-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= (int)$rev['rating']): ?>
                                                <svg width="16" height="16" fill="#fbbf24" stroke="currentColor" viewBox="0 0 24 24" class="text-warning">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                </svg>
                                            <?php else: ?>
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="text-muted">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                </svg>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No reviews yet. Be the first to share your thoughts.</p>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">Write a review</h5>
                            <?php if (isset($_SESSION['user'])): ?>
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Rating</label>
                                        <select name="rating" class="form-select" required>
                                            <?php for ($r = 5; $r >= 1; $r--): ?>
                                                <option value="<?php echo $r; ?>"><?php echo $r; ?> star<?php echo $r === 1 ? '' : 's'; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Comment</label>
                                        <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience" required></textarea>
                                    </div>
                                    <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                                </form>
                            <?php else: ?>
                                <p class="text-muted mb-0">
                                    <a href="login.php" class="text-decoration-none">Log in</a> to add your rating and comment.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function changeQty(change) {
        const input = document.getElementById('quantity');
        let currentQty = parseInt(input.value) || 1;
        const maxQty = parseInt(input.getAttribute('max')) || 999;
        currentQty = Math.max(1, Math.min(maxQty, currentQty + change));
        input.value = currentQty;
    }

    function addToCart(productId, price, name, image, originalPrice) {
        // Check if user is logged in
        const isLoggedIn = <?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>;
        
        if (!isLoggedIn) {
            if (confirm('You need to login to add items to cart. Would you like to login now?')) {
                window.location.href = 'login.php';
            }
            return;
        }
        
        const quantity = parseInt(document.getElementById('quantity').value) || 1;

        // Get existing cart from localStorage or create new one
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        // Check if product already in cart
        const existingItem = cart.find(item => item.id === productId);
        if (existingItem) {
            existingItem.quantity += quantity;
            existingItem.price = price; // ensure latest discount
            existingItem.originalPrice = originalPrice || 0;
        } else {
            cart.push({
                id: productId,
                name: name,
                price: price,
                originalPrice: originalPrice || 0,
                image: image,
                quantity: quantity
            });
        }

        // Save to localStorage
        localStorage.setItem('cart', JSON.stringify(cart));

        // Persist to database
        syncCartToServer({ id: productId, quantity, price, name, image });

        // Update cart badge
        updateCartBadge();

        alert('Product added to cart!');
    }

    async function syncCartToServer(item) {
        try {
            const body = new URLSearchParams({
                product_id: item.id,
                quantity: item.quantity,
                price: item.price,
                product_name: item.name,
                product_image: item.image
            });
            const response = await fetch('api/cart-add.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body
            });
            if (!response.ok) {
                console.warn('Cart sync failed', await response.text());
            }
        } catch (err) {
            console.warn('Cart sync error', err);
        }
    }
</script>

<?php include 'includes/footer.php'; ?>
