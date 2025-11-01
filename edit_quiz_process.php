<?php
/**
 * Edit Quiz Process Handler
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

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $teacherId = $_SESSION['user_id'];
    $quizId = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
    $questions = isset($_POST['questions']) ? $_POST['questions'] : [];

    if ($quizId === 0) {
        $_SESSION['error_message'] = 'Invalid quiz ID.';
        header('Location: quiz_management.php');
        exit();
    }

    // Get database connection
    $conn = getDBConnection();

    // Verify quiz belongs to this teacher
    $stmt = $conn->prepare("SELECT id FROM quizzes WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $quizId, $teacherId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error_message'] = 'Quiz not found or access denied.';
        $stmt->close();
        closeDBConnection($conn);
        header('Location: quiz_management.php');
        exit();
    }
    $stmt->close();

    // Validation
    $errors = [];

    if (empty($questions)) {
        $errors[] = 'No questions to update.';
    }

    // Validate each question
    foreach ($questions as $questionId => $question) {
        if (empty($question['text'])) {
            $errors[] = "Question {$question['question_number']} text is required.";
        }

        // Check if at least one answer is marked as correct
        $hasCorrectAnswer = false;
        if (isset($question['answers'])) {
            foreach ($question['answers'] as $answerId => $answer) {
                if (isset($answer['is_correct']) && $answer['is_correct'] == 1) {
                    $hasCorrectAnswer = true;
                    break;
                }
            }
        }

        if (!$hasCorrectAnswer) {
            $errors[] = "Question {$question['question_number']} must have at least one correct answer.";
        }
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
        header("Location: edit_quiz.php?id=$quizId");
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update questions and answers
        foreach ($questions as $questionId => $question) {
            // Update question
            $stmt = $conn->prepare("UPDATE questions SET question_text = ? WHERE id = ? AND quiz_id = ?");
            $questionText = trim($question['text']);
            $stmt->bind_param("sii", $questionText, $questionId, $quizId);
            $stmt->execute();
            $stmt->close();

            // Update answers
            if (isset($question['answers'])) {
                foreach ($question['answers'] as $answerId => $answer) {
                    $answerText = trim($answer['text']);
                    $isCorrect = isset($answer['is_correct']) && $answer['is_correct'] == 1 ? 1 : 0;

                    $stmt = $conn->prepare("UPDATE answers SET answer_text = ?, is_correct = ? WHERE id = ? AND question_id = ?");
                    $stmt->bind_param("siii", $answerText, $isCorrect, $answerId, $questionId);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        // Commit transaction
        $conn->commit();

        // Success message
        $_SESSION['success_message'] = 'Quiz updated successfully!';
        closeDBConnection($conn);
        header("Location: edit_quiz.php?id=$quizId");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = 'Failed to update quiz. Please try again.';
        closeDBConnection($conn);
        header("Location: edit_quiz.php?id=$quizId");
        exit();
    }

} else {
    // If not POST request, redirect to quiz management page
    header('Location: quiz_management.php');
    exit();
}
?>
