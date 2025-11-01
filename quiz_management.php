<?php
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Teacher') {
    header('Location: login.php');
    exit();
}

// Include database configuration
require_once 'config/database.php';

// Get teacher ID
$teacherId = $_SESSION['user_id'];

// Get all quiz classes created by this teacher
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE teacher_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();
$quizzes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
closeDBConnection($conn);

// Get user data
$fullName = $_SESSION['full_name'];
$email = $_SESSION['email'];

// Get class created message
$classCreated = isset($_SESSION['class_created']) ? $_SESSION['class_created'] : false;
$newClassId = isset($_SESSION['new_class_id']) ? $_SESSION['new_class_id'] : '';
$className = isset($_SESSION['class_name']) ? $_SESSION['class_name'] : '';
unset($_SESSION['class_created']);
unset($_SESSION['new_class_id']);
unset($_SESSION['class_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Management - QuizCraft</title>
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

        .quiz-card {
            transition: all 0.3s ease;
        }

        .quiz-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .create-card {
            transition: all 0.3s ease;
            border: 3px dashed #d1d5db;
        }

        .create-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.2);
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Success Modal for Class Created -->
    <?php if ($classCreated): ?>
    <div id="successModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black bg-opacity-50">
        <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-md w-full transform scale-95 transition-all duration-300" id="modalContent">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-4">
                    <svg class="h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-4xl font-bold text-green-600 mb-2">Class Created!</h3>
                <p class="text-lg text-gray-600 mb-4">
                    Your quiz class "<?php echo htmlspecialchars($className); ?>" has been created successfully.
                </p>
                <div class="bg-purple-50 border-2 border-purple-300 rounded-xl p-6 mb-6">
                    <p class="text-sm text-gray-600 mb-2">Share this Class ID with your students:</p>
                    <p class="text-5xl font-bold text-purple-600 tracking-wider"><?php echo $newClassId; ?></p>
                    <button onclick="copyClassId('<?php echo $newClassId; ?>')" class="mt-4 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                        Copy Class ID
                    </button>
                </div>
                <button onclick="closeSuccessModal()" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-lg px-8 py-3 rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    Continue
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
                <a href="teacher_dashboard.php" class="flex items-center space-x-3 hover:opacity-80 transition">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                    </svg>
                    <span class="text-white text-2xl font-bold">QuizCraft</span>
                    <span class="bg-white bg-opacity-20 text-white text-sm px-3 py-1 rounded-full">Teacher</span>
                </a>

                <!-- Back to Dashboard Button -->
                <a href="teacher_dashboard.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold px-6 py-2 rounded-full transition">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Page Header -->
        <div class="mb-10 fade-in-up">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Quiz Management</h1>
            <p class="text-gray-600 text-lg">Create new quiz classes and manage existing ones</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 fade-in-up delay-1">
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Quiz Classes</p>
                        <p class="text-4xl font-bold text-blue-600"><?php echo count($quizzes); ?></p>
                    </div>
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Active Classes</p>
                        <p class="text-4xl font-bold text-green-600"><?php echo count(array_filter($quizzes, function($q) { return $q['is_active']; })); ?></p>
                    </div>
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">This Month</p>
                        <p class="text-4xl font-bold text-purple-600">+<?php echo count(array_filter($quizzes, function($q) { return strtotime($q['created_at']) > strtotime('-30 days'); })); ?></p>
                    </div>
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create New Quiz Class Section -->
        <div class="mb-10 fade-in-up delay-2">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-7 h-7 text-green-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
                Create New Quiz Class
            </h2>
            <a href="create_quiz.php" class="block create-card bg-white rounded-2xl p-8 cursor-pointer">
                <div class="text-center">
                    <div class="w-20 h-20 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Create New Quiz Class</h3>
                    <p class="text-gray-600">Click here to create a new quiz class with questions</p>
                </div>
            </a>
        </div>

        <!-- Previous Quiz Classes Section -->
        <div class="fade-in-up delay-3">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-7 h-7 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
                </svg>
                Previous Quiz Classes
            </h2>

            <?php if (empty($quizzes)): ?>
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                    </svg>
                    <p class="text-xl text-gray-500 mb-2">No quiz classes created yet</p>
                    <p class="text-gray-400">Create your first quiz class to get started</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($quizzes as $quiz): ?>
                        <a href="view_quiz.php?id=<?php echo $quiz['id']; ?>" class="quiz-card bg-white rounded-2xl shadow-lg p-6 cursor-pointer">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                    <p class="text-sm text-gray-600 line-clamp-2"><?php echo htmlspecialchars($quiz['description']); ?></p>
                                </div>
                                <?php if ($quiz['is_active']): ?>
                                    <span class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-xs font-semibold">Active</span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-semibold">Inactive</span>
                                <?php endif; ?>
                            </div>

                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-gray-500">Class ID:</span>
                                    <span class="font-semibold text-purple-600"><?php echo htmlspecialchars($quiz['class_id']); ?></span>
                                </div>
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-gray-500">Questions:</span>
                                    <span class="font-semibold text-gray-800"><?php echo $quiz['total_questions']; ?></span>
                                </div>
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-gray-500">Duration:</span>
                                    <span class="font-semibold text-gray-800"><?php echo $quiz['duration']; ?> mins</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Created:</span>
                                    <span class="font-semibold text-gray-800"><?php echo date('M d, Y', strtotime($quiz['created_at'])); ?></span>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-200 flex gap-2">
                                <button onclick="event.preventDefault(); window.location.href='edit_quiz.php?id=<?php echo $quiz['id']; ?>'" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg transition">
                                    Edit
                                </button>
                                <button onclick="event.preventDefault(); window.location.href='view_quiz.php?id=<?php echo $quiz['id']; ?>'" class="flex-1 bg-purple-500 hover:bg-purple-600 text-white font-semibold px-4 py-2 rounded-lg transition">
                                    View
                                </button>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        // Close success modal
        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            if (modal) {
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.remove();
                }, 300);
            }
        }

        // Copy class ID to clipboard
        function copyClassId(classId) {
            navigator.clipboard.writeText(classId).then(function() {
                alert('Class ID ' + classId + ' copied to clipboard!');
            }, function(err) {
                // Fallback for older browsers
                const input = document.createElement('input');
                input.value = classId;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                alert('Class ID ' + classId + ' copied to clipboard!');
            });
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
