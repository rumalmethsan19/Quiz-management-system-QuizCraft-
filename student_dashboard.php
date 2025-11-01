<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Student') {
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
    <title>Student Dashboard - QuizCraft</title>
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

        .search-input {
            transition: all 0.3s ease;
        }

        .search-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
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
                            <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/>
                        </svg>
                    </div>
                    <h3 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2"><?php echo $welcomeMessage; ?>!</h3>
                    <p class="text-lg text-gray-600 mb-6">
                        Ready to start learning?
                    </p>
                <?php endif; ?>
                <button onclick="closeWelcomeModal()" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-lg px-8 py-3 rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    <?php echo !empty($successMessage) ? 'Continue' : 'Let\'s Begin'; ?>
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
                    <span class="bg-white bg-opacity-20 text-white text-sm px-3 py-1 rounded-full">Student</span>
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
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Student Dashboard</h1>
            <p class="text-gray-600 text-lg">Join classes and take quizzes to test your knowledge</p>
        </div>

        <!-- Search Class Section -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-10 fade-in-up delay-1">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-8 h-8 text-purple-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
                Search Class
            </h2>
            <form id="searchClassForm" method="POST" action="search_class.php" class="space-y-4">
                <div>
                    <label for="classId" class="block text-gray-700 text-lg font-semibold mb-2">
                        Enter Class ID
                    </label>
                    <div class="flex gap-4">
                        <input
                            type="text"
                            id="classId"
                            name="classId"
                            required
                            placeholder="Enter class ID (e.g., CLASS-12345)"
                            class="search-input flex-1 px-6 py-4 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 text-lg"
                        >
                        <button
                            type="submit"
                            class="bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-lg px-8 py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300"
                        >
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-gray-500 text-sm mt-2">Ask your teacher for the class ID to join a class</p>
                </div>
            </form>
        </div>

        <!-- Dashboard Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">

            <!-- Profile Card -->
            <a href="profile.php" class="card-hover bg-white rounded-2xl shadow-lg p-6 fade-in-up delay-2">
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

            <!-- My Classes Card -->
            <a href="my_classes.php" class="card-hover bg-white rounded-2xl shadow-lg p-6 fade-in-up delay-3">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zm5 13.18l-1.46-1.46-.71.71 2.17 2.17L22 12.6l-.71-.71L16 17.18zM12 17L6 14v-4l6 3.27L18 10v4l-6 3z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-1">My Classes</h3>
                <p class="text-gray-600 text-sm">View your enrolled classes</p>
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
            <h2 class="text-2xl font-bold text-gray-800 mb-6">My Learning Progress</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <p class="text-gray-600 mb-2">Classes Joined</p>
                    <p class="text-4xl font-bold text-blue-600">0</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600 mb-2">Quizzes Taken</p>
                    <p class="text-4xl font-bold text-green-600">0</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600 mb-2">Average Score</p>
                    <p class="text-4xl font-bold text-purple-600">0%</p>
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
