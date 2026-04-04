<?php
$title = "Products";
include 'includes/header.php';
require_once 'Database/db.php';

// Define categories
$categories = ['All', 'Laptops', 'Desktops', 'Gaming', 'Accessories', 'Webcams'];

// Normalize image paths (support remote URLs, data URIs, and local uploads)
function normalizeProductImage($path)
{
    $placeholder = 'data:image/svg+xml,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" width=\"400\" height=\"300\"%3E%3Crect fill=\"%23f1f3f5\" width=\"400\" height=\"300\"/%3E%3Ctext x=\"50%25\" y=\"50%25\" text-anchor=\"middle\" dy=\".3em\" fill=\"%23999\"%3ENo Image%3C/text%3E%3C/svg%3E';
    if (empty($path)) {
        return $placeholder;
    }
    $path = trim($path);
    $path = str_replace('\\', '/', $path);
    if (str_starts_with($path, './')) {
        $path = substr($path, 2);
    }
    // If remote or data URI, return as-is
    if (preg_match('~^(https?:)?//~i', $path) || str_starts_with($path, 'data:')) {
        return $path;
    }
    // If absolute path within project, trim to web relative
    $projectRoot = realpath(__DIR__);
    $absolutePath = $path;
    if (!str_starts_with($absolutePath, $projectRoot)) {
        $absolutePath = $projectRoot . '/' . ltrim($path, '/');
    }
    if (file_exists($absolutePath)) {
        // make relative to project root for web serving
        if (str_starts_with($absolutePath, $projectRoot)) {
            $relativePath = ltrim(str_replace($projectRoot, '', $absolutePath), '/');
            return $relativePath;
        }
        return ltrim($path, '/');
    }
    // If file not found, try basename inside images folder
    $basename = basename($path);
    if ($basename && file_exists($projectRoot . '/images/' . $basename)) {
        return 'images/' . $basename;
    }
    return $placeholder;
}

function categoryMatches($productCategory, $selectedCategory)
{
    if ($selectedCategory === 'All') {
        return true;
    }
    return strcasecmp(trim((string) $productCategory), trim((string) $selectedCategory)) === 0;
}

// Get selected category from URL
$selectedCategory = isset($_GET['category']) ? ucfirst($_GET['category']) : 'All';
if (!in_array($selectedCategory, $categories)) {
    $selectedCategory = 'All';
}

// Fetch products from DB so admin-added items appear automatically
$productsList = [];
$productsError = '';
try {
    // Discover available columns to build a safe SELECT
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
        if (isset($availableCols[$field])) {
            $selectFields[] = $field;
        }
    }
    if ($imageCol)
        $selectFields[] = $imageCol;
    if ($stockCol)
        $selectFields[] = $stockCol;
    if ($ratingCol)
        $selectFields[] = $ratingCol;
    if ($reviewsCol)
        $selectFields[] = $reviewsCol;
    if ($discountCol)
        $selectFields[] = $discountCol;
    if ($originalCol)
        $selectFields[] = $originalCol;
    $select = implode(', ', $selectFields);

    $orderBy = "ORDER BY id DESC";
    $products_columns_result = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'created_at'");
    if ($products_columns_result && mysqli_num_rows($products_columns_result) > 0) {
        $orderBy = "ORDER BY created_at DESC";
    }

    $products_result = mysqli_query($conn, "SELECT $select FROM products $orderBy");
    if ($products_result) {
        while ($row = mysqli_fetch_assoc($products_result)) {
            $imageVal = '';
            if ($imageCol && !empty($row[$imageCol])) {
                $imageVal = $row[$imageCol];
            } elseif (isset($availableCols['image']) && !empty($row['image'])) {
                $imageVal = $row['image'];
            }
            $imageVal = normalizeProductImage($imageVal);
            $stockVal = $stockCol && isset($row[$stockCol]) ? (int) $row[$stockCol] : 0;
            $discountVal = $discountCol && isset($row[$discountCol]) ? (int) $row[$discountCol] : 0;
            $originalVal = $originalCol && isset($row[$originalCol]) ? (float) $row[$originalCol] : null;
            $basePrice = $originalVal !== null && $originalVal > 0 ? $originalVal : (isset($row['price']) ? (float)$row['price'] : 0);
            $finalPrice = $discountVal > 0 ? $basePrice * (1 - $discountVal / 100) : (isset($row['price']) ? (float)$row['price'] : 0);
            $productsList[] = [
                'id' => (int) $row['id'],
                'name' => $row['name'] ?? 'Untitled Product',
                'category' => $row['category'] ?? 'Misc',
                'description' => $row['description'] ?? '',
                'price' => isset($row['price']) ? (float) $row['price'] : 0,
                'basePrice' => $basePrice,
                'finalPrice' => $finalPrice,
                'discount' => $discountVal,
                'originalPrice' => $originalVal,
                'stock' => $stockVal,
                'image' => $imageVal,
                'rating' => $ratingCol && isset($row[$ratingCol]) ? (int) $row[$ratingCol] : 4,
                'reviews' => $reviewsCol && isset($row[$reviewsCol]) ? (int) $row[$reviewsCol] : 0,
            ];
        }
    }
} catch (Throwable $e) {
    $productsError = 'Unable to load products.';
}

