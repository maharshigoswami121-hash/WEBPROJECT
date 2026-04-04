<?php
session_start();
include 'Database/db.php';
$title = "Admin";
include 'includes/header.php';
?>

<div class="min-vh-100 py-5">
    <div class="container px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold" style="color: #333;">Admin Panel - Registered Users</h1>
            <div class="text-muted">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    class="d-inline-block me-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span id="user-count">Loading...</span>
            </div>
        </div>

        <?php
        // Fetch all users from database
        // Try to get all columns, handle missing columns gracefully
        $query = "SELECT id, first_name, last_name, email, address, city, state, postal_code 
                  FROM users 
                  ORDER BY id DESC";

        // Try to include username and created_at if they exist
        $test_query = "SHOW COLUMNS FROM users";
        $columns_result = mysqli_query($conn, $test_query);
        $available_columns = [];
        if ($columns_result) {
            while ($col = mysqli_fetch_assoc($columns_result)) {
                $available_columns[] = $col['Field'];
            }
        }

        // Build query with available columns
        $select_fields = "id, first_name, last_name, email, address, city, state, postal_code";
        if (in_array('username', $available_columns)) {
            $select_fields .= ", username";
        }
        if (in_array('created_at', $available_columns)) {
            $select_fields .= ", created_at";
        }

        $order_by = in_array('created_at', $available_columns) ? "ORDER BY created_at DESC" : "ORDER BY id DESC";

        $query = "SELECT $select_fields FROM users $order_by";
        $result = mysqli_query($conn, $query);

        if (!$result) {
            echo '<div class="alert alert-danger">Error fetching users: ' . mysqli_error($conn) . '</div>';
        } else {
            $users = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row;
            }
            $user_count = count($users);
            ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <?php if (empty($users)): ?>
                        <div class="p-5 text-center">
                            <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                class="text-muted mb-3">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <h5 class="text-muted">No users registered yet</h5>
                            <p class="text-muted mb-0">Users will appear here once they register.</p>
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
                                        <th scope="col">City</th>
                                        <th scope="col">Province</th>
                                        <th scope="col">Postal Code</th>
                                        <th scope="col" class="pe-4">Registered</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user):
                                        $full_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
                                        $username = isset($user['username']) && !empty($user['username']) ? htmlspecialchars($user['username']) : 'N/A';
                                        $email = htmlspecialchars($user['email']);
                                        $address = htmlspecialchars($user['address'] ?? '');
                                        $city = htmlspecialchars($user['city'] ?? '');
                                        $state = htmlspecialchars($user['state'] ?? '');
                                        $postal_code = htmlspecialchars($user['postal_code'] ?? '');
                                        $created_at = isset($user['created_at']) && !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A';
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
                                                <a href="mailto:<?php echo $email; ?>" class="text-decoration-none"
                                                    style="color: #667eea;">
                                                    <?php echo $email; ?>
                                                </a>
                                            </td>
                                            <td class="text-muted"><?php echo $address; ?></td>
                                            <td><?php echo $city; ?></td>
                                            <td>
                                                <span class="badge bg-info bg-opacity-10 text-info"><?php echo $state; ?></span>
                                            </td>
                                            <td class="text-muted"><?php echo $postal_code; ?></td>
                                            <td class="pe-4 text-muted small"><?php echo $created_at; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($users)): ?>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing <strong><?php echo $user_count; ?></strong> registered
                        user<?php echo $user_count !== 1 ? 's' : ''; ?>
                    </div>
                    <a href="index.php" class="btn btn-outline-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            class="d-inline-block me-1">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Home
                    </a>
                </div>
            <?php endif; ?>

        <?php } ?>
    </div>
</div>

<script>
    // Update user count
    document.addEventListener('DOMContentLoaded', function () {
        const userCount=<?php echo isset($user_count) ? $user_count : 0; ?>;
        const countElement=document.getElementById('user-count');
        if (countElement)
        {
            countElement.textContent=userCount+' User'+(userCount!==1? 's':'');
        }
    });
</script>

<?php include 'includes/footer.php'; ?>