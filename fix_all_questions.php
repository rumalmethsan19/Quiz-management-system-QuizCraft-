<?php
/**
 * COMPREHENSIVE Question Number Fix
 * This will fix ALL quizzes in the database
 */

session_start();
require_once 'config/database.php';

// Check if user is logged in as teacher (optional - comment out for direct access)
// if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Teacher') {
//     die('Access denied. Please login as teacher.');
// }

$isFixed = false;
$message = '';
$quizzesFixed = [];

if (isset($_POST['fix_now'])) {
    $conn = getDBConnection();

    try {
        // Get all quizzes
        $quizResult = $conn->query("SELECT id, title FROM quizzes ORDER BY id ASC");

        if ($quizResult && $quizResult->num_rows > 0) {
            while ($quiz = $quizResult->fetch_assoc()) {
                $quizId = $quiz['id'];
                $quizTitle = $quiz['title'];

                // Get all questions for this quiz, ordered by ID (insertion order)
                $stmt = $conn->prepare("SELECT id FROM questions WHERE quiz_id = ? ORDER BY id ASC");
                $stmt->bind_param("i", $quizId);
                $stmt->execute();
                $questionsResult = $stmt->get_result();

                $questionNumber = 1;
                $updatedCount = 0;

                // Update each question with correct numbering
                while ($question = $questionsResult->fetch_assoc()) {
                    $questionId = $question['id'];

                    $updateStmt = $conn->prepare("UPDATE questions SET question_number = ? WHERE id = ?");
                    $updateStmt->bind_param("ii", $questionNumber, $questionId);
                    $updateStmt->execute();
                    $updateStmt->close();

                    $updatedCount++;
                    $questionNumber++;
                }

                $stmt->close();

                $quizzesFixed[] = [
                    'title' => $quizTitle,
                    'questions' => $updatedCount
                ];
            }

            $isFixed = true;
            $message = 'All quizzes have been fixed successfully!';
        } else {
            $message = 'No quizzes found in the database.';
        }

    } catch (Exception $e) {
        $message = 'Error fixing quizzes: ' . $e->getMessage();
    }

    closeDBConnection($conn);
}

?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Fix All Question Numbers - QuizCraft</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-100 min-h-screen p-8'>
    <div class='max-w-4xl mx-auto'>
        <div class='bg-white rounded-2xl shadow-lg p-8'>
            <h1 class='text-3xl font-bold text-gray-800 mb-6'>Fix Question Numbers</h1>

            <div class='mb-6'>
                <p class='text-gray-600 mb-4'>
                    This tool will renumber all questions in all quizzes to ensure they are numbered correctly (1, 2, 3, ...).
                </p>
                <p class='text-gray-600 mb-4'>
                    <strong>What it does:</strong>
                </p>
                <ul class='list-disc list-inside text-gray-600 mb-4 space-y-2'>
                    <li>Scans all quizzes in the database</li>
                    <li>Renumbers questions based on their insertion order (by ID)</li>
                    <li>Fixes any duplicate or incorrect question numbers</li>
                </ul>
            </div>

            <?php if ($isFixed): ?>
                <div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6' role='alert'>
                    <p class='font-bold'>Success!</p>
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>

                <?php if (!empty($quizzesFixed)): ?>
                    <div class='mb-6'>
                        <h2 class='text-xl font-bold text-gray-800 mb-3'>Fixed Quizzes:</h2>
                        <div class='space-y-2'>
                            <?php foreach ($quizzesFixed as $quiz): ?>
                                <div class='bg-gray-50 p-3 rounded-lg'>
                                    <p class='font-semibold'><?php echo htmlspecialchars($quiz['title']); ?></p>
                                    <p class='text-sm text-gray-600'><?php echo $quiz['questions']; ?> questions renumbered</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <a href='quiz_management.php' class='inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition'>
                    Back to Quiz Management
                </a>

            <?php elseif (!empty($message)): ?>
                <div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6' role='alert'>
                    <p class='font-bold'>Error</p>
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>

            <?php else: ?>
                <form method='POST' onsubmit='return confirm("Are you sure you want to fix all question numbers? This will update the database.");'>
                    <button type='submit' name='fix_now' class='bg-gradient-to-r from-green-600 to-green-700 text-white font-bold px-8 py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition'>
                        Fix All Questions Now
                    </button>
                </form>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>