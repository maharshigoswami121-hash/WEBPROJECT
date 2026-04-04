<?php
session_start();
include 'Database/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: admin-login.php');
    exit;
}

$title = "Manage Users";

// Defaults so the page still renders if the users table is missing columns
$hasRole = false;
$hasCreatedAt = false;
$users = [];

try {
    // Detect optional columns
    if ($colRes = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'")) {
        $hasRole = mysqli_num_rows($colRes) > 0;
        mysqli_free_result($colRes);
    }
    if ($colRes = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'created_at'")) {
        $hasCreatedAt = mysqli_num_rows($colRes) > 0;
        mysqli_free_result($colRes);
    }

    // Handle delete
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $id = (int) $_GET['delete'];

        $canDelete = true;
        if ($hasRole) {
            $check_query = "SELECT role FROM users WHERE id = $id";
            if ($check_result = mysqli_query($conn, $check_query)) {
                $user = mysqli_fetch_assoc($check_result);
                mysqli_free_result($check_result);
                if ($user && ($user['role'] ?? '') === 'admin') {
                    $canDelete = false;
                }
            }
        }

        if ($canDelete) {
            $delete_query = "DELETE FROM users WHERE id = $id";
            if (mysqli_query($conn, $delete_query)) {
                $_SESSION['success_message'] = 'User deleted successfully.';
            } else {
                $_SESSION['error_message'] = 'Error deleting user.';
            }
        } else {
            $_SESSION['error_message'] = 'Cannot delete admin user.';
        }
        header('Location: admin-users.php');
        exit;
    }

    // Fetch all users (excluding current admin)
    $admin_id = (int) $_SESSION['admin_user']['id'];
    $select = "id, first_name, last_name, username, email, address, city, state, postal_code";
    if ($hasRole) {
        $select .= ", role";
    }
    if ($hasCreatedAt) {
        $select .= ", created_at";
    }

    $orderBy = $hasCreatedAt ? "ORDER BY created_at DESC" : "";
    $query = "SELECT $select FROM users WHERE id != $admin_id $orderBy";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {
    $_SESSION['error_message'] = 'Unable to load users. Please verify the users table exists and try again.';
}

$success = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

include 'includes/header.php';
?>

<div class="min-vh-100" style="background-color: #f8f9fa;">
    <div class="bg-white border-bottom shadow-sm">
        <div class="container-fluid px-4 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold mb-0" style="color: #667eea;">Manage Users</h4>
                <a href="admin-dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($users)): ?>
                    <div class="p-5 text-center">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            class="text-muted mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <h5 class="text-muted">No users found</h5>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4">ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Username</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Address</th>
                                    <th scope="col">Role</th>
                                    <th scope="col" class="pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user):
                                    $full_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
                                    $username = isset($user['username']) && !empty($user['username']) ? htmlspecialchars($user['username']) : 'N/A';
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold" style="color: #667eea;">#<?php echo $user['id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2"
                                                    style="width: 36px; height: 36px;">
                                                    <span class="text-primary fw-bold" style="font-size: 0.875rem;">
                                                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                                    </span>
                                                </div>
                                                <span class="fw-medium"><?php echo $full_name; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                @<?php echo $username; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>"
                                                class="text-decoration-none" style="color: #667eea;">
                                                <?php echo htmlspecialchars($user['email']); ?>
                                            </a>
                                        </td>
                                        <td class="text-muted small">
                                            <?php echo htmlspecialchars($user['address'] . ', ' . $user['city'] . ', ' . $user['state']); ?>
                                        </td>
                                        <td>
                                            <?php if ($hasRole): ?>
                                                <?php if (($user['role'] ?? '') === 'admin'): ?>
                                                    <span class="badge bg-danger">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">User</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4">
                                            <?php if (!$hasRole || ($user['role'] ?? '') !== 'admin'): ?>
                                                <a href="admin-users.php?delete=<?php echo $user['id']; ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this user?');">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">Protected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
