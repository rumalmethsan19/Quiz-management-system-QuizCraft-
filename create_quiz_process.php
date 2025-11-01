<?php
/**
 * Create Quiz Process Handler
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

    // Get and sanitize basic information
    $className = isset($_POST['className']) ? trim($_POST['className']) : '';
    $numQuestions = isset($_POST['numQuestions']) ? intval($_POST['numQuestions']) : 0;
    $numAnswers = isset($_POST['numAnswers']) ? intval($_POST['numAnswers']) : 0;
    $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 30;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $questions = isset($_POST['questions']) ? $_POST['questions'] : [];

    // Validation
    $errors = [];

    if (empty($className)) {
        $errors[] = 'Class name is required.';
    }

    if ($numQuestions < 1 || $numQuestions > 50) {
        $errors[] = 'Number of questions must be between 1 and 50.';
    }

    if ($numAnswers < 2 || $numAnswers > 6) {
        $errors[] = 'Number of answers must be between 2 and 6.';
    }

    if (count($questions) !== $numQuestions) {
        $errors[] = 'Number of questions does not match.';
    }

    // Validate each question
    foreach ($questions as $qNum => $question) {
        if (empty($question['text'])) {
            $errors[] = "Question {$qNum} text is required.";
        }

        if (!isset($question['answers']) || count($question['answers']) !== $numAnswers) {
            $errors[] = "Question {$qNum} must have {$numAnswers} answers.";
        }

        // Check if at least one answer is marked as correct
        $hasCorrectAnswer = false;
        if (isset($question['answers'])) {
            foreach ($question['answers'] as $answer) {
                if (isset($answer['is_correct']) && $answer['is_correct'] == 1) {
                    $hasCorrectAnswer = true;
                    break;
                }
            }
        }

        if (!$hasCorrectAnswer) {
            $errors[] = "Question {$qNum} must have at least one correct answer.";
        }
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
        header('Location: create_quiz.php');
        exit();
    }

    // Generate unique 6-digit class ID
    $classId = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);

    // Get database connection
    $conn = getDBConnection();

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert quiz
        $stmt = $conn->prepare("INSERT INTO quizzes (teacher_id, title, class_id, description, total_questions, duration, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $isActive = 1;
        $stmt->bind_param("isssiis", $teacherId, $className, $classId, $description, $numQuestions, $duration, $isActive);
        $stmt->execute();
        $quizId = $conn->insert_id;
        $stmt->close();

        // Insert questions and answers
        foreach ($questions as $qNum => $question) {
            // Insert question
            $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_number, question_text) VALUES (?, ?, ?)");
            $questionText = trim($question['text']);
            $stmt->bind_param("iis", $quizId, $qNum, $questionText);
            $stmt->execute();
            $questionId = $conn->insert_id;
            $stmt->close();

            // Insert answers
            if (isset($question['answers'])) {
                foreach ($question['answers'] as $answerNum => $answer) {
                    if (!empty($answer['text'])) {
                        $answerText = trim($answer['text']);
                        $isCorrect = isset($answer['is_correct']) && $answer['is_correct'] == 1 ? 1 : 0;

                        $stmt = $conn->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
                        $stmt->bind_param("isi", $questionId, $answerText, $isCorrect);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
        }

        // Commit transaction
        $conn->commit();

        // Success message with class ID
        $_SESSION['class_created'] = true;
        $_SESSION['new_class_id'] = $classId;
        $_SESSION['class_name'] = $className;
        closeDBConnection($conn);
        header('Location: quiz_management.php');
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = 'Failed to create quiz class. Please try again.';
        closeDBConnection($conn);
        header('Location: create_quiz.php');
        exit();
    }

} else {
    // If not POST request, redirect to create quiz page
    header('Location: create_quiz.php');
    exit();
}
?>
