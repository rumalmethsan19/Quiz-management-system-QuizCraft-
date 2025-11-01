<?php
/**
 * Quiz Submission Handler
 * QuizCraft - Quiz Management System
 */

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

    $quizId = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
    $studentId = $_SESSION['user_id'];
    $startTime = isset($_POST['start_time']) ? intval($_POST['start_time']) : time();
    $endTime = time();
    $timeTaken = round(($endTime - $startTime) / 60); // Convert to minutes

    if ($quizId === 0) {
        $_SESSION['error_message'] = 'Invalid quiz';
        header('Location: student_dashboard.php');
        exit();
    }

    // Get database connection
    $conn = getDBConnection();

    // Note: We allow reattempts, so we don't check for existing results here
    // The reattempt_quiz.php page handles deleting old results before redirecting here

    // Get quiz details
    $stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();
    $quiz = $result->fetch_assoc();
    $stmt->close();

    if (!$quiz) {
        $_SESSION['error_message'] = 'Quiz not found';
        closeDBConnection($conn);
        header('Location: student_dashboard.php');
        exit();
    }

    // Get all questions
    $stmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ?");
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Calculate score
    $totalQuestions = count($questions);
    $correctAnswers = 0;
    $studentAnswers = [];
    $answeredQuestions = 0;

    foreach ($questions as $question) {
        $questionId = $question['id'];
        $selectedAnswerId = isset($_POST['question_' . $questionId]) ? intval($_POST['question_' . $questionId]) : 0;

        if ($selectedAnswerId > 0) {
            $answeredQuestions++;

            // Get the selected answer
            $stmt = $conn->prepare("SELECT * FROM answers WHERE id = ?");
            $stmt->bind_param("i", $selectedAnswerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $selectedAnswer = $result->fetch_assoc();
            $stmt->close();

            if ($selectedAnswer) {
                $isCorrect = $selectedAnswer['is_correct'];
                if ($isCorrect) {
                    $correctAnswers++;
                }

                $studentAnswers[] = [
                    'question_id' => $questionId,
                    'answer_id' => $selectedAnswerId,
                    'is_correct' => $isCorrect
                ];
            }
        } else {
            // Question not answered - get first answer as default (marked as incorrect)
            $stmt = $conn->prepare("SELECT id FROM answers WHERE question_id = ? LIMIT 1");
            $stmt->bind_param("i", $questionId);
            $stmt->execute();
            $result = $stmt->get_result();
            $firstAnswer = $result->fetch_assoc();
            $stmt->close();

            if ($firstAnswer) {
                $studentAnswers[] = [
                    'question_id' => $questionId,
                    'answer_id' => $firstAnswer['id'],
                    'is_correct' => 0
                ];
            }
        }
    }

    // Calculate marks and percentage
    $totalMarks = $totalQuestions; // 1 mark per question
    $score = $correctAnswers;
    $percentage = ($correctAnswers / $totalQuestions) * 100;
    $status = $percentage >= 50 ? 'Pass' : 'Fail'; // 50% passing grade

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert quiz result
        $stmt = $conn->prepare("INSERT INTO quiz_results (quiz_id, student_id, score, total_marks, percentage, status, time_taken_minutes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiidsi", $quizId, $studentId, $score, $totalMarks, $percentage, $status, $timeTaken);
        $stmt->execute();
        $resultId = $conn->insert_id;
        $stmt->close();

        // Insert student answers
        foreach ($studentAnswers as $answer) {
            $stmt = $conn->prepare("INSERT INTO student_answers (result_id, question_id, answer_id, is_correct) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiii", $resultId, $answer['question_id'], $answer['answer_id'], $answer['is_correct']);
            $stmt->execute();
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();

        // Redirect to results page
        closeDBConnection($conn);
        header('Location: view_result.php?result_id=' . $resultId);
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = 'Failed to submit quiz. Please try again.';
        closeDBConnection($conn);
        header('Location: student_dashboard.php');
        exit();
    }

} else {
    // If not POST request, redirect to dashboard
    header('Location: student_dashboard.php');
    exit();
}
?>
