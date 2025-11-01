<?php
/**
 * Database Update Script - Add Profile Fields
 * QuizCraft - Quiz Management System
 */

// Database credentials
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'quizcraft_db';

// Connect to database
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Update - QuizCraft</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gradient-to-r from-purple-600 to-pink-600 min-h-screen flex items-center justify-center'>
    <div class='bg-white rounded-3xl shadow-2xl p-10 max-w-2xl w-full mx-4'>
        <h1 class='text-4xl font-bold text-purple-600 mb-6 text-center'>Database Update - Profile Fields</h1>
        <div class='space-y-4'>";

// Add profile_image column
$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT NULL";
if ($conn->query($sql) === TRUE) {
    echo "<p class='text-green-600 font-semibold'>✓ Column 'profile_image' added/verified</p>";
} else {
    echo "<p class='text-yellow-600 font-semibold'>⚠ profile_image: " . $conn->error . "</p>";
}

// Add degree column
$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS degree VARCHAR(255) DEFAULT NULL";
if ($conn->query($sql) === TRUE) {
    echo "<p class='text-green-600 font-semibold'>✓ Column 'degree' added/verified</p>";
} else {
    echo "<p class='text-yellow-600 font-semibold'>⚠ degree: " . $conn->error . "</p>";
}

// Add birthdate column
$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS birthdate DATE DEFAULT NULL";
if ($conn->query($sql) === TRUE) {
    echo "<p class='text-green-600 font-semibold'>✓ Column 'birthdate' added/verified</p>";
} else {
    echo "<p class='text-yellow-600 font-semibold'>⚠ birthdate: " . $conn->error . "</p>";
}

// Add gender column
$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS gender ENUM('Male', 'Female', 'Other') DEFAULT NULL";
if ($conn->query($sql) === TRUE) {
    echo "<p class='text-green-600 font-semibold'>✓ Column 'gender' added/verified</p>";
} else {
    echo "<p class='text-yellow-600 font-semibold'>⚠ gender: " . $conn->error . "</p>";
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
            <h2 class='text-2xl font-bold text-green-700 mb-2'>Update Complete!</h2>
            <p class='text-gray-700 mb-4'>Profile fields have been added to the database successfully.</p>
            <a href='index.php' class='inline-block bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold px-8 py-3 rounded-full hover:shadow-lg transition-all duration-300'>
                Go to Home Page
            </a>
        </div>
        <div class='mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-lg'>
            <p class='text-sm text-yellow-800'>
                <strong>Important:</strong> Delete this file (update_database_profile.php) after running it.
            </p>
        </div>
    </div>
</body>
</html>";

$conn->close();
?>
