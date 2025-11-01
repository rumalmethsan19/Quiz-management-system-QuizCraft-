<?php
/**
 * Registration Process Handler
 * QuizCraft - Quiz Management System
 */

session_start();

// Include database configuration
require_once 'config/database.php';

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get and sanitize input data
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';
    $fullName = isset($_POST['fullName']) ? trim($_POST['fullName']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $workSchool = isset($_POST['workSchool']) ? trim($_POST['workSchool']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';

    // Validation
    $errors = [];

    // Validate Role
    if (empty($role) || !in_array($role, ['Student', 'Teacher'])) {
        $errors[] = 'Please select a valid role (Student or Teacher).';
    }

    // Validate Full Name
    if (empty($fullName)) {
        $errors[] = 'Full name is required.';
    } elseif (strlen($fullName) < 3) {
        $errors[] = 'Full name must be at least 3 characters long.';
    }

    // Validate Email
    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Validate Work/School
    if (empty($workSchool)) {
        $errors[] = 'Work/School information is required.';
    }

    // Validate Username
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 4) {
        $errors[] = 'Username must be at least 4 characters long.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }

    // Validate Password
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    // Validate Confirm Password
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
        $_SESSION['form_data'] = $_POST;
        header('Location: register.php');
        exit();
    }

    // Get database connection
    $conn = getDBConnection();

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error_message'] = 'Email address is already registered. Please use a different email or login.';
        $_SESSION['form_data'] = $_POST;
        $stmt->close();
        closeDBConnection($conn);
        header('Location: register.php');
        exit();
    }
    $stmt->close();

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error_message'] = 'Username is already taken. Please choose a different username.';
        $_SESSION['form_data'] = $_POST;
        $stmt->close();
        closeDBConnection($conn);
        header('Location: register.php');
        exit();
    }
    $stmt->close();

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO users (role, full_name, email, work_school, username, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $role, $fullName, $email, $workSchool, $username, $hashedPassword);

    if ($stmt->execute()) {
        // Registration successful
        $_SESSION['success_message'] = 'Account Created Successfully!';
        $_SESSION['registered_username'] = $username;
        $stmt->close();
        closeDBConnection($conn);
        header('Location: index.php');
        exit();
    } else {
        // Registration failed
        $_SESSION['error_message'] = 'Registration failed. Please try again later.';
        $_SESSION['form_data'] = $_POST;
        $stmt->close();
        closeDBConnection($conn);
        header('Location: register.php');
        exit();
    }

} else {
    // If not POST request, redirect to register page
    header('Location: register.php');
    exit();
}
?>
