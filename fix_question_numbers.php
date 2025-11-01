<?php
/**
 * Fix Question Numbers Script
 * This script fixes the question numbering for all existing quizzes in the database
 * QuizCraft - Quiz Management System
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Fix Question Numbers - QuizCraft</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-100 min-h-screen p-8'>
    <div class='max-w-3xl mx-auto'>
        <div class='bg-white rounded-2xl shadow-lg p-8'>
            <h1 class='text-3xl font-bold text-gray-800 mb-6'>Fixing Question Numbers</h1>
            <div class='space-y-4'>";

// Get database connection
$conn = getDBConnection();

try {
    // Get all quizzes
    $stmt = $conn->prepare("SELECT id, title FROM quizzes ORDER BY id");
    $stmt->execute();
    $result = $stmt->get_result();
    $quizzes = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo "<p class='text-gray-700'>Found " . count($quizzes) . " quiz(es) to process...</p>";

    // Process each quiz
    foreach ($quizzes as $quiz) {
        $quizId = $quiz['id'];
        $quizTitle = htmlspecialchars($quiz['title']);

        echo "<div class='bg-blue-50 border-l-4 border-blue-500 p-4 mt-4'>";
        echo "<p class='font-semibold text-blue-800'>Processing: {$quizTitle} (ID: {$quizId})</p>";

        // Get all questions for this quiz, ordered by their current question_number
        $stmt = $conn->prepare("SELECT id FROM questions WHERE quiz_id = ? ORDER BY id ASC");
        $stmt->bind_param("i", $quizId);
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo "<p class='text-blue-700'>Found " . count($questions) . " question(s)</p>";

        // Update each question with the correct sequential number
        $questionNumber = 1;
        foreach ($questions as $question) {
            $questionId = $question['id'];

            $updateStmt = $conn->prepare("UPDATE questions SET question_number = ? WHERE id = ?");
            $updateStmt->bind_param("ii", $questionNumber, $questionId);
            $updateStmt->execute();
            $updateStmt->close();

            $questionNumber++;
        }

        echo "<p class='text-green-600 font-semibold'>✓ Updated " . count($questions) . " question(s) successfully</p>";
        echo "</div>";
    }

    echo "<div class='bg-green-100 border-l-4 border-green-500 p-4 mt-6'>";
    echo "<p class='text-green-800 font-bold text-xl'>✓ All quizzes have been fixed successfully!</p>";
    echo "<p class='text-green-700 mt-2'>Question numbers have been corrected for all existing quizzes.</p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='bg-red-100 border-l-4 border-red-500 p-4 mt-6'>";
    echo "<p class='text-red-800 font-bold'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

closeDBConnection($conn);

echo "
            <div class='mt-8 text-center'>
                <a href='index.php' class='bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold px-8 py-3 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition inline-block'>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>";
?>
