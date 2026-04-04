<?php
session_start();
include 'Database/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: admin-login.php');
    exit;
}

$title = "Edit Product";
include 'includes/header.php';

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product
$query = "SELECT * FROM products WHERE id = $product_id";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    $_SESSION['error_message'] = 'Product not found.';
    header('Location: admin-products.php');
    exit;
}

// Ensure deal columns exist so admins can set discounts
function ensureDealColumns($conn): array
{
    $hasDiscount = false;
    $hasOriginal = false;
    if ($cols = mysqli_query($conn, "SHOW COLUMNS FROM products")) {
        while ($col = mysqli_fetch_assoc($cols)) {
            if ($col['Field'] === 'discount') $hasDiscount = true;
            if ($col['Field'] === 'original_price') $hasOriginal = true;
        }
        mysqli_free_result($cols);
    }
    if (!$hasDiscount) {
        @mysqli_query($conn, "ALTER TABLE products ADD COLUMN discount INT DEFAULT 0");
        $hasDiscount = true;
    }
    if (!$hasOriginal) {
        @mysqli_query($conn, "ALTER TABLE products ADD COLUMN original_price DECIMAL(10,2) NULL");
        $hasOriginal = true;
    }
    return [$hasDiscount, $hasOriginal];
}

[$hasDiscountCol, $hasOriginalCol] = ensureDealColumns($conn);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = escapeInput($_POST['name']);
    $description = escapeInput($_POST['description']);
    $category = escapeInput($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $discount = isset($_POST['discount']) ? (int)$_POST['discount'] : 0;
    $originalPrice = isset($_POST['original_price']) && $_POST['original_price'] !== '' ? (float)$_POST['original_price'] : null;

    // Image handling: keep existing unless URL provided or new file uploaded
    $image_url = escapeInput($_POST['image_url'] ?? ($product['image_url'] ?? $product['image'] ?? ''));
    $uploadDir = 'images/';

    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileInfo = pathinfo($_FILES['image_file']['name']);
        $ext = strtolower($fileInfo['extension'] ?? '');
        if (in_array($ext, $allowedExt)) {
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            $targetName = 'prod_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $targetName;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetPath)) {
                $image_url = $targetPath;
            }
        }
    }

    // Detect which image column exists
    $imageColumn = 'image_url';
    if ($colRes = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'image_url'")) {
        if (mysqli_num_rows($colRes) === 0) {
            $imageColumn = 'image';
        }
        mysqli_free_result($colRes);
    }

    $setClauses = ['name = ?', 'category = ?', 'description = ?', 'price = ?', 'stock = ?'];
    $types = 'sssdi';
    $values = [$name, $category, $description, $price, $stock];

    if (!empty($imageColumn)) {
        $setClauses[] = "$imageColumn = ?";
        $types .= 's';
        $values[] = $image_url;
    }

    if ($hasDiscountCol) {
        $setClauses[] = "discount = ?";
        $types .= 'i';
        $values[] = $discount;
    }

    if ($hasOriginalCol) {
        $setClauses[] = "original_price = ?";
        $types .= 'd';
        $values[] = $originalPrice !== null ? $originalPrice : null;
    }

    $setSql = implode(', ', $setClauses);
    $stmt = mysqli_prepare($conn, "UPDATE products SET $setSql WHERE id = ?");
    if ($stmt) {
        $types .= 'i';
        $values[] = $product_id;
        mysqli_stmt_bind_param($stmt, $types, ...$values);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = 'Product updated successfully.';
            mysqli_stmt_close($stmt);
            header('Location: admin-products.php');
            exit;
        }
        $error = 'Error updating product: ' . mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $error = 'Error preparing update query: ' . mysqli_error($conn);
    }
}

$categories = ['Laptops', 'Desktops', 'Gaming', 'Accessories', 'Webcams'];
?>

<div class="min-vh-100" style="background-color: #f8f9fa;">
    <div class="bg-white border-bottom shadow-sm">
        <div class="container-fluid px-4 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold mb-0" style="color: #667eea;">Edit Product</h4>
                <a href="admin-products.php" class="btn btn-outline-secondary btn-sm">Back to Products</a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Product Name *</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                                        <span>Description *</span>
                                        <small class="text-muted">Keep it concise but helpful.</small>
                                    </label>
                                    <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Category *</label>
                                    <select name="category" class="form-select" required>
                                        <option value="">Select category...</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat; ?>" <?php echo $product['category'] === $cat ? 'selected' : ''; ?>>
                                                <?php echo $cat; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Image URL (optional)</label>
                                    <input type="text" name="image_url" class="form-control" value="<?php echo htmlspecialchars($product['image_url'] ?? $product['image'] ?? ''); ?>">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Price *</label>
                                    <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Stock *</label>
                                    <input type="number" name="stock" class="form-control" min="0" value="<?php echo $product['stock'] ?? 0; ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Discount % (optional)</label>
                                    <input type="number" name="discount" class="form-control" min="0" max="90" value="<?php echo (int)($product['discount'] ?? 0); ?>">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Original Price (optional)</label>
                                    <input type="number" step="0.01" name="original_price" class="form-control" value="<?php echo isset($product['original_price']) ? htmlspecialchars($product['original_price']) : ''; ?>" placeholder="e.g. 1299.00">
                                </div>

                                <div class="col-12">
                                    <small class="text-muted" id="deal-price-preview">Final price after discount: $0.00</small>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="d-inline-block me-1">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Update Product
                                    </button>
                                    <a href="admin-products.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script>
(function() {
    const priceInput = document.querySelector('input[name="price"]');
    const discountInput = document.querySelector('input[name="discount"]');
    const originalInput = document.querySelector('input[name="original_price"]');
    const helper = document.getElementById('deal-price-preview');

    function toNum(val) {
        const num = parseFloat(val);
        return Number.isFinite(num) ? num : 0;
    }

    function updatePreview() {
        if (!priceInput || !discountInput || !originalInput || !helper) return;
        const price = toNum(priceInput.value);
        const discount = toNum(discountInput.value);
        const original = toNum(originalInput.value);
        const base = original > 0 ? original : price;
        const final = base * (1 - (discount > 0 ? discount / 100 : 0));
        const display = final > 0 ? final : price;
        helper.textContent = 'Final price after discount: $' + display.toFixed(2);
    }

    ['input', 'change'].forEach(evt => {
        priceInput?.addEventListener(evt, updatePreview);
        discountInput?.addEventListener(evt, updatePreview);
        originalInput?.addEventListener(evt, updatePreview);
    });
    updatePreview();
})();
</script>
