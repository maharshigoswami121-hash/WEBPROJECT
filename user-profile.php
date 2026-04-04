<?php
session_start();
if (!isset($_SESSION['user'])) {
    $_SESSION['login_error'] = 'Please login to view your profile.';
    header('Location: login.php');
    exit;
}

include 'Database/db.php';

$userId = (int) $_SESSION['user']['id'];
$errorMessage = '';
$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

// Determine available columns
$selectFields = "first_name, last_name, email, address, city, state, postal_code, phone, country";
$createdAtExists = false;

if ($columnResult = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'created_at'")) {
    $createdAtExists = mysqli_num_rows($columnResult) > 0;
    mysqli_free_result($columnResult);
}

if ($createdAtExists) {
    $selectFields .= ", created_at";
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = escapeInput($_POST['first_name'] ?? '');
    $last = escapeInput($_POST['last_name'] ?? '');
    $email = escapeInput($_POST['email'] ?? '');
    $phone = escapeInput($_POST['phone'] ?? '');
    $address = escapeInput($_POST['address'] ?? '');
    $city = escapeInput($_POST['city'] ?? '');
    $state = escapeInput($_POST['state'] ?? '');
    $postal = escapeInput($_POST['postal_code'] ?? '');
    $country = escapeInput($_POST['country'] ?? '');

    if ($first === '' || $last === '' || $email === '') {
        $errorMessage = 'First name, last name, and email are required.';
    } else {
        // Ensure email is unique for other users
        $emailCheck = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
        mysqli_stmt_bind_param($emailCheck, 'si', $email, $userId);
        mysqli_stmt_execute($emailCheck);
        mysqli_stmt_store_result($emailCheck);
        if (mysqli_stmt_num_rows($emailCheck) > 0) {
            $errorMessage = 'That email is already in use by another account.';
        }
        mysqli_stmt_close($emailCheck);
    }

    if ($errorMessage === '') {
        $updateSql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, city = ?, state = ?, postal_code = ?, country = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($stmt, 'sssssssssi', $first, $last, $email, $phone, $address, $city, $state, $postal, $country, $userId);
        if (mysqli_stmt_execute($stmt)) {
            // Refresh session data for header display
            $_SESSION['user']['first_name'] = $first;
            $_SESSION['user']['last_name'] = $last;
            $_SESSION['user']['email'] = $email;
            $_SESSION['success_message'] = 'Profile updated successfully.';
            mysqli_stmt_close($stmt);
            header('Location: user-profile.php');
            exit;
        } else {
            $errorMessage = 'Unable to update your profile. Please try again.';
        }
        mysqli_stmt_close($stmt);
    }
}

$query = "SELECT $selectFields FROM users WHERE id = $userId LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    $_SESSION['login_error'] = 'Unable to load your profile. Please login again.';
    header('Location: login.php');
    exit;
}

$userProfile = mysqli_fetch_assoc($result);
$title = "My Profile";
include 'includes/header.php';

$formValue = function ($key) use ($userProfile) {
    return htmlspecialchars($_POST[$key] ?? $userProfile[$key] ?? '');
};
?>

<div class="min-vh-100" style="background-color: #f8f9fa;">
    <div class="bg-light border-bottom">
        <div class="container px-4 py-3">
            <div class="d-flex align-items-center gap-2" style="font-size: 0.875rem;">
                <a href="index.php" class="text-decoration-none" style="color: #667eea;">Home</a>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="text-muted">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-dark fw-medium">My Profile</span>
            </div>
        </div>
    </div>

    <div class="container px-4 py-5">
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-4 justify-content-between flex-wrap">
                            <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 70px; height: 70px;">
                                <span class="text-primary fw-bold" style="font-size: 1.5rem;">
                                    <?php echo strtoupper(substr($userProfile['first_name'], 0, 1)); ?>
                                </span>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0" style="color: #333;">
                                    <?php echo htmlspecialchars($userProfile['first_name'] . ' ' . $userProfile['last_name']); ?>
                                </h3>
                                <?php if ($createdAtExists && !empty($userProfile['created_at'])): ?>
                                    <p class="text-muted mb-0">
                                        Member since <?php echo date('M Y', strtotime($userProfile['created_at'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="show-edit-form-btn">Edit</button>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted small mb-1">Email</p>
                                    <p class="fw-semibold mb-0"><?php echo htmlspecialchars($userProfile['email']); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted small mb-1">Phone</p>
                                    <p class="fw-semibold mb-0">
                                        <?php echo !empty($userProfile['phone']) ? htmlspecialchars($userProfile['phone']) : 'Not provided'; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted small mb-1">Country</p>
                                    <p class="fw-semibold mb-0">
                                        <?php echo !empty($userProfile['country']) ? htmlspecialchars($userProfile['country']) : 'Not provided'; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted small mb-1">Address</p>
                                    <p class="fw-semibold mb-0">
                                        <?php echo htmlspecialchars($userProfile['address']); ?><br>
                                        <?php echo htmlspecialchars($userProfile['city'] . ', ' . $userProfile['state']); ?>
                                        <?php echo htmlspecialchars($userProfile['postal_code']); ?><br>
                                        <?php echo htmlspecialchars($userProfile['country']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" id="edit-profile-form" style="display: none;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3">Edit Profile</h4>
                        <form method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo $formValue('first_name'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo $formValue('last_name'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo $formValue('email'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo $formValue('phone'); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Address</label>
                                <input type="text" name="address" class="form-control" value="<?php echo $formValue('address'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">City</label>
                                <input type="text" name="city" class="form-control" value="<?php echo $formValue('city'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">State</label>
                                <input type="text" name="state" class="form-control" value="<?php echo $formValue('state'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Postal Code</label>
                                <input type="text" name="postal_code" class="form-control" value="<?php echo $formValue('postal_code'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Country</label>
                                <input type="text" name="country" class="form-control" value="<?php echo $formValue('country'); ?>">
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2">
                                <a href="products.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
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
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('show-edit-form-btn');
    const formCard = document.getElementById('edit-profile-form');
    if (editBtn && formCard) {
        editBtn.addEventListener('click', function() {
            formCard.style.display = 'block';
            formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
});
</script>
