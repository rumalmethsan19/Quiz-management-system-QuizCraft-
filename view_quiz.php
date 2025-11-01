<?php
/**
 * View Quiz - Teachers can view quiz details and student results
 * QuizCraft - Quiz Management System
 */

session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Teacher') {
    header('Location: login.php');
    exit();
}

// Include database configuration
require_once 'config/database.php';

// Get quiz ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'Invalid quiz ID';
    header('Location: quiz_management.php');
    exit();
}

$quizId = intval($_GET['id']);
$teacherId = $_SESSION['user_id'];

// Get database connection
$conn = getDBConnection();

// Get quiz details - verify it belongs to this teacher
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $quizId, $teacherId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = 'Quiz not found or access denied';
    $stmt->close();
    closeDBConnection($conn);
    header('Location: quiz_management.php');
    exit();
}

$quiz = $result->fetch_assoc();
$stmt->close();

// Get all questions with their answers
$stmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY question_number ASC");
$stmt->bind_param("i", $quizId);
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

// Get student results - ordered by score descending
$stmt = $conn->prepare("
    SELECT qr.*, u.full_name, u.email
    FROM quiz_results qr
    JOIN users u ON qr.student_id = u.id
    WHERE qr.quiz_id = ?
    ORDER BY qr.score DESC, qr.percentage DESC, qr.submitted_at ASC
");
$stmt->bind_param("i", $quizId);
$stmt->execute();
$result = $stmt->get_result();
$studentResults = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate statistics
$totalStudents = count($studentResults);
$passedStudents = count(array_filter($studentResults, function($r) { return $r['status'] === 'Pass'; }));
$failedStudents = $totalStudents - $passedStudents;
$averageScore = $totalStudents > 0 ? array_sum(array_column($studentResults, 'percentage')) / $totalStudents : 0;

closeDBConnection($conn);

// Get user data
$fullName = $_SESSION['full_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Quiz - <?php echo htmlspecialchars($quiz['title']); ?></title>
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

        .tab-button {
            transition: all 0.3s ease;
        }

        .tab-button.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Top Navigation Bar -->
    <nav class="gradient-bg shadow-lg no-print">
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

                <!-- Back Button -->
                <a href="quiz_management.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold px-6 py-2 rounded-full transition">
                    ← Back to Quiz Management
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Quiz Header -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8 fade-in-up">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($quiz['title']); ?></h1>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($quiz['description']); ?></p>
                </div>
                <?php if ($quiz['is_active']): ?>
                    <span class="bg-green-100 text-green-600 px-4 py-2 rounded-full text-sm font-semibold">Active</span>
                <?php else: ?>
                    <span class="bg-gray-100 text-gray-600 px-4 py-2 rounded-full text-sm font-semibold">Inactive</span>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-blue-600 font-semibold">Class ID</p>
                    <p class="text-2xl font-bold text-blue-700"><?php echo htmlspecialchars($quiz['class_id']); ?></p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <p class="text-purple-600 font-semibold">Total Questions</p>
                    <p class="text-2xl font-bold text-purple-700"><?php echo $quiz['total_questions']; ?></p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-green-600 font-semibold">Duration</p>
                    <p class="text-2xl font-bold text-green-700"><?php echo $quiz['duration']; ?> mins</p>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg">
                    <p class="text-orange-600 font-semibold">Created</p>
                    <p class="text-lg font-bold text-orange-700"><?php echo date('M d, Y', strtotime($quiz['created_at'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Students</p>
                        <p class="text-4xl font-bold text-blue-600"><?php echo $totalStudents; ?></p>
                    </div>
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Passed</p>
                        <p class="text-4xl font-bold text-green-600"><?php echo $passedStudents; ?></p>
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
                        <p class="text-gray-600 text-sm">Failed</p>
                        <p class="text-4xl font-bold text-red-600"><?php echo $failedStudents; ?></p>
                    </div>
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Average Score</p>
                        <p class="text-4xl font-bold text-purple-600"><?php echo number_format($averageScore, 1); ?>%</p>
                    </div>
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-2xl shadow-lg p-2 mb-8 flex gap-2 no-print">
            <button onclick="showTab('questions')" id="questionsTab" class="tab-button active flex-1 px-6 py-3 rounded-lg font-semibold">
                Questions & Answers
            </button>
            <button onclick="showTab('results')" id="resultsTab" class="tab-button flex-1 px-6 py-3 rounded-lg font-semibold bg-gray-100 text-gray-700">
                Student Results
            </button>
        </div>

        <!-- Questions Tab Content -->
        <div id="questionsContent" class="tab-content">
            <div class="mb-6 no-print">
                <button onclick="window.print()" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition">
                    Print Questions
                </button>
            </div>

            <?php foreach ($questions as $index => $question): ?>
                <div class="bg-white rounded-2xl shadow-lg p-8 mb-6 fade-in-up">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full flex items-center justify-center text-white font-bold mr-3"><?php echo $question['question_number']; ?></span>
                        <?php echo htmlspecialchars($question['question_text']); ?>
                    </h3>

                    <div class="space-y-3">
                        <?php foreach ($question['answers'] as $ansIndex => $answer): ?>
                            <?php $letter = chr(65 + $ansIndex); // A, B, C, D... ?>
                            <div class="border-2 <?php echo $answer['is_correct'] ? 'border-green-500 bg-green-50' : 'border-gray-300'; ?> rounded-xl p-4">
                                <div class="flex items-center">
                                    <span class="inline-block w-8 h-8 <?php echo $answer['is_correct'] ? 'bg-green-500 text-white' : 'bg-purple-100 text-purple-600'; ?> rounded-full text-center font-bold mr-3 leading-8"><?php echo $letter; ?></span>
                                    <span class="flex-1"><?php echo htmlspecialchars($answer['answer_text']); ?></span>
                                    <?php if ($answer['is_correct']): ?>
                                        <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">Correct Answer</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Results Tab Content -->
        <div id="resultsContent" class="tab-content hidden">
            <?php if (empty($studentResults)): ?>
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                    </svg>
                    <p class="text-xl text-gray-500 mb-2">No students have taken this quiz yet</p>
                    <p class="text-gray-400">Results will appear here once students complete the quiz</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="gradient-bg text-white">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-bold">Rank</th>
                                    <th class="px-6 py-4 text-left text-sm font-bold">Student Name</th>
                                    <th class="px-6 py-4 text-left text-sm font-bold">Email</th>
                                    <th class="px-6 py-4 text-center text-sm font-bold">Score</th>
                                    <th class="px-6 py-4 text-center text-sm font-bold">Percentage</th>
                                    <th class="px-6 py-4 text-center text-sm font-bold">Status</th>
                                    <th class="px-6 py-4 text-center text-sm font-bold">Time Taken</th>
                                    <th class="px-6 py-4 text-center text-sm font-bold">Submitted At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($studentResults as $rank => $result): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <?php if ($rank === 0): ?>
                                                    <span class="text-2xl">🥇</span>
                                                <?php elseif ($rank === 1): ?>
                                                    <span class="text-2xl">🥈</span>
                                                <?php elseif ($rank === 2): ?>
                                                    <span class="text-2xl">🥉</span>
                                                <?php else: ?>
                                                    <span class="text-lg font-bold text-gray-600">#<?php echo $rank + 1; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($result['full_name']); ?></p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($result['email']); ?></p>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <p class="font-bold text-gray-800"><?php echo $result['score']; ?> / <?php echo $result['total_marks']; ?></p>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <p class="font-bold text-lg <?php echo $result['percentage'] >= 50 ? 'text-green-600' : 'text-red-600'; ?>">
                                                <?php echo number_format($result['percentage'], 1); ?>%
                                            </p>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <?php if ($result['status'] === 'Pass'): ?>
                                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold">Pass</span>
                                            <?php else: ?>
                                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-semibold">Fail</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <p class="text-gray-600"><?php echo $result['time_taken_minutes']; ?> mins</p>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <p class="text-gray-600 text-sm"><?php echo date('M d, Y', strtotime($result['submitted_at'])); ?></p>
                                            <p class="text-gray-500 text-xs"><?php echo date('h:i A', strtotime($result['submitted_at'])); ?></p>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
                button.classList.add('bg-gray-100', 'text-gray-700');
            });

            // Show selected tab content
            document.getElementById(tabName + 'Content').classList.remove('hidden');

            // Add active class to selected tab button
            const activeButton = document.getElementById(tabName + 'Tab');
            activeButton.classList.add('active');
            activeButton.classList.remove('bg-gray-100', 'text-gray-700');
        }
    </script>

</body>
</html>
