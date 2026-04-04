<?php
session_start();
include 'Database/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin-login.php');
    exit;
}

$email = isset($_POST['email']) ? escapeInput($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Basic validation
if (empty($email) || empty($password)) {
    $_SESSION['admin_login_error'] = 'Please enter both email and password.';
    header('Location: admin-login.php');
    exit;
}

try {
    // Check if the role column exists; fall back to email-only lookup if it does not
    $roleColumnExists = false;
    if ($roleResult = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'")) {
        $roleColumnExists = mysqli_num_rows($roleResult) > 0;
        mysqli_free_result($roleResult);
    }

    $query = "SELECT id, first_name, last_name, email, password" . ($roleColumnExists ? ", role" : "") .
        " FROM users WHERE email = '$email'" . ($roleColumnExists ? " AND role = 'admin'" : "");
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        $isPasswordValid = verifyPassword($password, $admin['password']) || $password === $admin['password'];

        if ($isPasswordValid && (!$roleColumnExists || ($admin['role'] ?? '') === 'admin')) {
            $_SESSION['admin'] = true;
            $_SESSION['admin_user'] = [
                'id' => $admin['id'],
                'email' => $admin['email'],
                'first_name' => $admin['first_name'],
                'last_name' => $admin['last_name']
            ];
            header('Location: admin-dashboard.php');
            exit;
        }
    }

    $_SESSION['admin_login_error'] = 'Invalid email or password.';
} catch (mysqli_sql_exception $e) {
    $_SESSION['admin_login_error'] = 'Login error. Please check that the users table has a role column and try again.';
}

header('Location: admin-login.php');
exit;
