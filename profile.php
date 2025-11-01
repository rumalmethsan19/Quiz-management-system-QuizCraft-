<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Include database configuration
require_once 'config/database.php';

// Get user data
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch complete user data from database
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();
closeDBConnection($conn);

// Get success/error messages
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Dashboard link based on role
$dashboardLink = ($role === 'Teacher') ? 'teacher_dashboard.php' : 'student_dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - QuizCraft</title>
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

        .profile-image-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Top Navigation Bar -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="<?php echo $dashboardLink; ?>" class="flex items-center space-x-3 hover:opacity-80 transition">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                    </svg>
                    <span class="text-white text-2xl font-bold">QuizCraft</span>
                    <span class="bg-white bg-opacity-20 text-white text-sm px-3 py-1 rounded-full"><?php echo $role; ?></span>
                </a>

                <!-- Back to Dashboard Button -->
                <a href="<?php echo $dashboardLink; ?>" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold px-6 py-2 rounded-full transition">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Page Header -->
        <div class="mb-10 fade-in-up">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">My Profile</h1>
            <p class="text-gray-600 text-lg">Manage your account information</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($successMessage)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg fade-in-up">
            <p class="font-bold">Success!</p>
            <p><?php echo $successMessage; ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg fade-in-up">
            <p class="font-bold">Error!</p>
            <p><?php echo $errorMessage; ?></p>
        </div>
        <?php endif; ?>

        <!-- Profile Form -->
        <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="space-y-6">

            <!-- Profile Image Section -->
            <div class="bg-white rounded-2xl shadow-lg p-8 fade-in-up delay-1">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Profile Picture</h2>
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <div class="flex flex-col items-center">
                        <?php if (!empty($userData['profile_image']) && file_exists('uploads/profile_images/' . $userData['profile_image'])): ?>
                            <img id="profileImagePreview" src="uploads/profile_images/<?php echo htmlspecialchars($userData['profile_image']); ?>" alt="Profile" class="profile-image-preview rounded-full border-4 border-purple-500 shadow-lg">
                        <?php else: ?>
                            <div id="profileImagePreview" class="profile-image-preview rounded-full border-4 border-gray-300 bg-gradient-to-r from-purple-600 to-pink-600 flex items-center justify-center text-white text-6xl font-bold shadow-lg">
                                <?php echo strtoupper(substr($userData['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="profileImage" name="profileImage" accept="image/*" class="hidden" onchange="previewImage(event)">
                        <label for="profileImage" class="mt-4 cursor-pointer bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold px-6 py-3 rounded-full hover:shadow-lg transition">
                            Change Photo
                        </label>
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-600">Upload a profile picture to personalize your account</p>
                        <p class="text-sm text-gray-500 mt-2">Accepted formats: JPG, PNG, GIF (Max 5MB)</p>
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="bg-white rounded-2xl shadow-lg p-8 fade-in-up delay-2">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Full Name -->
                    <div>
                        <label for="fullName" class="block text-gray-700 font-semibold mb-2">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($userData['full_name']); ?>" required class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-gray-700 font-semibold mb-2">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500">
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-gray-700 font-semibold mb-2">Username <span class="text-red-500">*</span></label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>" required class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500">
                    </div>

                    <!-- Work/School -->
                    <div>
                        <label for="workSchool" class="block text-gray-700 font-semibold mb-2">Work/School <span class="text-red-500">*</span></label>
                        <input type="text" id="workSchool" name="workSchool" value="<?php echo htmlspecialchars($userData['work_school']); ?>" required class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500">
                    </div>

                </div>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-2xl shadow-lg p-8 fade-in-up delay-3">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Change Password</h2>
                <p class="text-gray-600 mb-4">Leave blank if you don't want to change your password</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- New Password -->
                    <div>
                        <label for="newPassword" class="block text-gray-700 font-semibold mb-2">New Password</label>
                        <input type="password" id="newPassword" name="newPassword" class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500" placeholder="Enter new password">
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirmPassword" class="block text-gray-700 font-semibold mb-2">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500" placeholder="Confirm new password">
                    </div>

                </div>
                <p id="passwordError" class="text-red-500 text-sm mt-2 hidden">Passwords do not match</p>
            </div>

            <!-- Additional Information (Optional) -->
            <div class="bg-white rounded-2xl shadow-lg p-8 fade-in-up delay-3">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Additional Information (Optional)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Degree -->
                    <div>
                        <label for="degree" class="block text-gray-700 font-semibold mb-2">Degree/Qualification</label>
                        <input type="text" id="degree" name="degree" value="<?php echo htmlspecialchars($userData['degree'] ?? ''); ?>" class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500" placeholder="e.g., Bachelor's in Computer Science">
                    </div>

                    <!-- Birthdate -->
                    <div>
                        <label for="birthdate" class="block text-gray-700 font-semibold mb-2">Birth Date</label>
                        <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($userData['birthdate'] ?? ''); ?>" class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500">
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-gray-700 font-semibold mb-2">Gender</label>
                        <select id="gender" name="gender" class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($userData['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($userData['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($userData['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-4 fade-in-up delay-3">
                <a href="<?php echo $dashboardLink; ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold px-8 py-4 rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold px-8 py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition">
                    Save Changes
                </button>
            </div>

        </form>

    </div>

    <script>
        // Preview profile image
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profileImagePreview');
                    preview.innerHTML = '<img src="' + e.target.result + '" class="profile-image-preview rounded-full border-4 border-purple-500 shadow-lg">';
                }
                reader.readAsDataURL(file);
            }
        }

        // Password matching validation
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = this.value;
            const errorElement = document.getElementById('passwordError');

            if (confirmPassword && newPassword !== confirmPassword) {
                errorElement.classList.remove('hidden');
            } else {
                errorElement.classList.add('hidden');
            }
        });

        // Form submission validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                document.getElementById('passwordError').classList.remove('hidden');
                document.getElementById('confirmPassword').focus();
            }
        });
    </script>

</body>
</html>
