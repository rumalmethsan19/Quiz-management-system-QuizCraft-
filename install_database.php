<?php
/**
 * Database Installation Script
 * QuizCraft - Quiz Management System
 *
 * This script will create the database and all required tables
 * Run this file ONCE to set up the database
 */

// Database credentials
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'quizcraft_db';

// Connect to MySQL server (without selecting database)
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Installation - QuizCraft</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gradient-to-r from-purple-600 to-pink-600 min-h-screen flex items-center justify-center'>
    <div class='bg-white rounded-3xl shadow-2xl p-10 max-w-2xl w-full mx-4'>
        <h1 class='text-4xl font-bold text-purple-600 mb-6 text-center'>QuizCraft Database Installation</h1>
        <div class='space-y-4'>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "<p class='text-green-600 font-semibold'>✓ Database '$dbname' created successfully</p>";
} else {
    echo "<p class='text-red-600 font-semibold'>✗ Error creating database: " . $conn->error . "</p>";
}

// Select the database
$conn->select_db($dbname);

// Create Users Table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('Student', 'Teacher') NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    work_school VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    degree VARCHAR(255) DEFAULT NULL,
    birthdate DATE DEFAULT NULL,
    gender ENUM('Male', 'Female', 'Other') DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p class='text-green-600 font-semibold'>✓ Table 'users' created successfully</p>";
} else {
    echo "<p class='text-red-600 font-semibold'>✗ Error creating users table: " . $conn->error . "</p>";
}

// Create Quizzes Table
$sql = "CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    class_id VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    total_questions INT NOT NULL,
    duration INT DEFAULT 30,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_teacher (teacher_id),
    INDEX idx_class_id (class_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p class='text-green-600 font-semibold'>✓ Table 'quizzes' created successfully</p>";
} else {
    echo "<p class='text-red-600 font-semibold'>✗ Error creating quizzes table: " . $conn->error . "</p>";
}

// Create Questions Table
$sql = "CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_number INT NOT NULL,
    question_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_quiz (quiz_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p class='text-green-600 font-semibold'>✓ Table 'questions' created successfully</p>";
} else {
    echo "<p class='text-red-600 font-semibold'>✗ Error creating questions table: " . $conn->error . "</p>";
}

// Create Answers Table
$sql = "CREATE TABLE IF NOT EXISTS answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    answer_text TEXT NOT NULL,
    is_correct TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p class='text-green-600 font-semibold'>✓ Table 'answers' created successfully</p>";
} else {
    echo "<p class='text-red-600 font-semibold'>✗ Error creating answers table: " . $conn->error . "</p>";
}

// Create Quiz Results Table
$sql = "CREATE TABLE IF NOT EXISTS quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    student_id INT NOT NULL,
    score INT NOT NULL,
    total_marks INT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    status ENUM('Pass', 'Fail') NOT NULL,
    time_taken_minutes INT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_quiz (quiz_id),
    INDEX idx_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p class='text-green-600 font-semibold'>✓ Table 'quiz_results' created successfully</p>";
} else {
    echo "<p class='text-red-600 font-semibold'>✗ Error creating quiz_results table: " . $conn->error . "</p>";
}

// Create Student Answers Table
$sql = "CREATE TABLE IF NOT EXISTS student_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    result_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_id INT NOT NULL,
    is_correct TINYINT(1) NOT NULL,
    FOREIGN KEY (result_id) REFERENCES quiz_results(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (answer_id) REFERENCES answers(id) ON DELETE CASCADE,
    INDEX idx_result (result_id),
    INDEX idx_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p class='text-green-600 font-semibold'>✓ Table 'student_answers' created successfully</p>";
} else {
    echo "<p class='text-red-600 font-semibold'>✗ Error creating student_answers table: " . $conn->error . "</p>";
}

// Create Class Enrollments Table
$sql = "CREATE TABLE IF NOT EXISTS class_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    quiz_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, quiz_id),
    INDEX idx_student (student_id),
    INDEX idx_quiz (quiz_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "<p class='text-green-600 font-semibold'>✓ Table 'class_enrollments' created successfully</p>";
} else {
    echo "<p class='text-red-600 font-semibold'>✗ Error creating class_enrollments table: " . $conn->error . "</p>";
}

// Create uploads directory for profile images
$uploadsDir = __DIR__ . '/uploads/profile_images';
if (!file_exists($uploadsDir)) {
    if (mkdir($uploadsDir, 0777, true)) {
        echo "<p class='text-green-600 font-semibold'>✓ Created directory: uploads/profile_images</p>";
    } else {
        echo "<p class='text-red-600 font-semibold'>✗ Failed to create uploads directory</p>";
    }
} else {
    echo "<p class='text-green-600 font-semibold'>✓ Directory already exists: uploads/profile_images</p>";
}

echo "
        </div>
        <div class='mt-8 p-6 bg-green-50 border-l-4 border-green-500 rounded-lg'>
            <h2 class='text-2xl font-bold text-green-700 mb-2'>Installation Complete!</h2>
            <p class='text-gray-700 mb-4'>Your QuizCraft database has been set up successfully.</p>
            <a href='index.php' class='inline-block bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold px-8 py-3 rounded-full hover:shadow-lg transition-all duration-300'>
                Go to Home Page
            </a>
        </div>
        <div class='mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-lg'>
            <p class='text-sm text-yellow-800'>
                <strong>Important:</strong> For security reasons, delete or rename this file (install_database.php) after installation.
            </p>
        </div>
    </div>
</body>
</html>";

$conn->close();
?>
