<?php
session_start();

// Get success message if any
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

// Clear session message
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuizCraft - Welcome</title>
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

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .fade-in-up {
            animation: fadeInUp 1s ease-out forwards;
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        .gradient-bg {
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #4facfe);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        .text-shadow {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .delay-1 {
            animation-delay: 0.2s;
            opacity: 0;
        }

        .delay-2 {
            animation-delay: 0.4s;
            opacity: 0;
        }

        .delay-3 {
            animation-delay: 0.6s;
            opacity: 0;
        }

        .delay-4 {
            animation-delay: 0.8s;
            opacity: 0;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen overflow-x-hidden overflow-y-auto">

    <!-- Decorative Elements -->
    <div class="absolute top-10 left-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse"></div>
    <div class="absolute top-20 right-10 w-72 h-72 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse delay-1"></div>
    <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse delay-2"></div>

    <!-- Success Message Modal -->
    <?php if (!empty($successMessage)): ?>
    <div id="successModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black bg-opacity-50 fade-in-up">
        <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-md w-full transform scale-95 transition-all duration-300" id="modalContent">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-2">Account Created!</h3>
                <p class="text-lg text-gray-600 mb-6">
                    Your account has been created successfully. Welcome to QuizCraft!
                </p>
                <button onclick="closeModal()" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-lg px-8 py-3 rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    Get Started
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="relative z-10 text-center px-6 py-20">

        <!-- Quiz Icon/Logo -->
        <div class="mb-8 fade-in-up">
            <div class="inline-block float-animation">
                <svg class="w-24 h-24 mx-auto text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                </svg>
            </div>
        </div>

        <!-- Welcome Text -->
        <h1 class="text-6xl md:text-8xl font-bold text-white mb-6 fade-in-up delay-1 text-shadow">
            Welcome to
        </h1>

        <h2 class="text-7xl md:text-9xl font-extrabold text-white mb-8 fade-in-up delay-2 text-shadow">
            QuizCraft
        </h2>

        <!-- Subtitle -->
        <p class="text-xl md:text-2xl text-white mb-12 fade-in-up delay-3 text-shadow">
            Craft Your Knowledge, Master Your Skills
        </p>

        <!-- CTA Buttons -->
        <div class="fade-in-up delay-3 flex flex-col md:flex-row gap-6 justify-center items-center mb-16">
            <button id="createAccountBtn" class="bg-white text-purple-600 font-bold text-lg px-10 py-4 rounded-full shadow-2xl hover:shadow-3xl transform hover:scale-105 transition-all duration-300 hover:bg-purple-50 w-64">
                Create Account
            </button>
            <button id="loginAccountBtn" class="bg-transparent border-4 border-white text-white font-bold text-lg px-10 py-4 rounded-full shadow-2xl hover:shadow-3xl transform hover:scale-105 transition-all duration-300 hover:bg-white hover:text-purple-600 w-64">
                Login Account
            </button>
        </div>

        <!-- Description Section -->
        <div class="fade-in-up delay-4 max-w-4xl mx-auto mb-12">
            <div class="glass-effect rounded-3xl p-8 md:p-10 shadow-2xl">
                <h3 class="text-3xl md:text-4xl font-bold text-white mb-4 text-shadow">
                    The Smart Quiz Management System
                </h3>

                <p class="text-lg md:text-xl text-white mb-6 leading-relaxed">
                    Create, manage, and participate in quizzes with ease!<br>
                    Our platform allows teachers, students, and professionals to create quizzes, take tests, and track performance in real time.<br>
                    Whether you're testing knowledge, preparing for exams, or making learning fun, QuizCraft is the perfect solution.
                </p>

                <!-- Features List -->
                <div class="grid md:grid-cols-2 gap-4 text-left mb-6">
                    <div class="flex items-start space-x-3">
                        <span class="text-green-300 text-2xl">✔</span>
                        <span class="text-white text-lg">Create quizzes in minutes</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="text-green-300 text-2xl">✔</span>
                        <span class="text-white text-lg">Add multiple question types</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="text-green-300 text-2xl">✔</span>
                        <span class="text-white text-lg">Auto-grading and score reports</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="text-green-300 text-2xl">✔</span>
                        <span class="text-white text-lg">User-friendly dashboard</span>
                    </div>
                    <div class="flex items-start space-x-3 md:col-span-2 justify-center md:justify-start">
                        <span class="text-green-300 text-2xl">✔</span>
                        <span class="text-white text-lg">Accessible from any device</span>
                    </div>
                </div>

                <p class="text-xl md:text-2xl text-white font-semibold mt-6 text-shadow">
                    Start learning the smart way — Let's Begin!
                </p>
            </div>
        </div>

    </div>

    <script>
        // Close success modal
        function closeModal() {
            const modal = document.getElementById('successModal');
            if (modal) {
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.remove();
                }, 300);
            }
        }

        // Auto-scale modal on load
        window.addEventListener('load', function() {
            const modalContent = document.getElementById('modalContent');
            if (modalContent) {
                setTimeout(() => {
                    modalContent.style.transform = 'scale(1)';
                }, 100);
            }
        });

        // Create Account Button
        document.getElementById('createAccountBtn').addEventListener('click', function() {
            this.classList.add('scale-95');
            setTimeout(() => {
                this.classList.remove('scale-95');
                window.location.href = 'register.php';
            }, 150);
        });

        // Login Account Button
        document.getElementById('loginAccountBtn').addEventListener('click', function() {
            this.classList.add('scale-95');
            setTimeout(() => {
                this.classList.remove('scale-95');
                window.location.href = 'login.php';
            }, 150);
        });

        // Add cursor effect
        document.addEventListener('mousemove', function(e) {
            const cursor = document.createElement('div');
            cursor.className = 'absolute w-2 h-2 bg-white rounded-full pointer-events-none';
            cursor.style.left = e.pageX + 'px';
            cursor.style.top = e.pageY + 'px';
            cursor.style.opacity = '0.6';
            document.body.appendChild(cursor);

            setTimeout(() => {
                cursor.style.opacity = '0';
                cursor.style.transform = 'scale(2)';
                cursor.style.transition = 'all 0.5s ease-out';
            }, 10);

            setTimeout(() => {
                cursor.remove();
            }, 500);
        });

        // Prevent default context menu on right click for better UX
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    </script>

</body>
</html>
