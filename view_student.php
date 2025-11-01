<?php
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Teacher') {
    header('Location: login.php');
    exit();
}

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: student_control.php');
    exit();
}

// Include database configuration
require_once 'config/database.php';

// Get student data from database
$studentId = intval($_GET['id']);
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'Student'");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Student not found
    $stmt->close();
    closeDBConnection($conn);
    header('Location: student_control.php');
    exit();
}

$student = $result->fetch_assoc();
$stmt->close();
closeDBConnection($conn);

// Get teacher data
$teacherName = $_SESSION['full_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($student['full_name']); ?> - Student Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .delay-1 {
            animation-delay: 0.1s;
            opacity: 0;
        }

        .delay-2 {
            animation-delay: 0.2s;
            opacity: 0;
        }

        .delay-3 {
            animation-delay: 0.3s;
            opacity: 0;
        }

        .delay-4 {
            animation-delay: 0.4s;
            opacity: 0;
        }

        .profile-image {
            width: 180px;
            height: 180px;
            object-fit: cover;
        }

        .info-card {
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Top Navigation Bar -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="teacher_dashboard.php" class="flex items-center space-x-3 hover:opacity-80 transition">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                    </svg>
                    <span class="text-white text-2xl font-bold">QuizCraft</span>
                    <span class="bg-white bg-opacity-20 text-white text-sm px-3 py-1 rounded-full">Teacher</span>
                </a>

                <!-- Back to Student Control Button -->
                <a href="student_control.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold px-6 py-2 rounded-full transition">
                    ← Back to Student Control
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Page Header -->
        <div class="mb-10 fade-in-up">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Student Profile</h1>
            <p class="text-gray-600 text-lg">Viewing profile of <?php echo htmlspecialchars($student['full_name']); ?></p>
        </div>

        <!-- Profile Overview Card -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-10 fade-in-up delay-1">
            <div class="flex flex-col md:flex-row items-center gap-8">
                <!-- Profile Image -->
                <div class="flex-shrink-0">
                    <?php if (!empty($student['profile_image']) && file_exists('uploads/profile_images/' . $student['profile_image'])): ?>
                        <img src="uploads/profile_images/<?php echo htmlspecialchars($student['profile_image']); ?>" alt="Profile" class="profile-image rounded-full border-4 border-purple-500 shadow-lg">
                    <?php else: ?>
                        <div class="profile-image rounded-full border-4 border-gray-300 bg-gradient-to-r from-purple-600 to-pink-600 flex items-center justify-center text-white text-7xl font-bold shadow-lg">
                            <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Basic Info -->
                <div class="flex-1 text-center md:text-left">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($student['full_name']); ?></h2>
                    <p class="text-xl text-gray-600 mb-4"><?php echo htmlspecialchars($student['email']); ?></p>
                    <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                        <span class="bg-green-100 text-green-600 px-4 py-2 rounded-full text-sm font-semibold">Active Student</span>
                        <span class="bg-blue-100 text-blue-600 px-4 py-2 rounded-full text-sm font-semibold">@<?php echo htmlspecialchars($student['username']); ?></span>
                        <span class="bg-purple-100 text-purple-600 px-4 py-2 rounded-full text-sm font-semibold"><?php echo htmlspecialchars($student['work_school']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10 fade-in-up delay-2">
            <div class="bg-white rounded-2xl shadow-lg p-6 text-center info-card">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <p class="text-gray-600 text-sm mb-1">Quizzes Taken</p>
                <p class="text-3xl font-bold text-blue-600">0</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 text-center info-card">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <p class="text-gray-600 text-sm mb-1">Average Score</p>
                <p class="text-3xl font-bold text-green-600">0%</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 text-center info-card">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zm5 13.18l-1.46-1.46-.71.71 2.17 2.17L22 12.6l-.71-.71L16 17.18zM12 17L6 14v-4l6 3.27L18 10v4l-6 3z"/>
                    </svg>
                </div>
                <p class="text-gray-600 text-sm mb-1">Classes Joined</p>
                <p class="text-3xl font-bold text-purple-600">0</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 text-center info-card">
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>
                    </svg>
                </div>
                <p class="text-gray-600 text-sm mb-1">Performance</p>
                <p class="text-3xl font-bold text-orange-600">--</p>
            </div>
        </div>

        <!-- Detailed Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 fade-in-up delay-3">

            <!-- Personal Information Card -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <svg class="w-7 h-7 text-purple-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                    </svg>
                    Personal Information
                </h2>
                <div class="space-y-4">
                    <div class="border-b border-gray-200 pb-3">
                        <p class="text-gray-500 text-sm mb-1">Full Name</p>
                        <p class="text-gray-800 font-semibold text-lg"><?php echo htmlspecialchars($student['full_name']); ?></p>
                    </div>
                    <div class="border-b border-gray-200 pb-3">
                        <p class="text-gray-500 text-sm mb-1">Username</p>
                        <p class="text-gray-800 font-semibold text-lg">@<?php echo htmlspecialchars($student['username']); ?></p>
                    </div>
                    <div class="border-b border-gray-200 pb-3">
                        <p class="text-gray-500 text-sm mb-1">Email Address</p>
                        <p class="text-gray-800 font-semibold text-lg"><?php echo htmlspecialchars($student['email']); ?></p>
                    </div>
                    <div class="border-b border-gray-200 pb-3">
                        <p class="text-gray-500 text-sm mb-1">Work/School</p>
                        <p class="text-gray-800 font-semibold text-lg"><?php echo htmlspecialchars($student['work_school']); ?></p>
                    </div>
                    <?php if (!empty($student['degree'])): ?>
                    <div class="border-b border-gray-200 pb-3">
                        <p class="text-gray-500 text-sm mb-1">Degree/Qualification</p>
                        <p class="text-gray-800 font-semibold text-lg"><?php echo htmlspecialchars($student['degree']); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($student['birthdate'])): ?>
                    <div class="border-b border-gray-200 pb-3">
                        <p class="text-gray-500 text-sm mb-1">Birth Date</p>
                        <p class="text-gray-800 font-semibold text-lg"><?php echo date('F d, Y', strtotime($student['birthdate'])); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($student['gender'])): ?>
                    <div class="pb-3">
                        <p class="text-gray-500 text-sm mb-1">Gender</p>
                        <p class="text-gray-800 font-semibold text-lg"><?php echo htmlspecialchars($student['gender']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Account Activity Card -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <svg class="w-7 h-7 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                    </svg>
                    Account Activity
                </h2>
                <div class="space-y-4">
                    <div class="border-b border-gray-200 pb-3">
                        <p class="text-gray-500 text-sm mb-1">Account Created</p>
                        <p class="text-gray-800 font-semibold text-lg"><?php echo date('F d, Y \a\t g:i A', strtotime($student['created_at'])); ?></p>
                    </div>
                    <div class="border-b border-gray-200 pb-3">
                        <p class="text-gray-500 text-sm mb-1">Last Login</p>
                        <p class="text-gray-800 font-semibold text-lg">
                            <?php
                            if ($student['last_login']) {
                                echo date('F d, Y \a\t g:i A', strtotime($student['last_login']));
                            } else {
                                echo 'Never logged in';
                            }
                            ?>
                        </p>
                    </div>
                    <div class="border-b border-gray-200 pb-3">
                        <p class="text-gray-500 text-sm mb-1">Account Status</p>
                        <p class="text-gray-800 font-semibold text-lg">
                            <?php if ($student['is_active']): ?>
                                <span class="text-green-600">Active</span>
                            <?php else: ?>
                                <span class="text-red-600">Inactive</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="border-b border-gray-200 pb-3">
                        <p class="text-gray-500 text-sm mb-1">Student ID</p>
                        <p class="text-gray-800 font-semibold text-lg">#<?php echo str_pad($student['id'], 6, '0', STR_PAD_LEFT); ?></p>
                    </div>
                    <div class="pb-3">
                        <p class="text-gray-500 text-sm mb-1">Account Type</p>
                        <p class="text-gray-800 font-semibold text-lg"><?php echo htmlspecialchars($student['role']); ?></p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Recent Activity Section (Placeholder) -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mt-10 fade-in-up delay-4">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-7 h-7 text-green-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
                </svg>
                Recent Activity
            </h2>
            <div class="text-center py-12">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                </svg>
                <p class="text-xl text-gray-500">No quiz activity yet</p>
                <p class="text-sm text-gray-400 mt-2">This student hasn't taken any quizzes</p>
            </div>
        </div>

    </div>

</body>
</html>
