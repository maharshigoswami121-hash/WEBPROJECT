<?php
session_start();
include 'Database/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: admin-login.php');
    exit;
}

$title = "Admin Dashboard";
include 'includes/header.php';

// Get statistics - handle missing role column
$roleColumnExists = false;
if ($roleResult = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'")) {
    $roleColumnExists = mysqli_num_rows($roleResult) > 0;
    mysqli_free_result($roleResult);
}

$users_query = $roleColumnExists 
    ? "SELECT COUNT(*) as count FROM users WHERE role = 'user'" 
    : "SELECT COUNT(*) as count FROM users";
$users_count = mysqli_fetch_assoc(mysqli_query($conn, $users_query))['count'] ?? 0;

$products_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'] ?? 0;
$orders_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'] ?? 0;

// Check if status column exists in orders table
$statusColumnExists = false;
if ($statusResult = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'status'")) {
    $statusColumnExists = mysqli_num_rows($statusResult) > 0;
    mysqli_free_result($statusResult);
}

$revenue_query = $statusColumnExists
    ? "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'"
    : "SELECT SUM(total_amount) as total FROM orders";
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, $revenue_query))['total'] ?? 0;
?>

<div class="min-vh-100" style="background-color: #f8f9fa;">
    <!-- Admin Header -->
    <div class="bg-white border-bottom shadow-sm">
        <div class="container-fluid px-4 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-0" style="color: #667eea;">Admin Dashboard</h4>
                    <small class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['admin_user']['first_name']); ?>!</small>
                </div>
                <a href="admin-logout.php" class="btn btn-outline-danger btn-sm">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="d-inline-block me-1">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <div class="row g-4">
            <!-- Statistics Cards -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Users</h6>
                                <h3 class="fw-bold mb-0"><?php echo $users_count; ?></h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <svg width="24" height="24" fill="none" stroke="#667eea" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Products</h6>
                                <h3 class="fw-bold mb-0"><?php echo $products_count; ?></h3>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <svg width="24" height="24" fill="none" stroke="#28a745" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Orders</h6>
                                <h3 class="fw-bold mb-0"><?php echo $orders_count; ?></h3>
                            </div>
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <svg width="24" height="24" fill="none" stroke="#17a2b8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Revenue</h6>
                                <h3 class="fw-bold mb-0">$<?php echo number_format($total_revenue, 2); ?></h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <svg width="24" height="24" fill="none" stroke="#ffc107" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Navigation -->
        <div class="row g-4 mt-2">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Admin Functions</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="admin-products.php" class="text-decoration-none">
                                    <div class="border rounded p-4 text-center h-100" style="transition: all 0.3s; cursor: pointer;" 
                                         onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.borderColor='#667eea';" 
                                         onmouseout="this.style.backgroundColor=''; this.style.borderColor='';">
                                        <svg width="48" height="48" fill="none" stroke="#667eea" viewBox="0 0 24 24" class="mb-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        <h6 class="fw-semibold mb-0">Manage Products</h6>
                                        <small class="text-muted">Add, edit, delete products</small>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-3">
                                <a href="admin-orders.php" class="text-decoration-none">
                                    <div class="border rounded p-4 text-center h-100" style="transition: all 0.3s; cursor: pointer;" 
                                         onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.borderColor='#667eea';" 
                                         onmouseout="this.style.backgroundColor=''; this.style.borderColor='';">
                                        <svg width="48" height="48" fill="none" stroke="#667eea" viewBox="0 0 24 24" class="mb-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        <h6 class="fw-semibold mb-0">View Orders</h6>
                                        <small class="text-muted">View all customer orders</small>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-3">
                                <a href="admin-users.php" class="text-decoration-none">
                                    <div class="border rounded p-4 text-center h-100" style="transition: all 0.3s; cursor: pointer;" 
                                         onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.borderColor='#667eea';" 
                                         onmouseout="this.style.backgroundColor=''; this.style.borderColor='';">
                                        <svg width="48" height="48" fill="none" stroke="#667eea" viewBox="0 0 24 24" class="mb-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <h6 class="fw-semibold mb-0">Manage Users</h6>
                                        <small class="text-muted">View and manage user accounts</small>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-3">
                                <a href="admin.php" class="text-decoration-none">
                                    <div class="border rounded p-4 text-center h-100" style="transition: all 0.3s; cursor: pointer;" 
                                         onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.borderColor='#667eea';" 
                                         onmouseout="this.style.backgroundColor=''; this.style.borderColor='';">
                                        <svg width="48" height="48" fill="none" stroke="#667eea" viewBox="0 0 24 24" class="mb-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <h6 class="fw-semibold mb-0">Registered Users</h6>
                                        <small class="text-muted">View all registered users</small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

