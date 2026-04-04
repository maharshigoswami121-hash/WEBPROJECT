<?php
$title = "Deals";
require_once 'Database/db.php';
include 'includes/header.php';

// Pull products from DB to show deals (newest first)
$saleProducts = [];
try {
    $availableCols = [];
    if ($cols = mysqli_query($conn, "SHOW COLUMNS FROM products")) {
        while ($col = mysqli_fetch_assoc($cols)) {
            $availableCols[$col['Field']] = true;
        }
    }
    $imageCol = isset($availableCols['image_url']) ? 'image_url' : (isset($availableCols['image']) ? 'image' : '');
    $ratingCol = isset($availableCols['rating']) ? 'rating' : '';
    $reviewsCol = isset($availableCols['reviews']) ? 'reviews' : '';
    $discountCol = isset($availableCols['discount']) ? 'discount' : '';
    $originalCol = isset($availableCols['original_price']) ? 'original_price' : '';

    $selectFields = ['id'];
    foreach (['name', 'category', 'price'] as $field) {
        if (isset($availableCols[$field])) $selectFields[] = $field;
    }
    if ($imageCol) $selectFields[] = $imageCol;
    if ($ratingCol) $selectFields[] = $ratingCol;
    if ($reviewsCol) $selectFields[] = $reviewsCol;
    if ($discountCol) $selectFields[] = $discountCol;
    if ($originalCol) $selectFields[] = $originalCol;
    $select = implode(', ', $selectFields);

    $orderBy = "ORDER BY id DESC";
    $columnsResult = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'created_at'");
    if ($columnsResult && mysqli_num_rows($columnsResult) > 0) {
        $orderBy = "ORDER BY created_at DESC";
    }

    $result = mysqli_query($conn, "SELECT $select FROM products $orderBy LIMIT 24");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $imageVal = $imageCol && !empty($row[$imageCol]) ? $row[$imageCol] : 'data:image/svg+xml,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" width=\"400\" height=\"300\"%3E%3Crect fill=\"%23f1f3f5\" width=\"400\" height=\"300\"/%3E%3Ctext x=\"50%25\" y=\"50%25\" text-anchor=\"middle\" dy=\".3em\" fill=\"%23999\"%3ENo Image%3C/text%3E%3C/svg%3E';
            $discountVal = $discountCol && isset($row[$discountCol]) ? (int)$row[$discountCol] : 0;
            $originalVal = $originalCol && isset($row[$originalCol]) ? (float)$row[$originalCol] : null;
            $currentPrice = isset($row['price']) ? (float)$row['price'] : 0;

            // Only show products that an admin has marked with a deal (discount or higher original price)
            $hasDeal = ($discountVal > 0) || ($originalVal !== null && $originalVal > $currentPrice);
            if (!$hasDeal) {
                continue;
            }

            $basePrice = ($originalVal !== null && $originalVal > 0) ? $originalVal : $currentPrice;
            $finalPrice = $discountVal > 0 ? $basePrice * (1 - $discountVal / 100) : $currentPrice;

            $saleProducts[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'] ?? 'Product',
                'category' => $row['category'] ?? 'Category',
                'image' => $imageVal,
                'price' => $currentPrice,
                'finalPrice' => $finalPrice,
                'basePrice' => $basePrice,
                'discount' => $discountVal,
                'originalPrice' => $originalVal,
                'rating' => $ratingCol && isset($row[$ratingCol]) ? (int)$row[$ratingCol] : 4,
                'reviews' => $reviewsCol && isset($row[$reviewsCol]) ? (int)$row[$reviewsCol] : 0
            ];
        }
    }
} catch (Throwable $e) {
    // ignore and fallback to empty
}
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
                <span class="text-dark fw-medium">Deals</span>
            </div>
        </div>
    </div>

    <!-- Hero Banner -->
    <section class="py-5" style="background: linear-gradient(to right, #667eea, #fbbf24); color: white;">
        <div class="container px-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: white;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
                <span class="fs-2 fw-bold">FLASH SALES</span>
            </div>
            <h1 class="display-4 fw-bold mb-2">Incredible Deals on Premium Tech Gear</h1>
            <p class="fs-5" style="opacity: 0.9;">Save big on the technology you love. Limited time offers!</p>
        </div>
    </section>

    <!-- Products Grid -->
    <div class="container px-4 py-5">
        <div class="row g-4">
            <?php if (empty($saleProducts)): ?>
                <div class="col-12">
                    <div class="bg-white border rounded p-5 text-center">
                        <h3 class="fw-bold mb-2">No deals available</h3>
                        <p class="text-muted mb-0">
                            <?php if ($isAdmin ?? false): ?>
                                Set deals from the Admin Dashboard by editing products and adding a discount.
                            <?php else: ?>
                                Please check back later.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php else: ?>
            <?php foreach ($saleProducts as $product): ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                        <div class="bg-white rounded border overflow-hidden h-100 position-relative"
                            style="transition: all 0.3s;"
                            onmouseover="this.style.boxShadow='0 10px 25px rgba(0,0,0,0.1)'; this.style.borderColor='#667eea';"
                            onmouseout="this.style.boxShadow=''; this.style.borderColor='';">

                            <!-- Discount Badge -->
                            <?php if (!empty($product['discount'])): ?>
                                <div class="position-absolute top-0 end-0 m-3"
                                    style="background-color: #dc3545; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 1.1rem; z-index: 10; box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);">
                                    -<?php echo (int)$product['discount']; ?>%
                                </div>
                            <?php endif; ?>

                            <!-- Product Image -->
                            <div class="position-relative rounded-bottom"
                                style="height: 210px; background: radial-gradient(circle at 20% 20%, #eef2ff, #f8fafc); overflow: hidden;">
                                <div class="position-absolute top-0 start-0 w-100 h-100"
                                     style="background: linear-gradient(135deg, rgba(102,126,234,0.1), rgba(251,191,36,0.1)); mix-blend-mode: multiply;"></div>
                                <img src="<?php echo $product['image']; ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-100 h-100"
                                    style="object-fit: contain; padding: 12px; transition: transform 0.3s ease;"
                                    onmouseover="this.style.transform='scale(1.06)'"
                                    onmouseout="this.style.transform='scale(1)'">
                            </div>

                            <!-- Product Info -->
                            <div class="p-4">
                                <p class="text-muted small mb-2"><?php echo htmlspecialchars($product['category']); ?></p>
                                <h3 class="fw-semibold mb-3"
                                    style="min-height: 48px; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; color: #333;">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h3>

                                <!-- Rating -->
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="d-flex align-items-center gap-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $product['rating']): ?>
                                                <svg width="16" height="16" fill="#fbbf24" stroke="currentColor" viewBox="0 0 24 24"
                                                    class="text-warning">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                </svg>
                                            <?php else: ?>
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                    class="text-muted">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                </svg>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="text-muted small">(<?php echo $product['reviews']; ?>)</span>
                                </div>

                                <!-- Price -->
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="fs-4 fw-bold"
                                        style="color: #333;">$<?php echo number_format($product['finalPrice'] ?? $product['price'], 2); ?></span>
                                    <?php if (!empty($product['originalPrice']) || (!empty($product['basePrice']) && ($product['basePrice'] > ($product['finalPrice'] ?? $product['price'])))): ?>
                                        <span class="text-muted small text-decoration-line-through">$<?php echo number_format($product['basePrice'] ?? $product['originalPrice'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
