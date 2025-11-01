<?php
/**
 * Profile Update Handler
 * QuizCraft - Quiz Management System
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Include database configuration
require_once 'config/database.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userId = $_SESSION['user_id'];

    // Get and sanitize input data
    $fullName = isset($_POST['fullName']) ? trim($_POST['fullName']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $workSchool = isset($_POST['workSchool']) ? trim($_POST['workSchool']) : '';
    $newPassword = isset($_POST['newPassword']) ? $_POST['newPassword'] : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';

    // Optional fields
    $degree = isset($_POST['degree']) ? trim($_POST['degree']) : null;
    $birthdate = isset($_POST['birthdate']) ? $_POST['birthdate'] : null;
    $gender = isset($_POST['gender']) ? $_POST['gender'] : null;

    // Validation
    $errors = [];

    // Validate required fields
    if (empty($fullName)) {
        $errors[] = 'Full name is required.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email address is required.';
    }

    if (empty($username)) {
        $errors[] = 'Username is required.';
    }

    if (empty($workSchool)) {
        $errors[] = 'Work/School is required.';
    }

    // Validate password if changing
    if (!empty($newPassword)) {
        if (strlen($newPassword) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
    }

    // Handle profile image upload
    $profileImageName = null;
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['profileImage']['type'], $allowedTypes)) {
            $errors[] = 'Invalid image format. Only JPG, PNG, and GIF are allowed.';
        }

        if ($_FILES['profileImage']['size'] > $maxSize) {
            $errors[] = 'Image size must be less than 5MB.';
        }

        if (empty($errors)) {
            $extension = pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION);
            $profileImageName = 'profile_' . $userId . '_' . time() . '.' . $extension;
            $uploadPath = 'uploads/profile_images/' . $profileImageName;

            // Create directory if it doesn't exist
            if (!file_exists('uploads/profile_images')) {
                mkdir('uploads/profile_images', 0777, true);
            }

            if (!move_uploaded_file($_FILES['profileImage']['tmp_name'], $uploadPath)) {
                $errors[] = 'Failed to upload image.';
                $profileImageName = null;
            }
        }
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
        header('Location: profile.php');
        exit();
    }

    // Get database connection
    $conn = getDBConnection();

    // Check if email already exists (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error_message'] = 'Email address is already in use by another account.';
        $stmt->close();
        closeDBConnection($conn);
        header('Location: profile.php');
        exit();
    }
    $stmt->close();

    // Check if username already exists (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->bind_param("si", $username, $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error_message'] = 'Username is already taken by another account.';
        $stmt->close();
        closeDBConnection($conn);
        header('Location: profile.php');
        exit();
    }
    $stmt->close();

    // Prepare update query
    if (!empty($newPassword)) {
        // Update with new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        if ($profileImageName) {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, username = ?, work_school = ?, password = ?, profile_image = ?, degree = ?, birthdate = ?, gender = ? WHERE id = ?");
            $stmt->bind_param("sssssssssi", $fullName, $email, $username, $workSchool, $hashedPassword, $profileImageName, $degree, $birthdate, $gender, $userId);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, username = ?, work_school = ?, password = ?, degree = ?, birthdate = ?, gender = ? WHERE id = ?");
            $stmt->bind_param("ssssssssi", $fullName, $email, $username, $workSchool, $hashedPassword, $degree, $birthdate, $gender, $userId);
        }
    } else {
        // Update without password change
        if ($profileImageName) {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, username = ?, work_school = ?, profile_image = ?, degree = ?, birthdate = ?, gender = ? WHERE id = ?");
            $stmt->bind_param("ssssssssi", $fullName, $email, $username, $workSchool, $profileImageName, $degree, $birthdate, $gender, $userId);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, username = ?, work_school = ?, degree = ?, birthdate = ?, gender = ? WHERE id = ?");
            $stmt->bind_param("sssssssi", $fullName, $email, $username, $workSchool, $degree, $birthdate, $gender, $userId);
        }
    }

    if ($stmt->execute()) {
        // Update session variables
        $_SESSION['full_name'] = $fullName;
        $_SESSION['email'] = $email;
        $_SESSION['username'] = $username;

        $_SESSION['success_message'] = 'Done';
        $stmt->close();
        closeDBConnection($conn);

        // Redirect to dashboard based on role
        $dashboardUrl = ($_SESSION['role'] === 'Teacher') ? 'teacher_dashboard.php' : 'student_dashboard.php';
        header('Location: ' . $dashboardUrl);
        exit();
    } else {
        $_SESSION['error_message'] = 'Failed to update profile. Please try again.';
        $stmt->close();
        closeDBConnection($conn);
        header('Location: profile.php');
        exit();
    }

} else {
    // If not POST request, redirect to profile page
    header('Location: profile.php');
    exit();
}
?>
