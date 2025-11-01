<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Student') {
    header('Location: login.php');
    exit();
}

// Check if quiz_id is provided
if (!isset($_GET['quiz_id']) || empty($_GET['quiz_id'])) {
    header('Location: my_classes.php');
    exit();
}

// Include database configuration
require_once 'config/database.php';

$quizId = intval($_GET['quiz_id']);
$studentId = $_SESSION['user_id'];

// Get database connection
$conn = getDBConnection();

// Get the result_id for this quiz and student
$stmt = $conn->prepare("SELECT id FROM quiz_results WHERE quiz_id = ? AND student_id = ?");
$stmt->bind_param("ii", $quizId, $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $resultId = $row['id'];
    $stmt->close();

    // Delete only the student answers for this result
    $stmt = $conn->prepare("DELETE FROM student_answers WHERE result_id = ?");
    $stmt->bind_param("i", $resultId);
    $stmt->execute();
    $stmt->close();

    // Delete the quiz result (this will allow a fresh attempt)
    $stmt = $conn->prepare("DELETE FROM quiz_results WHERE id = ?");
    $stmt->bind_param("i", $resultId);
    $stmt->execute();
    $stmt->close();
}

closeDBConnection($conn);

// Redirect to take quiz page
header('Location: take_quiz.php?quiz_id=' . $quizId);
exit();
?>
