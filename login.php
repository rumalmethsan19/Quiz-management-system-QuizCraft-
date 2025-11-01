<?php
session_start();

// Get error/success messages if any
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];

// Clear session messages
unset($_SESSION['error_message']);
unset($_SESSION['success_message']);
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QuizCraft</title>
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
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .gradient-bg {
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #4facfe);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
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

        .input-field {
            transition: all 0.3s ease;
        }

        .input-field:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .role-option {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .role-option:hover {
            transform: translateY(-3px);
        }

        .role-option.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen overflow-x-hidden overflow-y-auto py-10">

    <!-- Decorative Elements -->
    <div class="absolute top-10 left-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse"></div>
    <div class="absolute bottom-10 right-10 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse"></div>

    <!-- Main Content -->
    <div class="relative z-10 max-w-lg mx-auto px-6">

        <!-- Header -->
        <div class="text-center mb-8 fade-in-up">
            <a href="index.php" class="inline-block mb-6 text-white hover:scale-110 transition-transform duration-300">
                <svg class="w-16 h-16 mx-auto" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                </svg>
            </a>
            <h1 class="text-5xl md:text-6xl font-bold text-white mb-3">Welcome Back</h1>
            <p class="text-xl text-white">Login to continue your learning journey</p>
        </div>

        <!-- Login Form -->
        <div class="glass-effect rounded-3xl p-8 md:p-10 shadow-2xl fade-in-up delay-1">

            <?php if (!empty($successMessage)): ?>
            <!-- Success Alert -->
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg fade-in-up" role="alert">
                <div class="flex items-start">
                    <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-bold">Success</p>
                        <p class="text-sm"><?php echo $successMessage; ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
            <!-- Error Alert -->
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg fade-in-up" role="alert">
                <div class="flex items-start">
                    <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-bold">Login Error</p>
                        <p class="text-sm"><?php echo $errorMessage; ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="login_process.php" class="space-y-6">

                <!-- Role Selection -->
                <div class="fade-in-up delay-2">
                    <label class="block text-gray-700 text-lg font-semibold mb-3">
                        Select Your Role <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="role-option border-4 border-gray-300 rounded-xl p-6 text-center" onclick="selectRole('Student')">
                            <svg class="w-12 h-12 mx-auto mb-2 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/>
                            </svg>
                            <p class="font-bold text-lg">Student</p>
                        </div>
                        <div class="role-option border-4 border-gray-300 rounded-xl p-6 text-center" onclick="selectRole('Teacher')">
                            <svg class="w-12 h-12 mx-auto mb-2 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                            </svg>
                            <p class="font-bold text-lg">Teacher</p>
                        </div>
                    </div>
                    <input type="hidden" id="role" name="role" required>
                    <p id="roleError" class="text-red-500 text-sm mt-2 hidden">Please select a role</p>
                </div>

                <!-- Username -->
                <div class="fade-in-up delay-2">
                    <label for="username" class="block text-gray-700 text-lg font-semibold mb-2">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            id="username"
                            name="username"
                            required
                            value="<?php echo isset($formData['username']) ? htmlspecialchars($formData['username']) : ''; ?>"
                            class="input-field w-full px-5 py-4 pl-12 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 text-lg"
                            placeholder="Enter your username"
                        >
                        <svg class="w-6 h-6 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>

                <!-- Password -->
                <div class="fade-in-up delay-3">
                    <label for="password" class="block text-gray-700 text-lg font-semibold mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="input-field w-full px-5 py-4 pl-12 pr-12 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 text-lg"
                            placeholder="Enter your password"
                        >
                        <svg class="w-6 h-6 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-purple-600">
                            <svg id="eyeIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="fade-in-up delay-3 pt-4">
                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-xl py-4 rounded-xl shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-300"
                    >
                        Login
                    </button>
                </div>

                <!-- Register Link -->
                <div class="text-center fade-in-up delay-3">
                    <p class="text-gray-600 text-lg">
                        Don't have an account?
                        <a href="register.php" class="text-purple-600 font-semibold hover:text-purple-800 hover:underline">
                            Create Account
                        </a>
                    </p>
                </div>

            </form>
        </div>

    </div>

    <script>
        let selectedRole = null;

        // Role selection function
        function selectRole(role) {
            selectedRole = role;
            document.getElementById('role').value = role;

            // Remove selected class from all options
            const allOptions = document.querySelectorAll('.role-option');
            allOptions.forEach(option => {
                option.classList.remove('selected');
            });

            // Add selected class to clicked option
            event.currentTarget.classList.add('selected');

            // Hide error message
            document.getElementById('roleError').classList.add('hidden');
        }

        // Toggle password visibility
        function togglePassword() {
            const field = document.getElementById('password');
            if (field.type === 'password') {
                field.type = 'text';
            } else {
                field.type = 'password';
            }
        }

        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();

            let isValid = true;

            // Check if role is selected
            if (!selectedRole) {
                document.getElementById('roleError').classList.remove('hidden');
                isValid = false;
            }

            // If all validations pass, submit the form
            if (isValid) {
                // Show loading animation
                const button = event.target.querySelector('button[type="submit"]');
                button.innerHTML = '<svg class="animate-spin h-6 w-6 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                button.disabled = true;

                // Submit the form
                this.submit();
            }
        });
    </script>

</body>
</html>
