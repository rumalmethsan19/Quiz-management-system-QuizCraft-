<?php
/**
 * Login Process Handler
 * QuizCraft - Quiz Management System
 */

session_start();

// Include database configuration
require_once 'config/database.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get and sanitize input data
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validation
    $errors = [];

    // Validate Role
    if (empty($role) || !in_array($role, ['Student', 'Teacher'])) {
        $errors[] = 'Please select a valid role (Student or Teacher).';
    }

    // Validate Username
    if (empty($username)) {
        $errors[] = 'Username is required.';
    }

    // Validate Password
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
        $_SESSION['form_data'] = $_POST;
        header('Location: login.php');
        exit();
    }

    // Get database connection
    $conn = getDBConnection();

    // Check if user exists with the given username and role
    $stmt = $conn->prepare("SELECT id, username, password, role, full_name, email FROM users WHERE username = ? AND role = ? AND is_active = 1");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // User found, verify password
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Password is correct, login successful

            // Update last login timestamp
            $updateStmt = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            $updateStmt->close();

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            $_SESSION['welcome_message'] = 'Welcome ' . $user['full_name'];

            // Close connections
            $stmt->close();
            closeDBConnection($conn);

            // Redirect to dashboard based on role
            if ($user['role'] === 'Teacher') {
                header('Location: teacher_dashboard.php');
            } else {
                header('Location: student_dashboard.php');
            }
            exit();

        } else {
            // Password is incorrect
            $_SESSION['error_message'] = 'Username, Password, or Role is incorrect. Please try again.';
            $_SESSION['form_data'] = ['username' => $username, 'role' => $role];
            $stmt->close();
            closeDBConnection($conn);
            header('Location: login.php');
            exit();
        }

    } else {
        // User not found or inactive
        $_SESSION['error_message'] = 'Username, Password, or Role is incorrect. Please try again.';
        $_SESSION['form_data'] = ['username' => $username, 'role' => $role];
        $stmt->close();
        closeDBConnection($conn);
        header('Location: login.php');
        exit();
    }

} else {
    // If not POST request, redirect to login page
    header('Location: login.php');
    exit();
}
?>
