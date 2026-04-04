<?php
session_start();
include 'Database/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = isset($_POST['email']) ? escapeInput($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Basic validation
if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'Please enter both email and password.';
    header('Location: login.php');
    exit;
}

try {
    // See if a role column exists so we can identify admins
    $roleColumnExists = false;
    if ($roleResult = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'")) {
        $roleColumnExists = mysqli_num_rows($roleResult) > 0;
        mysqli_free_result($roleResult);
    }

    $query = "SELECT id, first_name, last_name, email, password" . ($roleColumnExists ? ", role" : "") .
        " FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $role = $roleColumnExists ? ($user['role'] ?? '') : '';

        // Accept hashed passwords, but also fall back to plain text if legacy data exists
        $passwordValid = verifyPassword($password, $user['password']) || $password === $user['password'];

        if ($passwordValid) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => $role
            ];

            // Promote admin session if applicable
            if ($roleColumnExists && $role === 'admin') {
                $_SESSION['admin'] = true;
                $_SESSION['admin_user'] = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name']
                ];
                header('Location: admin-dashboard.php');
                exit;
            }

            $_SESSION['success_message'] = 'Login successful!';
            header('Location: index.php');
            exit;
        }
    }

    $_SESSION['login_error'] = 'Invalid email or password.';
} catch (mysqli_sql_exception $e) {
    $_SESSION['login_error'] = 'Login error. Please try again.';
}

header('Location: login.php');
exit;
