<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Student') {
    header('Location: login.php');
    exit();
}

// Include database configuration
require_once 'config/database.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classId = isset($_POST['classId']) ? trim($_POST['classId']) : '';

    if (empty($classId)) {
        $_SESSION['error_message'] = 'Please enter a Class ID';
        header('Location: student_dashboard.php');
        exit();
    }

    // Search for the class
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT q.*, u.full_name as teacher_name FROM quizzes q
                           JOIN users u ON q.teacher_id = u.id
                           WHERE q.class_id = ? AND q.is_active = 1");
    $stmt->bind_param("s", $classId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error_message'] = 'Class ID not found or class is inactive';
        $stmt->close();
        closeDBConnection($conn);
        header('Location: student_dashboard.php');
        exit();
    }

    $quiz = $result->fetch_assoc();
    $stmt->close();

    // Check if student has already taken this quiz
    $studentId = $_SESSION['user_id'];

    // Enroll student in class if not already enrolled
    $stmt = $conn->prepare("INSERT IGNORE INTO class_enrollments (student_id, quiz_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $studentId, $quiz['id']);
    $stmt->execute();
    $stmt->close();

    // Check if already taken
    $stmt = $conn->prepare("SELECT * FROM quiz_results WHERE quiz_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $quiz['id'], $studentId);
    $stmt->execute();
    $resultCheck = $stmt->get_result();
    $alreadyTaken = $resultCheck->num_rows > 0;
    $stmt->close();
    closeDBConnection($conn);

} else {
    header('Location: student_dashboard.php');
    exit();
}

// Get user data
$fullName = $_SESSION['full_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Found - QuizCraft</title>
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Top Navigation Bar -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="student_dashboard.php" class="flex items-center space-x-3 hover:opacity-80 transition">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                    </svg>
                    <span class="text-white text-2xl font-bold">QuizCraft</span>
                    <span class="bg-white bg-opacity-20 text-white text-sm px-3 py-1 rounded-full">Student</span>
                </a>

                <!-- Back to Dashboard Button -->
                <a href="student_dashboard.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold px-6 py-2 rounded-full transition">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Class Found Card -->
        <div class="bg-white rounded-3xl shadow-2xl p-10 fade-in-up">
            <div class="text-center mb-8">
                <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-gradient-to-r from-green-500 to-green-600 mb-6">
                    <svg class="h-14 w-14 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-gray-800 mb-2">Class Found!</h1>
                <p class="text-gray-600 text-lg">You can join this quiz class</p>
            </div>

            <!-- Quiz Details -->
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl p-8 mb-8 fade-in-up delay-1">
                <h2 class="text-3xl font-bold text-purple-700 mb-6"><?php echo htmlspecialchars($quiz['title']); ?></h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-xl p-4 shadow">
                        <p class="text-gray-600 text-sm mb-1">Teacher</p>
                        <p class="text-gray-800 font-bold text-lg"><?php echo htmlspecialchars($quiz['teacher_name']); ?></p>
                    </div>

                    <div class="bg-white rounded-xl p-4 shadow">
                        <p class="text-gray-600 text-sm mb-1">Class ID</p>
                        <p class="text-purple-600 font-bold text-lg"><?php echo htmlspecialchars($quiz['class_id']); ?></p>
                    </div>

                    <div class="bg-white rounded-xl p-4 shadow">
                        <p class="text-gray-600 text-sm mb-1">Total Questions</p>
                        <p class="text-gray-800 font-bold text-lg"><?php echo $quiz['total_questions']; ?> Questions</p>
                    </div>

                    <div class="bg-white rounded-xl p-4 shadow">
                        <p class="text-gray-600 text-sm mb-1">Duration</p>
                        <p class="text-gray-800 font-bold text-lg"><?php echo $quiz['duration']; ?> Minutes</p>
                    </div>
                </div>

                <?php if (!empty($quiz['description'])): ?>
                <div class="bg-white rounded-xl p-4 shadow">
                    <p class="text-gray-600 text-sm mb-1">Description</p>
                    <p class="text-gray-800"><?php echo htmlspecialchars($quiz['description']); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center fade-in-up delay-2">
                <?php if ($alreadyTaken): ?>
                    <div class="text-center">
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg mb-4">
                            <p class="font-bold">Already Taken</p>
                            <p class="text-sm">You have already completed this quiz</p>
                        </div>
                        <a href="view_result.php?quiz_id=<?php echo $quiz['id']; ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg px-8 py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition">
                            View Your Results
                        </a>
                    </div>
                <?php else: ?>
                    <a href="take_quiz.php?quiz_id=<?php echo $quiz['id']; ?>" class="bg-gradient-to-r from-green-600 to-green-700 text-white font-bold text-lg px-10 py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition text-center">
                        Start Quiz
                    </a>
                <?php endif; ?>

                <a href="student_dashboard.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold text-lg px-10 py-4 rounded-xl transition text-center">
                    Cancel
                </a>
            </div>
        </div>

    </div>

</body>
</html>
