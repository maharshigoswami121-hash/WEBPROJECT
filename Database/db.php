<?php
// Database Connection Configuration (XAMPP default)
define('DB_HOST', '127.0.0.1'); // Use TCP to avoid missing socket errors
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'webproject_db');
define('DB_PORT', 3306);

// Create connection with basic error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
    mysqli_set_charset($conn, "utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Database connection failed. Please ensure MySQL is running and credentials are correct. Error: " . $e->getMessage());
}

// Function to escape input for security
function escapeInput($input)
{
    global $conn;
    return mysqli_real_escape_string($conn, trim($input));
}

// Function to hash password
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

// Function to verify password
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}
