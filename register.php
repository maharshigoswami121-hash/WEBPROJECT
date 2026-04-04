<?php
session_start();
$title = "Register";
include 'includes/header.php';

// Get errors and form data from session
$errors = isset($_SESSION['registration_errors']) ? $_SESSION['registration_errors'] : [];
$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['registration_errors']);
unset($_SESSION['form_data']);
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
                <span class="text-dark fw-medium">Register</span>
            </div>
        </div>
    </div>

    <!-- Register Section -->
    <section class="py-5">
        <div class="container px-4">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="bg-white rounded border p-5 shadow-sm">
                        <div class="text-center mb-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                style="width: 80px; height: 80px;">
                                <svg width="40" height="40" fill="none" stroke="#667eea" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <h2 class="fw-bold mb-2" style="color: #333;">Create Your Account</h2>
                            <p class="text-muted mb-0">Join Premium Collection and start shopping today</p>
                        </div>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form class="row g-3 needs-validation" action="signup.php" method="POST" novalidate>
                            <!-- Personal Information Section -->
                            <div class="col-12">
                                <h5 class="fw-semibold mb-3"
                                    style="color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 0.5rem; display: inline-block;">
                                    Personal Information
                                </h5>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="color: #333;">First Name</label>
                                <input type="text" name="firstname" class="form-control"
                                    placeholder="Enter your first name" required>
                                <div class="invalid-feedback">Please enter your first name.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="color: #333;">Last Name</label>
                                <input type="text" name="lastname" class="form-control"
                                    placeholder="Enter your last name" required>
                                <div class="invalid-feedback">Please enter your last name.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="color: #333;">User Name</label>
                                <input type="text" name="username" class="form-control" placeholder="Choose a username"
                                    required>
                                <div class="invalid-feedback">Please choose a username.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="color: #333;">Email</label>
                                <input type="email" name="email" class="form-control"
                                    placeholder="Enter your email address" required>
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>

                            <!-- Account Security Section -->
                            <div class="col-12 mt-3">
                                <h5 class="fw-semibold mb-3"
                                    style="color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 0.5rem; display: inline-block;">
                                    Account Security
                                </h5>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="color: #333;">Password</label>
                                <input type="password" name="password" class="form-control"
                                    placeholder="Create a password" required>
                                <div class="invalid-feedback">Please enter a password.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="color: #333;">Confirm Password</label>
                                <input type="password" name="confirmpassword" class="form-control"
                                    placeholder="Confirm your password" required>
                                <div class="invalid-feedback">Passwords must match.</div>
                            </div>

                            <!-- Address Information Section -->
                            <div class="col-12 mt-3">
                                <h5 class="fw-semibold mb-3"
                                    style="color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 0.5rem; display: inline-block;">
                                    Address Information
                                </h5>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold" style="color: #333;">Address</label>
                                <input type="text" name="address" class="form-control"
                                    placeholder="Enter your street address" required>
                                <div class="invalid-feedback">Please enter your address.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="color: #333;">City</label>
                                <input type="text" name="city" class="form-control" placeholder="Enter your city"
                                    required>
                                <div class="invalid-feedback">Please enter a valid city.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="color: #333;">Province</label>
                                <select class="form-select" name="province" required>
                                    <option value="" selected disabled>Choose province...</option>
                                    <option>ON</option>
                                    <option>QC</option>
                                    <option>BC</option>
                                    <option>AB</option>
                                    <option>MB</option>
                                    <option>NS</option>
                                </select>
                                <div class="invalid-feedback">Please select a province.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="color: #333;">Postal Code</label>
                                <input type="text" name="postal" class="form-control" placeholder="A1A 1A1" required>
                                <div class="invalid-feedback">Please enter a valid postal code.</div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label small text-muted" for="terms">
                                        I agree to the <a href="#" class="text-decoration-none"
                                            style="color: #667eea;">Terms and Conditions</a> and <a href="#"
                                            class="text-decoration-none" style="color: #667eea;">Privacy Policy</a>
                                    </label>
                                    <div class="invalid-feedback">You must agree to the terms and conditions.</div>
                                </div>
                            </div>

                            <div class="col-12 text-center mt-4">
                                <button class="btn btn-primary btn-lg px-5" type="submit"
                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                    Create Account
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4 pt-4 border-top">
                            <p class="text-muted mb-0 small">
                                Already have an account?
                                <a href="login.php" class="text-decoration-none fw-semibold"
                                    style="color: #667eea;">Sign in here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    // Bootstrap form validation
    (function () {
        'use strict';
        window.addEventListener('load', function () {
            var forms=document.getElementsByClassName('needs-validation');
            var validation=Array.prototype.filter.call(forms, function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.checkValidity()===false)
                    {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>

<?php include 'includes/footer.php'; ?>