<?php
session_start();
$title = "Login";
$isAdminLogin = isset($_GET['admin']); // same page supports admin sign-in
include 'includes/header.php';

$error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
unset($_SESSION['login_error']);
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
                <span class="text-dark fw-medium">Login</span>
            </div>
        </div>
    </div>

    <!-- Login Section -->
    <section class="py-5">
        <div class="container px-4">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <div class="bg-white rounded border p-5 shadow-sm">
                        <div class="text-center mb-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                style="width: 80px; height: 80px;">
                                <svg width="40" height="40" fill="none" stroke="#667eea" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h2 class="fw-bold mb-2" style="color: #333;">Welcome Back</h2>
                            <p class="text-muted mb-0">
                                <?php echo $isAdminLogin ? 'Admin access' : 'Sign in to your account to continue'; ?>
                            </p>
                        </div>

                        <?php if ($isAdminLogin): ?>
                            <div class="alert alert-info small">
                                Use your admin credentials to access the dashboard.
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="login-process.php">
                            <?php if ($isAdminLogin): ?>
                                <input type="hidden" name="admin" value="1">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="color: #333;">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <svg width="20" height="20" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" style="color: #667eea;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                    <input type="email" name="email" class="form-control border-start-0"
                                        placeholder="Enter your email" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold" style="color: #333;">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <svg width="20" height="20" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" style="color: #667eea;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </span>
                                    <input type="password" name="password" class="form-control border-start-0"
                                        placeholder="Enter your password" required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberMe">
                                    <label class="form-check-label small text-muted" for="rememberMe">
                                        Remember me
                                    </label>
                                </div>
                                <a href="#" class="text-decoration-none small" style="color: #667eea;">Forgot
                                    password?</a>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 btn-lg mb-3"
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                Sign In
                            </button>
                        </form>

                        <div class="text-center mt-4 pt-4 border-top">
                            <p class="text-muted mb-0 small">
                                Don't have an account?
                                <a href="register.php" class="text-decoration-none fw-semibold"
                                    style="color: #667eea;">Sign up here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