// Fallback query if primary lookup failed or returned nothing
if (empty($productsList)) {
    $fallback = @mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
    if ($fallback) {
        while ($row = mysqli_fetch_assoc($fallback)) {
            $imageVal = '';
            if (!empty($row['image_url'])) {
                $imageVal = $row['image_url'];
            } elseif (!empty($row['image'])) {
                $imageVal = $row['image'];
            }
            $imageVal = normalizeProductImage($imageVal);
            $stockVal = isset($row['stock']) ? (int) $row['stock'] : (isset($row['stock_quantity']) ? (int) $row['stock_quantity'] : 0);
            $discountVal = isset($row['discount']) ? (int)$row['discount'] : 0;
            $originalVal = isset($row['original_price']) ? (float)$row['original_price'] : null;
            $basePrice = $originalVal !== null && $originalVal > 0 ? $originalVal : (isset($row['price']) ? (float)$row['price'] : 0);
            $finalPrice = $discountVal > 0 ? $basePrice * (1 - $discountVal / 100) : (isset($row['price']) ? (float)$row['price'] : 0);
            $productsList[] = [
                'id' => (int) ($row['id'] ?? 0),
                'name' => $row['name'] ?? 'Untitled Product',
                'category' => $row['category'] ?? 'Misc',
                'description' => $row['description'] ?? '',
                'price' => isset($row['price']) ? (float) $row['price'] : 0,
                'basePrice' => $basePrice,
                'finalPrice' => $finalPrice,
                'discount' => $discountVal,
                'originalPrice' => $originalVal,
                'stock' => $stockVal,
                'image' => $imageVal,
                'rating' => isset($row['rating']) ? (int) $row['rating'] : 4,
                'reviews' => isset($row['reviews']) ? (int) $row['reviews'] : 0,
            ];
        }
    } elseif ($productsError === '') {
        $productsError = 'Error loading products: ' . mysqli_error($conn);
    }
}

