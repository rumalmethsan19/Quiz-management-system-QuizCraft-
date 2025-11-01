<?php
/**
 * Check Question Numbers
 * This script shows the current state of question numbering in the database
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Check Question Numbers - QuizCraft</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-100 min-h-screen p-8'>
    <div class='max-w-5xl mx-auto'>
        <div class='bg-white rounded-2xl shadow-lg p-8'>
            <h1 class='text-3xl font-bold text-gray-800 mb-6'>Question Numbers Database Check</h1>";

$conn = getDBConnection();

// Get all quizzes with their questions
$stmt = $conn->prepare("
    SELECT
        q.id as quiz_id,
        q.title,
        q.class_id,
        qu.id as question_id,
        qu.question_number,
        qu.question_text
    FROM quizzes q
    LEFT JOIN questions qu ON q.id = qu.quiz_id
    ORDER BY q.id, qu.id
");
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$currentQuizId = null;
$questionCount = 0;

foreach ($data as $row) {
    if ($currentQuizId !== $row['quiz_id']) {
        if ($currentQuizId !== null) {
            echo "</div></div>";
        }

        $currentQuizId = $row['quiz_id'];
        $questionCount = 0;

        echo "<div class='bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 mb-6'>";
        echo "<h2 class='text-2xl font-bold text-gray-800 mb-2'>" . htmlspecialchars($row['title']) . "</h2>";
        echo "<p class='text-gray-600 mb-4'>Class ID: <span class='font-mono font-bold'>" . htmlspecialchars($row['class_id']) . "</span></p>";
        echo "<div class='space-y-2'>";
    }

    $questionCount++;
    $isCorrect = ($row['question_number'] == $questionCount);
    $statusColor = $isCorrect ? 'green' : 'red';
    $statusIcon = $isCorrect ? '✓' : '✗';

    echo "<div class='bg-white rounded-lg p-4 border-l-4 border-{$statusColor}-500'>";
    echo "<div class='flex items-start gap-3'>";
    echo "<span class='w-8 h-8 bg-{$statusColor}-100 text-{$statusColor}-600 rounded-full flex items-center justify-center font-bold flex-shrink-0'>{$row['question_number']}</span>";
    echo "<div class='flex-1'>";
    echo "<p class='text-gray-700'>" . htmlspecialchars(substr($row['question_text'], 0, 80)) . "...</p>";
    echo "<p class='text-sm text-{$statusColor}-600 font-semibold mt-1'>{$statusIcon} DB Number: {$row['question_number']} | Expected: {$questionCount} | Question ID: {$row['question_id']}</p>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
}

if ($currentQuizId !== null) {
    echo "</div></div>";
}

closeDBConnection($conn);

echo "
            <div class='mt-8 flex gap-4 justify-center'>
                <a href='fix_question_numbers.php' class='bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold px-8 py-3 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition inline-block'>
                    Run Fix Script
                </a>
                <a href='index.php' class='bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold px-8 py-3 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition inline-block'>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>";
?>
