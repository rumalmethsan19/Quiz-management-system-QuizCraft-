<?php
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Teacher') {
    header('Location: login.php');
    exit();
}

// Get user data
$fullName = $_SESSION['full_name'];
$email = $_SESSION['email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz Class - QuizCraft</title>
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

        .input-field {
            transition: all 0.3s ease;
        }

        .input-field:focus {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .delay-1 {
            animation-delay: 0.1s;
            opacity: 0;
        }

        .delay-2 {
            animation-delay: 0.2s;
            opacity: 0;
        }

        .delay-3 {
            animation-delay: 0.3s;
            opacity: 0;
        }

        .question-card {
            transition: all 0.3s ease;
        }

        .answer-option {
            transition: all 0.2s ease;
        }

        .answer-option:hover {
            background-color: #f3f4f6;
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
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Page Header -->
        <div class="mb-10 fade-in-up">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Create New Quiz Class</h1>
            <p class="text-gray-600 text-lg">Fill in the details to create a new quiz class</p>
        </div>

        <form id="createQuizForm" method="POST" action="create_quiz_process.php" class="space-y-8">

            <!-- Basic Information -->
            <div class="bg-white rounded-2xl shadow-lg p-8 fade-in-up delay-1">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Class Name -->
                    <div>
                        <label for="className" class="block text-gray-700 font-semibold mb-2">Class Name <span class="text-red-500">*</span></label>
                        <input type="text" id="className" name="className" required class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500" placeholder="e.g., Math Quiz 101">
                    </div>

                    <!-- Number of Questions -->
                    <div>
                        <label for="numQuestions" class="block text-gray-700 font-semibold mb-2">Number of Questions <span class="text-red-500">*</span></label>
                        <input type="number" id="numQuestions" name="numQuestions" min="1" max="50" required class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500" placeholder="e.g., 10">
                    </div>

                    <!-- Number of Answers per Question -->
                    <div>
                        <label for="numAnswers" class="block text-gray-700 font-semibold mb-2">Answers per Question <span class="text-red-500">*</span></label>
                        <input type="number" id="numAnswers" name="numAnswers" min="2" max="6" required class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500" placeholder="e.g., 4">
                    </div>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

                    <!-- Timer Duration -->
                    <div>
                        <label for="duration" class="block text-gray-700 font-semibold mb-2">Timer Duration (Minutes) <span class="text-red-500">*</span></label>
                        <input type="number" id="duration" name="duration" min="1" max="180" required class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500" placeholder="e.g., 30">
                        <p class="text-sm text-gray-500 mt-1">Set the time limit for this quiz</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-gray-700 font-semibold mb-2">Quiz Description</label>
                        <textarea id="description" name="description" rows="3" class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500" placeholder="Brief description of the quiz (optional)"></textarea>
                    </div>

                </div>

                <!-- Generate Questions Button -->
                <div class="mt-6">
                    <button type="button" id="generateBtn" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold px-8 py-3 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition">
                        Generate Question Fields
                    </button>
                </div>
            </div>

            <!-- Questions Container (Will be generated dynamically) -->
            <div id="questionsContainer" class="space-y-6"></div>

            <!-- Submit Button (Hidden until questions are generated) -->
            <div id="submitSection" class="hidden fade-in-up">
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <div class="flex justify-end gap-4">
                        <a href="quiz_management.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold px-8 py-4 rounded-xl transition">
                            Cancel
                        </a>
                        <button type="submit" class="bg-gradient-to-r from-green-600 to-green-700 text-white font-bold px-8 py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition">
                            Create Quiz Class
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </div>

    <script>
        document.getElementById('generateBtn').addEventListener('click', function() {
            const className = document.getElementById('className').value;
            const numQuestions = parseInt(document.getElementById('numQuestions').value);
            const numAnswers = parseInt(document.getElementById('numAnswers').value);

            // Validation
            if (!className || !numQuestions || !numAnswers) {
                alert('Please fill in all basic information fields');
                return;
            }

            if (numQuestions < 1 || numQuestions > 50) {
                alert('Number of questions must be between 1 and 50');
                return;
            }

            if (numAnswers < 2 || numAnswers > 6) {
                alert('Number of answers must be between 2 and 6');
                return;
            }

            // Generate questions
            generateQuestions(numQuestions, numAnswers);

            // Show submit section
            document.getElementById('submitSection').classList.remove('hidden');

            // Scroll to questions
            document.getElementById('questionsContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        function generateQuestions(numQuestions, numAnswers) {
            const container = document.getElementById('questionsContainer');
            container.innerHTML = ''; // Clear existing questions

            for (let i = 1; i <= numQuestions; i++) {
                const questionCard = document.createElement('div');
                questionCard.className = 'bg-white rounded-2xl shadow-lg p-8 question-card fade-in-up';

                questionCard.innerHTML = `
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full flex items-center justify-center text-white font-bold mr-3">${i}</span>
                        Question ${i}
                    </h3>

                    <!-- Question Text -->
                    <div class="mb-6">
                        <label for="question_${i}" class="block text-gray-700 font-semibold mb-2">Question Text <span class="text-red-500">*</span></label>
                        <textarea
                            id="question_${i}"
                            name="questions[${i}][text]"
                            required
                            rows="3"
                            class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500"
                            placeholder="Enter your question here..."
                        ></textarea>
                    </div>

                    <!-- Answer Options -->
                    <div class="space-y-3">
                        <label class="block text-gray-700 font-semibold mb-3">Answer Options (Check the correct answer) <span class="text-red-500">*</span></label>
                        ${generateAnswerOptions(i, numAnswers)}
                    </div>
                `;

                container.appendChild(questionCard);
            }
        }

        function generateAnswerOptions(questionNum, numAnswers) {
            let html = '';

            for (let j = 1; j <= numAnswers; j++) {
                const letter = String.fromCharCode(64 + j); // A, B, C, D...

                html += `
                    <div class="answer-option flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl">
                        <input
                            type="checkbox"
                            id="q${questionNum}_answer${j}"
                            name="questions[${questionNum}][answers][${j}][is_correct]"
                            value="1"
                            class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500"
                            onchange="handleCorrectAnswer(${questionNum}, ${j}, ${numAnswers})"
                        >
                        <label for="q${questionNum}_answer${j}" class="flex-1">
                            <span class="inline-block w-8 h-8 bg-purple-100 text-purple-600 rounded-full text-center font-bold mr-2">${letter}</span>
                            <input
                                type="text"
                                name="questions[${questionNum}][answers][${j}][text]"
                                required
                                class="input-field flex-1 px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 w-full mt-2"
                                placeholder="Enter answer option ${letter}"
                            >
                        </label>
                    </div>
                `;
            }

            return html;
        }

        function handleCorrectAnswer(questionNum, answerNum, totalAnswers) {
            // Uncheck other checkboxes for this question (only one correct answer)
            for (let i = 1; i <= totalAnswers; i++) {
                if (i !== answerNum) {
                    const checkbox = document.getElementById(`q${questionNum}_answer${i}`);
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                }
            }
        }

        // Form validation before submit
        document.getElementById('createQuizForm').addEventListener('submit', function(e) {
            const numQuestions = parseInt(document.getElementById('numQuestions').value);

            // Check if at least one answer is marked as correct for each question
            let allValid = true;

            for (let i = 1; i <= numQuestions; i++) {
                const checkboxes = document.querySelectorAll(`input[name^="questions[${i}][answers]"][type="checkbox"]`);
                const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;

                if (checkedCount === 0) {
                    alert(`Please select the correct answer for Question ${i}`);
                    allValid = false;
                    e.preventDefault();
                    return;
                }
            }
        });
    </script>

</body>
</html>
