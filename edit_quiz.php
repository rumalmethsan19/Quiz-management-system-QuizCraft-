<?php
/**
 * Edit Quiz - Teachers can edit quiz questions and answers
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

closeDBConnection($conn);

// Get user data
$fullName = $_SESSION['full_name'];

// Get success/error messages
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz - <?php echo htmlspecialchars($quiz['title']); ?></title>
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

        .question-card {
            transition: all 0.3s ease;
        }

        .question-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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

                <!-- Back Button -->
                <a href="quiz_management.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold px-6 py-2 rounded-full transition">
                    ← Back to Quiz Management
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Success Message -->
        <?php if (!empty($successMessage)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg fade-in-up" role="alert">
                <p class="font-bold">Success!</p>
                <p><?php echo htmlspecialchars($successMessage); ?></p>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (!empty($errorMessage)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg fade-in-up" role="alert">
                <p class="font-bold">Error!</p>
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
        <?php endif; ?>

        <!-- Quiz Header -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8 fade-in-up">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Edit Quiz</h1>
            <p class="text-xl text-gray-600 mb-4"><?php echo htmlspecialchars($quiz['title']); ?></p>
            <div class="flex gap-4 text-sm">
                <span class="bg-blue-100 text-blue-600 px-4 py-2 rounded-full font-semibold">Class ID: <?php echo htmlspecialchars($quiz['class_id']); ?></span>
                <span class="bg-purple-100 text-purple-600 px-4 py-2 rounded-full font-semibold"><?php echo $quiz['total_questions']; ?> Questions</span>
                <span class="bg-green-100 text-green-600 px-4 py-2 rounded-full font-semibold"><?php echo $quiz['duration']; ?> Minutes</span>
            </div>
        </div>

        <!-- Edit Quiz Form -->
        <form method="POST" action="edit_quiz_process.php" id="editQuizForm">
            <input type="hidden" name="quiz_id" value="<?php echo $quizId; ?>">

            <!-- Questions -->
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-card bg-white rounded-2xl shadow-lg p-8 mb-6 fade-in-up">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center">
                            <span class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full flex items-center justify-center text-white font-bold mr-3"><?php echo $question['question_number']; ?></span>
                            Question <?php echo $question['question_number']; ?>
                        </h3>
                    </div>

                    <input type="hidden" name="questions[<?php echo $question['id']; ?>][id]" value="<?php echo $question['id']; ?>">
                    <input type="hidden" name="questions[<?php echo $question['id']; ?>][question_number]" value="<?php echo $question['question_number']; ?>">

                    <!-- Question Text -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-2">Question Text:</label>
                        <textarea name="questions[<?php echo $question['id']; ?>][text]" rows="3" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 transition"><?php echo htmlspecialchars($question['question_text']); ?></textarea>
                    </div>

                    <!-- Answers -->
                    <div class="space-y-3">
                        <label class="block text-gray-700 font-semibold mb-2">Answers:</label>
                        <?php foreach ($question['answers'] as $ansIndex => $answer): ?>
                            <?php $letter = chr(65 + $ansIndex); // A, B, C, D... ?>
                            <div class="border-2 border-gray-300 rounded-lg p-4">
                                <input type="hidden" name="questions[<?php echo $question['id']; ?>][answers][<?php echo $answer['id']; ?>][id]" value="<?php echo $answer['id']; ?>">

                                <div class="flex items-center gap-4">
                                    <!-- Answer Letter -->
                                    <span class="inline-block w-8 h-8 bg-purple-100 text-purple-600 rounded-full text-center font-bold leading-8 flex-shrink-0"><?php echo $letter; ?></span>

                                    <!-- Answer Text -->
                                    <input type="text" name="questions[<?php echo $question['id']; ?>][answers][<?php echo $answer['id']; ?>][text]" value="<?php echo htmlspecialchars($answer['answer_text']); ?>" required class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-purple-500 transition">

                                    <!-- Correct Answer Checkbox -->
                                    <label class="flex items-center gap-2 cursor-pointer flex-shrink-0">
                                        <input type="checkbox" name="questions[<?php echo $question['id']; ?>][answers][<?php echo $answer['id']; ?>][is_correct]" value="1" <?php echo $answer['is_correct'] ? 'checked' : ''; ?> class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                        <span class="text-sm font-semibold text-green-600">Correct</span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Submit Button -->
            <div class="bg-white rounded-2xl shadow-lg p-8 fade-in-up">
                <div class="flex justify-between items-center">
                    <a href="quiz_management.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold text-lg px-8 py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition">
                        Cancel
                    </a>
                    <button type="submit" class="bg-gradient-to-r from-green-600 to-green-700 text-white font-bold text-lg px-10 py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>

    </div>

    <script>
        // Confirm before leaving page with unsaved changes
        let formChanged = false;
        const form = document.getElementById('editQuizForm');

        form.addEventListener('change', function() {
            formChanged = true;
        });

        window.addEventListener('beforeunload', function (e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        form.addEventListener('submit', function() {
            formChanged = false;
        });

        // Form validation
        form.addEventListener('submit', function(e) {
            // Check that each question has at least one correct answer
            let valid = true;
            const questions = document.querySelectorAll('.question-card');

            questions.forEach((questionCard, index) => {
                const checkboxes = questionCard.querySelectorAll('input[type="checkbox"]');
                const hasChecked = Array.from(checkboxes).some(cb => cb.checked);

                if (!hasChecked) {
                    valid = false;
                    alert('Each question must have at least one correct answer!');
                }
            });

            if (!valid) {
                e.preventDefault();
            }
        });
    </script>

</body>
</html>
