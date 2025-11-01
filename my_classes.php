<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Student') {
    header('Location: login.php');
    exit();
}

// Include database configuration
require_once 'config/database.php';

$studentId = $_SESSION['user_id'];

// Get all enrolled classes with quiz details and results
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT
        q.*,
        u.full_name as teacher_name,
        ce.enrolled_at,
        qr.id as result_id,
        qr.score,
        qr.total_marks,
        qr.percentage,
        qr.status,
        qr.submitted_at,
        (SELECT COUNT(*) FROM quiz_results WHERE quiz_id = q.id AND student_id = ?) as attempt_count
    FROM class_enrollments ce
    JOIN quizzes q ON ce.quiz_id = q.id
    JOIN users u ON q.teacher_id = u.id
    LEFT JOIN quiz_results qr ON q.id = qr.quiz_id AND qr.student_id = ?
    WHERE ce.student_id = ?
    ORDER BY ce.enrolled_at DESC
");
$stmt->bind_param("iii", $studentId, $studentId, $studentId);
$stmt->execute();
$result = $stmt->get_result();
$classes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
closeDBConnection($conn);

// Get user data
$fullName = $_SESSION['full_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Classes - QuizCraft</title>
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

        .class-card {
            transition: all 0.3s ease;
        }

        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Page Header -->
        <div class="mb-10 fade-in-up">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">My Classes</h1>
            <p class="text-gray-600 text-lg">View all your enrolled quiz classes and results</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 fade-in-up delay-1">
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Classes</p>
                        <p class="text-4xl font-bold text-blue-600"><?php echo count($classes); ?></p>
                    </div>
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zm5 13.18l-1.46-1.46-.71.71 2.17 2.17L22 12.6l-.71-.71L16 17.18zM12 17L6 14v-4l6 3.27L18 10v4l-6 3z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Completed</p>
                        <p class="text-4xl font-bold text-green-600">
                            <?php echo count(array_filter($classes, function($c) { return !empty($c['result_id']); })); ?>
                        </p>
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
                        <p class="text-gray-600 text-sm">Average Score</p>
                        <p class="text-4xl font-bold text-purple-600">
                            <?php
                            $completedClasses = array_filter($classes, function($c) { return !empty($c['result_id']); });
                            if (count($completedClasses) > 0) {
                                $avgPercentage = array_sum(array_column($completedClasses, 'percentage')) / count($completedClasses);
                                echo number_format($avgPercentage, 1) . '%';
                            } else {
                                echo '--';
                            }
                            ?>
                        </p>
                    </div>
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classes List -->
        <div class="fade-in-up delay-2">
            <?php if (empty($classes)): ?>
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zm5 13.18l-1.46-1.46-.71.71 2.17 2.17L22 12.6l-.71-.71L16 17.18zM12 17L6 14v-4l6 3.27L18 10v4l-6 3z"/>
                    </svg>
                    <p class="text-xl text-gray-500 mb-2">No classes enrolled yet</p>
                    <p class="text-gray-400 mb-6">Search for a class ID to get started</p>
                    <a href="student_dashboard.php" class="inline-block bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold px-8 py-3 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition">
                        Search for Classes
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($classes as $class): ?>
                        <div class="class-card bg-white rounded-2xl shadow-lg p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-2xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($class['title']); ?></h3>
                                    <p class="text-gray-600 text-sm mb-2">
                                        <span class="font-semibold">Teacher:</span> <?php echo htmlspecialchars($class['teacher_name']); ?>
                                    </p>
                                    <?php if (!empty($class['description'])): ?>
                                        <p class="text-gray-500 text-sm line-clamp-2"><?php echo htmlspecialchars($class['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($class['result_id'])): ?>
                                    <?php if ($class['status'] === 'Pass'): ?>
                                        <span class="bg-green-100 text-green-600 px-4 py-2 rounded-full text-sm font-bold">Passed</span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-600 px-4 py-2 rounded-full text-sm font-bold">Failed</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-600 px-4 py-2 rounded-full text-sm font-bold">Not Taken</span>
                                <?php endif; ?>
                            </div>

                            <!-- Class Details -->
                            <div class="bg-gray-50 rounded-xl p-4 mb-4">
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <p class="text-gray-500">Class ID</p>
                                        <p class="font-semibold text-purple-600"><?php echo htmlspecialchars($class['class_id']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Questions</p>
                                        <p class="font-semibold text-gray-800"><?php echo $class['total_questions']; ?></p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Duration</p>
                                        <p class="font-semibold text-gray-800"><?php echo $class['duration']; ?> mins</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Enrolled</p>
                                        <p class="font-semibold text-gray-800"><?php echo date('M d, Y', strtotime($class['enrolled_at'])); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Result Info (if completed) -->
                            <?php if (!empty($class['result_id'])): ?>
                                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-4 mb-4">
                                    <p class="text-sm text-gray-600 mb-2">Your Score:</p>
                                    <p class="text-3xl font-bold <?php echo $class['status'] === 'Pass' ? 'text-green-600' : 'text-red-600'; ?> mb-1">
                                        <?php echo number_format($class['percentage'], 1); ?>%
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <?php echo $class['score']; ?>/<?php echo $class['total_marks']; ?> correct
                                    </p>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <?php if (!empty($class['result_id'])): ?>
                                    <a href="view_result.php?result_id=<?php echo $class['result_id']; ?>" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-3 rounded-lg text-center transition">
                                        View Results
                                    </a>
                                    <a href="reattempt_quiz.php?quiz_id=<?php echo $class['id']; ?>" class="flex-1 bg-purple-500 hover:bg-purple-600 text-white font-semibold px-4 py-3 rounded-lg text-center transition">
                                        Reattempt Quiz
                                    </a>
                                <?php else: ?>
                                    <a href="take_quiz.php?quiz_id=<?php echo $class['id']; ?>" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-3 rounded-lg text-center transition">
                                        Start Quiz
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