// Apply category filter server-side so only matching products render
if ($selectedCategory !== 'All') {
    $productsList = array_values(array_filter($productsList, function ($item) use ($selectedCategory) {
        return categoryMatches($item['category'] ?? '', $selectedCategory);
    }));
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
                <span class="text-dark fw-medium">Products</span>
            </div>
        </div>
    </div>

    <div class="container px-4 py-5">
        <div class="row g-4">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="bg-white rounded border p-4">
                    <h3 class="fw-bold mb-4">Categories</h3>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($categories as $cat): ?>
                            <a href="products.php?category=<?php echo $cat === 'All' ? '' : strtolower($cat); ?>"
                                class="text-decoration-none px-3 py-2 rounded <?php echo ($selectedCategory === $cat || ($cat === 'All' && $selectedCategory === 'All')) ? 'bg-primary text-white fw-semibold' : 'text-dark'; ?>"
                                style="<?php echo ($selectedCategory === $cat || ($cat === 'All' && $selectedCategory === 'All')) ? '' : 'transition: background-color 0.2s;'; ?>"
                                onmouseover="<?php echo ($selectedCategory === $cat || ($cat === 'All' && $selectedCategory === 'All')) ? '' : "this.style.backgroundColor='#f8f9fa'"; ?>"
                                onmouseout="<?php echo ($selectedCategory === $cat || ($cat === 'All' && $selectedCategory === 'All')) ? '' : "this.style.backgroundColor=''"; ?>">
                                <?php echo $cat; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Price Filter -->
                    <div class="mt-5 pt-4 border-top">
                        <h4 class="fw-semibold mb-4">Price Range</h4>
                        <div class="d-flex flex-column gap-3">
                            <label class="d-flex align-items-center gap-3" style="cursor: pointer;">
                                <input type="checkbox" class="form-check-input price-filter" data-min="0"
                                    data-max="100">
                                <span class="small">Under $100</span>
                            </label>
                            <label class="d-flex align-items-center gap-3" style="cursor: pointer;">
                                <input type="checkbox" class="form-check-input price-filter" data-min="100"
                                    data-max="500">
                                <span class="small">$100 - $500</span>
                            </label>
                            <label class="d-flex align-items-center gap-3" style="cursor: pointer;">
                                <input type="checkbox" class="form-check-input price-filter" data-min="500"
                                    data-max="1000">
                                <span class="small">$500 - $1000</span>
                            </label>
                            <label class="d-flex align-items-center gap-3" style="cursor: pointer;">
                                <input type="checkbox" class="form-check-input price-filter" data-min="1000"
                                    data-max="999999">
                                <span class="small">Over $1000</span>
                            </label>
                        </div>
                    </div>

                    <!-- Rating Filter -->
                    <div class="mt-5 pt-4 border-top">
                        <h4 class="fw-semibold mb-4">Rating</h4>
                        <div class="d-flex flex-column gap-3">
                            <?php for ($stars = 5; $stars >= 1; $stars--): ?>
                                <label class="d-flex align-items-center gap-3" style="cursor: pointer;">
                                    <input type="checkbox" <?php echo $stars >= 4 ?: ''; ?>
                                        class="form-check-input rating-filter" data-rating="<?php echo $stars; ?>">
                                    <span class="small"><?php echo $stars; ?>
                                        <?php echo $stars === 1 ? 'Star' : 'Stars'; ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-md-9">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                    <div class="mb-3 mb-md-0">
                        <h2 class="fw-bold mb-1" style="font-size: 1.75rem;">
                            <?php echo $selectedCategory === 'All' ? 'All Products' : $selectedCategory; ?>
                        </h2>
                        <p class="text-muted mb-0">
                            Showing <span id="product-count"><?php echo count($productsList); ?></span> products
                            <?php if (!empty($productsError)): ?>
                                <br><small class="text-danger"><?php echo htmlspecialchars($productsError); ?></small>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="row g-4" id="products-container">
                    <?php foreach ($productsList as $product): ?>
                        <?php
                        $imageSrc = !empty($product['image']) ? $product['image'] : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300"%3E%3Crect fill="%23f1f3f5" width="400" height="300"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%23999"%3ENo Image%3C/text%3E%3C/svg%3E';
                        $rating = isset($product['rating']) ? max(0, min(5, (int) $product['rating'])) : 0;
                        $reviews = isset($product['reviews']) ? (int) $product['reviews'] : 0;
                        $hasDiscount = !empty($product['discount']);
                        $finalPrice = isset($product['finalPrice']) ? (float)$product['finalPrice'] : (isset($product['price']) ? (float)$product['price'] : 0);
                        $basePrice = isset($product['basePrice']) ? (float)$product['basePrice'] : (isset($product['price']) ? (float)$product['price'] : 0);
                        ?>
                        <div class="col-12 col-sm-6 col-lg-4 product-item"
                            data-price="<?php echo htmlspecialchars($product['price']); ?>"
                            data-rating="<?php echo $rating; ?>"
                            data-category="<?php echo htmlspecialchars($product['category']); ?>">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                                <div class="bg-white rounded border overflow-hidden h-100 product-card" style="transition: all 0.3s;">
                                    <div class="position-relative d-flex align-items-center justify-content-center"
                                        style="height: 170px; background: linear-gradient(135deg, #f8f9fb 0%, #eef1f6 100%); overflow: hidden;">
                                        <?php if ($hasDiscount): ?>
                                            <span class="position-absolute top-0 start-0 m-3 badge bg-danger">-<?php echo (int)$product['discount']; ?>%</span>
                                        <?php endif; ?>
                                        <img src="<?php echo htmlspecialchars($imageSrc); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-100 h-100"
                                            style="object-fit: contain; padding: 12px;"
                                            onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect fill=%22%23f1f3f5%22 width=%22400%22 height=%22300%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22%3ENo Image%3C/text%3E%3C/svg%3E';">
                                    </div>
                                    <div class="p-4">
                                        <p class="text-muted small mb-2">
                                            <?php echo htmlspecialchars($product['category']); ?>
                                        </p>
                                        <h3 class="fw-semibold mb-3"
                                            style="min-height: 48px; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; color: #333;">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </h3>
                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <div class="d-flex align-items-center gap-1">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <svg width="16" height="16"
                                                        fill="<?php echo $i <= $rating ? '#fbbf24' : 'none'; ?>"
                                                        stroke="currentColor" viewBox="0 0 24 24"
                                                        class="<?php echo $i <= $rating ? 'text-warning' : 'text-muted'; ?>">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                    </svg>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="text-muted small">(<?php echo $reviews; ?>)</span>
                                        </div>
                                        <div class="d-flex align-items-baseline justify-content-between">
                                            <span class="fs-4 fw-bold"
                                                style="color: #333;">$<?php echo number_format($finalPrice, 2); ?></span>
                                            <?php if ($hasDiscount && $basePrice > $finalPrice): ?>
                                                <span class="text-muted text-decoration-line-through">$<?php echo number_format($basePrice, 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
        </div>
    </div>

        <?php include 'includes/footer.php'; ?>
