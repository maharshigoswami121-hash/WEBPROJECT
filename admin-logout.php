<?php
session_start();
// Clear all admin-related sessions
unset($_SESSION['admin']);
unset($_SESSION['admin_user']);
// Also clear user session if it exists (admin login may have set it)
// This is important because login-process.php sets $_SESSION['user'] even for admins
unset($_SESSION['user']);
// Clear all remaining session variables to ensure complete logout
session_unset();
header('Location: index.php');
exit;
?>