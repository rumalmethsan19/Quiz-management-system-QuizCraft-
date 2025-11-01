<?php
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Teacher') {
    header('Location: login.php');
    exit();
}

// Get welcome message
$welcomeMessage = isset($_SESSION['welcome_message']) ? $_SESSION['welcome_message'] : '';
unset($_SESSION['welcome_message']);

// Get success message
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);

// Get user data
$fullName = $_SESSION['full_name'];
$email = $_SESSION['email'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - QuizCraft</title>
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

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .slide-in-right {
            animation: slideInRight 0.6s ease-out forwards;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Welcome/Success Message Modal -->
    <?php if (!empty($welcomeMessage) || !empty($successMessage)): ?>
    <div id="welcomeModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black bg-opacity-50">
        <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-md w-full transform scale-95 transition-all duration-300" id="modalContent">
            <div class="text-center">
                <?php if (!empty($successMessage)): ?>
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-4">
                        <svg class="h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-5xl font-bold text-green-600 mb-2"><?php echo $successMessage; ?>!</h3>
                    <p class="text-lg text-gray-600 mb-6">
                        Your profile has been updated successfully.
                    </p>
                <?php else: ?>
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 mb-4">
                        <svg class="h-12 w-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                        </svg>
                    </div>
                    <h3 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2"><?php echo $welcomeMessage; ?>!</h3>
                    <p class="text-lg text-gray-600 mb-6">
                        Ready to create amazing quizzes?
                    </p>
                <?php endif; ?>
                <button onclick="closeWelcomeModal()" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-lg px-8 py-3 rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    <?php echo !empty($successMessage) ? 'Continue' : 'Let\'s Start'; ?>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Top Navigation Bar -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                    </svg>
                    <span class="text-white text-2xl font-bold">QuizCraft</span>
                    <span class="bg-white bg-opacity-20 text-white text-sm px-3 py-1 rounded-full">Teacher</span>
                </div>

                <!-- User Info -->
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-white font-semibold"><?php echo htmlspecialchars($fullName); ?></p>
                        <p class="text-purple-200 text-sm"><?php echo htmlspecialchars($email); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-purple-600 font-bold text-xl">
                        <?php echo strtoupper(substr($fullName, 0, 1)); ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Page Header -->
        <div class="mb-10 fade-in-up">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Teacher Dashboard</h1>
            <p class="text-gray-600 text-lg">Manage your quizzes and track student performance</p>
        </div>

        <!-- Dashboard Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

            <!-- Quiz Management Card -->
            <a href="quiz_management.php" class="card-hover bg-white rounded-2xl shadow-lg p-6 fade-in-up delay-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                        </svg>
                    </div>
                    <span class="text-blue-600 font-bold text-2xl">0</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-1">Quiz Management</h3>
                <p class="text-gray-600 text-sm">Create, edit, and manage quizzes</p>
            </a>

            <!-- Student Control Card -->
            <a href="student_control.php" class="card-hover bg-white rounded-2xl shadow-lg p-6 fade-in-up delay-2">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                        </svg>
                    </div>
                    <span class="text-green-600 font-bold text-2xl">0</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-1">Student Control</h3>
                <p class="text-gray-600 text-sm">Monitor and manage students</p>
            </a>

            <!-- Profile Card -->
            <a href="profile.php" class="card-hover bg-white rounded-2xl shadow-lg p-6 fade-in-up delay-3">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-1">Profile</h3>
                <p class="text-gray-600 text-sm">View and edit your profile</p>
            </a>

            <!-- Logout Card -->
            <a href="logout.php" class="card-hover bg-white rounded-2xl shadow-lg p-6 fade-in-up delay-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-gradient-to-r from-red-500 to-red-600 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-1">Logout</h3>
                <p class="text-gray-600 text-sm">Sign out from your account</p>
            </a>

        </div>

        <!-- Quick Stats Section -->
        <div class="bg-white rounded-2xl shadow-lg p-8 fade-in-up delay-4">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Quick Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <p class="text-gray-600 mb-2">Total Quizzes</p>
                    <p class="text-4xl font-bold text-blue-600">0</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600 mb-2">Total Students</p>
                    <p class="text-4xl font-bold text-green-600">0</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600 mb-2">Active Quizzes</p>
                    <p class="text-4xl font-bold text-purple-600">0</p>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Close welcome modal
        function closeWelcomeModal() {
            const modal = document.getElementById('welcomeModal');
            if (modal) {
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.remove();
                }, 300);
            }
        }

        // Auto-scale modal on load
        window.addEventListener('load', function() {
            const modalContent = document.getElementById('modalContent');
            if (modalContent) {
                setTimeout(() => {
                    modalContent.style.transform = 'scale(1)';
                }, 100);
            }
        });
    </script>

</body>
</html>
