<?php
$title = $title ?? "Home Page";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user']);
$isAdmin = (isset($_SESSION['admin']) && $_SESSION['admin'] === true) || (isset($_SESSION['admin_user']) && !empty($_SESSION['admin_user']));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css">
    <script>
        // Set login status for JavaScript
        window.userLoggedIn=<?php echo $isLoggedIn ? 'true' : 'false'; ?>;
    </script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a href="index.php" class="navbar-brand d-flex align-items-center gap-2">
                <div class="logo-icon">
                    <svg class="logo-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
                <span class="logo-text">Premium Collection</span>
            </a>
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">

                <!-- Custom SVG Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6" />
                    <line x1="3" y1="12" x2="21" y2="12" />
                    <line x1="3" y1="18" x2="21" y2="18" />
                </svg>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : ''; ?>"
                            href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'deals.php') ? 'active' : ''; ?>"
                            href="deals.php">Deals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : ''; ?>"
                            href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $isLoggedIn ? 'cart.php' : 'login.php'; ?>" class="cart-link">
                            <svg class="cart-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="cart-badge">0</span>
                        </a>
                    </li>
                    <?php if ($isAdmin): ?>
                        <li class="nav-item">
                            <a class="nav-link ms-lg-2 px-3 py-2 text-white fw-semibold <?php echo (basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php') ? 'active' : ''; ?>"
                                href="admin-dashboard.php"
                                style="border-radius: 999px; box-shadow: 0 6px 18px rgba(79, 70, 229, 0.25); display: inline-flex; align-items: center; gap: 8px;">
                                Admin Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-logout.php">Logout</a>
                        </li>
                    <?php elseif ($isLoggedIn): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?php echo (basename($_SERVER['PHP_SELF']) == 'user-profile.php') ? 'active' : ''; ?>"
                                href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($_SESSION['user']['first_name'] ?? 'Account'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="user-profile.php">My Profile</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="logout.php">Logout</a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item d-lg-none w-100 mt-2">
                            <div class="d-grid gap-2">
                                <a href="login.php" class="btn btn-light fw-semibold">Login</a>
                                <a href="register.php" class="btn btn-warning fw-semibold">Register</a>
                            </div>
                        </li>
                        <li class="nav-item d-none d-sm-flex">
                            <div class="auth-buttons d-flex align-items-center gap-2 ms-2">
                                <a href="login.php" class="btn-signin">Login</a>
                                <a href="register.php" class="btn-signup">Register</a>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
