<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Student') {
    header('Location: login.php');
    exit();
}

// Include database configuration
require_once 'config/database.php';

// Get quiz ID
if (!isset($_GET['quiz_id']) || empty($_GET['quiz_id'])) {
    header('Location: student_dashboard.php');
    exit();
}

$quizId = intval($_GET['quiz_id']);
$studentId = $_SESSION['user_id'];

// Get quiz details
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT q.*, u.full_name as teacher_name FROM quizzes q
                       JOIN users u ON q.teacher_id = u.id
                       WHERE q.id = ? AND q.is_active = 1");
$stmt->bind_param("i", $quizId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = 'Quiz not found';
    $stmt->close();
    closeDBConnection($conn);
    header('Location: student_dashboard.php');
    exit();
}

$quiz = $result->fetch_assoc();
$stmt->close();

// Note: We removed the "already taken" check to allow reattempts

// Get all questions with answers
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - QuizCraft</title>
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

        .answer-option {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .answer-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .answer-option.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }

        .timer {
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

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
                </div>

                <!-- Timer -->
                <div class="bg-white bg-opacity-20 px-6 py-3 rounded-full">
                    <div class="flex items-center space-x-2">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                        </svg>
                        <span class="text-white font-bold text-xl timer" id="timer"><?php echo $quiz['duration']; ?>:00</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Quiz Header -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8 fade-in-up">
            <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <p class="text-gray-600">Teacher: <?php echo htmlspecialchars($quiz['teacher_name']); ?></p>
            <div class="mt-4 flex gap-4 text-sm">
                <span class="bg-blue-100 text-blue-600 px-4 py-2 rounded-full font-semibold"><?php echo $quiz['total_questions']; ?> Questions</span>
                <span class="bg-purple-100 text-purple-600 px-4 py-2 rounded-full font-semibold"><?php echo $quiz['duration']; ?> Minutes</span>
            </div>
        </div>

        <!-- Quiz Form -->
        <form id="quizForm" method="POST" action="submit_quiz.php">
            <input type="hidden" name="quiz_id" value="<?php echo $quizId; ?>">
            <input type="hidden" name="start_time" id="startTime" value="<?php echo time(); ?>">

            <!-- Questions -->
            <?php foreach ($questions as $index => $question): ?>
                <div class="bg-white rounded-2xl shadow-lg p-8 mb-6 fade-in-up">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full flex items-center justify-center text-white font-bold mr-3"><?php echo $question['question_number']; ?></span>
                        <?php echo htmlspecialchars($question['question_text']); ?>
                    </h3>

                    <div class="space-y-3">
                        <?php foreach ($question['answers'] as $ansIndex => $answer): ?>
                            <?php $letter = chr(65 + $ansIndex); // A, B, C, D... ?>
                            <div class="answer-option border-2 border-gray-300 rounded-xl p-4" onclick="selectAnswer(<?php echo $question['id']; ?>, <?php echo $answer['id']; ?>, this)">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="question_<?php echo $question['id']; ?>" value="<?php echo $answer['id']; ?>" required class="hidden">
                                    <span class="inline-block w-8 h-8 bg-purple-100 text-purple-600 rounded-full text-center font-bold mr-3 leading-8"><?php echo $letter; ?></span>
                                    <span class="flex-1"><?php echo htmlspecialchars($answer['answer_text']); ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Submit Button -->
            <div class="bg-white rounded-2xl shadow-lg p-8 fade-in-up">
                <div class="flex justify-between items-center">
                    <p class="text-gray-600">Make sure you've answered all questions before submitting</p>
                    <button type="submit" class="bg-gradient-to-r from-green-600 to-green-700 text-white font-bold text-lg px-10 py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition">
                        Submit Quiz
                    </button>
                </div>
            </div>
        </form>

    </div>

    <!-- Time Up Modal -->
    <div id="timeUpModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-md w-full">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 mb-4">
                    <svg class="h-12 w-12 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                    </svg>
                </div>
                <h3 class="text-4xl font-bold text-red-600 mb-2">Time is Over!</h3>
                <p class="text-lg text-gray-600 mb-6">
                    Your quiz time has expired. Submitting your answers now...
                </p>
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600 mx-auto"></div>
            </div>
        </div>
    </div>

    <script>
        // Timer countdown
        let timeLeft = <?php echo $quiz['duration'] * 60; ?>; // Convert to seconds
        let timerInterval;

        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            const timerDisplay = document.getElementById('timer');

            timerDisplay.textContent = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

            // Change color when time is running low
            if (timeLeft <= 60) {
                timerDisplay.classList.add('text-red-500');
                timerDisplay.classList.remove('text-white');
            } else if (timeLeft <= 300) {
                timerDisplay.classList.add('text-yellow-300');
            }

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                showTimeUpModal();
            } else {
                timeLeft--;
            }
        }

        function showTimeUpModal() {
            // Show time up modal
            document.getElementById('timeUpModal').classList.remove('hidden');

            // Disable all answer options but keep form functional
            const answerOptions = document.querySelectorAll('.answer-option');
            answerOptions.forEach(option => {
                option.style.pointerEvents = 'none';
                option.style.opacity = '0.6';
            });

            // Auto submit the form after 2 seconds
            setTimeout(() => {
                document.getElementById('quizForm').submit();
            }, 2000);
        }

        // Update timer every second
        timerInterval = setInterval(updateTimer, 1000);

        // Select answer function
        function selectAnswer(questionId, answerId, element) {
            // Remove selected class from all options for this question
            const questionDiv = element.closest('.bg-white');
            questionDiv.querySelectorAll('.answer-option').forEach(opt => {
                opt.classList.remove('selected');
            });

            // Add selected class to clicked option
            element.classList.add('selected');

            // Check the radio button
            element.querySelector('input[type="radio"]').checked = true;
        }

        // Confirm before leaving page
        window.addEventListener('beforeunload', function (e) {
            e.preventDefault();
            e.returnValue = '';
        });

        // Form submission validation
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            const totalQuestions = <?php echo count($questions); ?>;
            let answeredCount = 0;

            <?php foreach ($questions as $question): ?>
            if (document.querySelector('input[name="question_<?php echo $question['id']; ?>"]:checked')) {
                answeredCount++;
            }
            <?php endforeach; ?>

            if (answeredCount < totalQuestions) {
                if (!confirm(`You have answered ${answeredCount} out of ${totalQuestions} questions. Submit anyway?`)) {
                    e.preventDefault();
                }
            }
        });
    </script>

</body>
</html>
