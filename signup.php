<?php
// Start session
session_start();
include 'Database/db.php';

// Simple registration processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $firstname = isset($_POST['firstname']) ? escapeInput($_POST['firstname']) : '';
    $lastname = isset($_POST['lastname']) ? escapeInput($_POST['lastname']) : '';
    $username = isset($_POST['username']) ? escapeInput($_POST['username']) : '';
    $email = isset($_POST['email']) ? escapeInput($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmpassword = isset($_POST['confirmpassword']) ? $_POST['confirmpassword'] : '';
    $address = isset($_POST['address']) ? escapeInput($_POST['address']) : '';
    $city = isset($_POST['city']) ? escapeInput($_POST['city']) : '';
    $state = isset($_POST['province']) ? escapeInput($_POST['province']) : '';
    $postal_code = isset($_POST['postal']) ? escapeInput($_POST['postal']) : '';

    // Basic validation
    $errors = [];

    if (empty($firstname)) {
        $errors[] = 'First name is required.';
    }

    if (empty($lastname)) {
        $errors[] = 'Last name is required.';
    }

    if (empty($username)) {
        $errors[] = 'Username is required.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirmpassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($address)) {
        $errors[] = 'Address is required.';
    }

    if (empty($city)) {
        $errors[] = 'City is required.';
    }

    if (empty($state)) {
        $errors[] = 'Province is required.';
    }

    if (empty($postal_code)) {
        $errors[] = 'Postal code is required.';
    }

    // If no errors, process registration
    if (empty($errors)) {
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $errors[] = 'Email already registered. Please login or use a different email.';
        } else {
            // Check if username column exists in database
            $columns_query = "SHOW COLUMNS FROM users LIKE 'username'";
            $columns_result = mysqli_query($conn, $columns_query);
            $username_column_exists = $columns_result && mysqli_num_rows($columns_result) > 0;

            // Check if username already exists (only if column exists)
            $username_error = false;
            if ($username_column_exists && !empty($username)) {
                $check_username_query = "SELECT id FROM users WHERE username = '$username'";
                $check_username_result = mysqli_query($conn, $check_username_query);

                if (mysqli_num_rows($check_username_result) > 0) {
                    $errors[] = 'Username already taken. Please choose a different username.';
                    $username_error = true;
                }
            }

            // Only proceed if no username errors
            if (!$username_error) {
                // Hash the password
                $hashedPassword = hashPassword($password);

                // Build insert query based on whether username column exists
                if ($username_column_exists) {
                    $insert_query = "INSERT INTO users (first_name, last_name, username, email, password, address, city, state, postal_code) 
                                    VALUES ('$firstname', '$lastname', '$username', '$email', '$hashedPassword', '$address', '$city', '$state', '$postal_code')";
                } else {
                    $insert_query = "INSERT INTO users (first_name, last_name, email, password, address, city, state, postal_code) 
                                    VALUES ('$firstname', '$lastname', '$email', '$hashedPassword', '$address', '$city', '$state', '$postal_code')";
                }

                if (mysqli_query($conn, $insert_query)) {
                    $user_id = mysqli_insert_id($conn);
                    $_SESSION['user'] = [
                        'id' => $user_id,
                        'first_name' => $firstname,
                        'last_name' => $lastname,
                        'email' => $email
                    ];

                    // Add username to session if it was saved
                    if ($username_column_exists && !empty($username)) {
                        $_SESSION['user']['username'] = $username;
                    }

                    $_SESSION['success_message'] = 'Registration successful! Here is your profile information.';
                    header('Location: user-profile.php');
                    exit;
                } else {
                    $errors[] = 'Registration failed. Please try again. Error: ' . mysqli_error($conn);
                }
            }
        }
    }

    // Store errors in session
    if (!empty($errors)) {
        $_SESSION['registration_errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        header('Location: register.php');
        exit;
    }
} else {
    // If not POST, redirect to register page
    header('Location: register.php');
    exit;
}
