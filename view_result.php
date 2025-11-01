<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Student') {
    header('Location: login.php');
    exit();
}

// Include database configuration
require_once 'config/database.php';

// Get result ID or quiz ID
$resultId = isset($_GET['result_id']) ? intval($_GET['result_id']) : 0;
$quizId = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$studentId = $_SESSION['user_id'];

$conn = getDBConnection();

// If quiz_id is provided, find the result_id
if ($quizId > 0 && $resultId === 0) {
    $stmt = $conn->prepare("SELECT id FROM quiz_results WHERE quiz_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $quizId, $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $resultId = $row['id'];
    }
    $stmt->close();
}

if ($resultId === 0) {
    $_SESSION['error_message'] = 'Result not found';
    closeDBConnection($conn);
    header('Location: student_dashboard.php');
    exit();
}

// Get quiz result
$stmt = $conn->prepare("SELECT qr.*, q.title, q.total_questions, q.class_id, u.full_name as teacher_name
                       FROM quiz_results qr
                       JOIN quizzes q ON qr.quiz_id = q.id
                       JOIN users u ON q.teacher_id = u.id
                       WHERE qr.id = ? AND qr.student_id = ?");
$stmt->bind_param("ii", $resultId, $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = 'Result not found or access denied';
    $stmt->close();
    closeDBConnection($conn);
    header('Location: student_dashboard.php');
    exit();
}

$quizResult = $result->fetch_assoc();
$stmt->close();

// Get all questions with answers and student's answers
$stmt = $conn->prepare("SELECT q.*, sa.answer_id as student_answer_id, sa.is_correct as student_is_correct
                       FROM questions q
                       LEFT JOIN student_answers sa ON q.id = sa.question_id AND sa.result_id = ?
                       WHERE q.quiz_id = ?
                       ORDER BY q.question_number ASC");
$stmt->bind_param("ii", $resultId, $quizResult['quiz_id']);
$stmt->execute();
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get answers for each question
foreach ($questions as &$question) {
    $stmt = $conn->prepare("SELECT * FROM answers WHERE question_id = ? ORDER BY id ASC");
    $stmt->bind_param("i", $question['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $question['answers'] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

closeDBConnection($conn);

// Get user data
$fullName = $_SESSION['full_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - QuizCraft</title>
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
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Results Summary -->
        <div class="bg-white rounded-3xl shadow-2xl p-10 mb-10 fade-in-up">
            <div class="text-center">
                <?php if ($quizResult['status'] === 'Pass'): ?>
                    <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-100 mb-6">
                        <svg class="h-14 w-14 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-5xl font-bold text-green-600 mb-2">Congratulations!</h1>
                    <p class="text-2xl text-gray-700 mb-6">You Passed!</p>
                <?php else: ?>
                    <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-red-100 mb-6">
                        <svg class="h-14 w-14 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h1 class="text-5xl font-bold text-red-600 mb-2">Not Quite There</h1>
                    <p class="text-2xl text-gray-700 mb-6">Keep Trying!</p>
                <?php endif; ?>

                <h2 class="text-2xl font-bold text-gray-800 mb-6"><?php echo htmlspecialchars($quizResult['title']); ?></h2>

                <!-- Score Display -->
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl p-8 mb-6">
                    <div class="text-7xl font-bold <?php echo $quizResult['status'] === 'Pass' ? 'text-green-600' : 'text-red-600'; ?> mb-4">
                        <?php echo number_format($quizResult['percentage'], 1); ?>%
                    </div>
                    <p class="text-xl text-gray-700 font-semibold">
                        <?php echo $quizResult['score']; ?> out of <?php echo $quizResult['total_marks']; ?> correct
                    </p>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white rounded-xl p-4 shadow">
                        <p class="text-gray-600 text-sm mb-1">Correct Answers</p>
                        <p class="text-3xl font-bold text-green-600"><?php echo $quizResult['score']; ?></p>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow">
                        <p class="text-gray-600 text-sm mb-1">Incorrect Answers</p>
                        <p class="text-3xl font-bold text-red-600"><?php echo $quizResult['total_marks'] - $quizResult['score']; ?></p>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow">
                        <p class="text-gray-600 text-sm mb-1">Time Taken</p>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $quizResult['time_taken_minutes']; ?> min</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Results -->
        <div class="mb-8 fade-in-up delay-1">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Detailed Results</h2>
        </div>

        <?php foreach ($questions as $index => $question): ?>
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-6 fade-in-up delay-<?php echo min($index + 2, 3); ?>">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-800 flex items-start flex-1">
                        <span class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full flex items-center justify-center text-white font-bold mr-3 flex-shrink-0"><?php echo $question['question_number']; ?></span>
                        <span class="pt-1"><?php echo htmlspecialchars($question['question_text']); ?></span>
                    </h3>
                    <?php if (!$question['student_answer_id']): ?>
                        <span class="bg-gray-100 text-gray-600 px-4 py-2 rounded-full text-sm font-bold flex items-center flex-shrink-0">
                            <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                            </svg>
                            Not Answered
                        </span>
                    <?php elseif ($question['student_is_correct']): ?>
                        <span class="bg-green-100 text-green-600 px-4 py-2 rounded-full text-sm font-bold flex items-center flex-shrink-0">
                            <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            Correct
                        </span>
                    <?php else: ?>
                        <span class="bg-red-100 text-red-600 px-4 py-2 rounded-full text-sm font-bold flex items-center flex-shrink-0">
                            <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>
                            </svg>
                            Incorrect
                        </span>
                    <?php endif; ?>
                </div>

                <div class="space-y-3 mt-6">
                    <?php if (!$question['student_answer_id']): ?>
                        <div class="bg-yellow-50 border-2 border-yellow-400 rounded-xl p-4 mb-4">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-yellow-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                                </svg>
                                <span class="text-yellow-800 font-semibold">You did not answer this question (Time ran out)</span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($question['answers'] as $ansIndex => $answer): ?>
                        <?php
                            $letter = chr(65 + $ansIndex);
                            $isStudentAnswer = ($answer['id'] == $question['student_answer_id']);
                            $isCorrectAnswer = $answer['is_correct'];

                            if ($isCorrectAnswer) {
                                $bgClass = 'bg-green-50 border-green-500 border-2';
                                $textClass = 'text-green-800';
                                $badgeClass = 'bg-green-500 text-white';
                            } elseif ($isStudentAnswer && !$isCorrectAnswer) {
                                $bgClass = 'bg-red-50 border-red-500 border-2';
                                $textClass = 'text-red-800';
                                $badgeClass = 'bg-red-500 text-white';
                            } else {
                                $bgClass = 'bg-gray-50 border-gray-200 border-2';
                                $textClass = 'text-gray-700';
                                $badgeClass = 'bg-gray-300 text-gray-700';
                            }
                        ?>
                        <div class="<?php echo $bgClass; ?> rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center flex-1">
                                    <span class="inline-block w-8 h-8 <?php echo $badgeClass; ?> rounded-full text-center font-bold mr-3 leading-8"><?php echo $letter; ?></span>
                                    <span class="<?php echo $textClass; ?> font-medium"><?php echo htmlspecialchars($answer['answer_text']); ?></span>
                                </div>
                                <div class="flex gap-2">
                                    <?php if ($isStudentAnswer && $question['student_answer_id']): ?>
                                        <span class="text-xs bg-blue-500 text-white px-3 py-1 rounded-full font-semibold">Your Answer</span>
                                    <?php endif; ?>
                                    <?php if ($isCorrectAnswer): ?>
                                        <span class="text-xs bg-green-500 text-white px-3 py-1 rounded-full font-semibold">Correct Answer</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Action Buttons -->
        <div class="bg-white rounded-2xl shadow-lg p-8 fade-in-up">
            <div class="flex justify-center gap-4">
                <a href="student_dashboard.php" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-lg px-10 py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition">
                    Back to Dashboard
                </a>
            </div>
        </div>

    </div>

</body>
</html>
