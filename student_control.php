<?php
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Teacher') {
    header('Location: login.php');
    exit();
}

// Include database configuration
require_once 'config/database.php';

// Get all students from database
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, full_name, email, username, work_school, created_at, last_login, profile_image FROM users WHERE role = 'Student' ORDER BY full_name ASC");
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
closeDBConnection($conn);

// Get user data
$fullName = $_SESSION['full_name'];
$email = $_SESSION['email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Control - QuizCraft</title>
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

        .student-card {
            transition: all 0.3s ease;
        }

        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .delay-1 {
            animation-delay: 0.1s;
            opacity: 0;
        }

        .delay-2 {
            animation-delay: 0.2s;
            opacity: 0;
        }

        .search-input {
            transition: all 0.3s ease;
        }

        .search-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
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

                <!-- Back to Dashboard Button -->
                <a href="teacher_dashboard.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold px-6 py-2 rounded-full transition">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Page Header -->
        <div class="mb-10 fade-in-up">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Student Control</h1>
            <p class="text-gray-600 text-lg">View and manage all registered students</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 fade-in-up delay-1">
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Students</p>
                        <p class="text-4xl font-bold text-blue-600"><?php echo count($students); ?></p>
                    </div>
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Active Students</p>
                        <p class="text-4xl font-bold text-green-600"><?php echo count($students); ?></p>
                    </div>
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">This Month</p>
                        <p class="text-4xl font-bold text-purple-600">+<?php echo count(array_filter($students, function($s) { return strtotime($s['created_at']) > strtotime('-30 days'); })); ?></p>
                    </div>
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Box -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-10 fade-in-up delay-2">
            <div class="flex items-center space-x-4">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="searchInput" placeholder="Search students by name, email, or school..." class="search-input flex-1 px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 text-lg">
            </div>
        </div>

        <!-- Students List -->
        <div class="bg-white rounded-2xl shadow-lg p-8 fade-in-up delay-2">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">All Students</h2>

            <?php if (empty($students)): ?>
                <div class="text-center py-12">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                    </svg>
                    <p class="text-xl text-gray-500">No students registered yet</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="studentsList">
                    <?php foreach ($students as $student): ?>
                        <a href="view_student.php?id=<?php echo $student['id']; ?>" class="student-card bg-gray-50 rounded-xl p-6 hover:bg-gray-100 transition cursor-pointer" data-name="<?php echo strtolower($student['full_name']); ?>" data-email="<?php echo strtolower($student['email']); ?>" data-school="<?php echo strtolower($student['work_school']); ?>">
                            <div class="flex items-start space-x-4">
                                <!-- Profile Image -->
                                <div class="flex-shrink-0">
                                    <?php if (!empty($student['profile_image']) && file_exists('uploads/profile_images/' . $student['profile_image'])): ?>
                                        <img src="uploads/profile_images/<?php echo htmlspecialchars($student['profile_image']); ?>" alt="Profile" class="w-16 h-16 rounded-full object-cover border-2 border-purple-500">
                                    <?php else: ?>
                                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 flex items-center justify-center text-white text-2xl font-bold">
                                            <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Student Info -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-bold text-gray-800 truncate"><?php echo htmlspecialchars($student['full_name']); ?></h3>
                                    <p class="text-sm text-gray-600 truncate"><?php echo htmlspecialchars($student['email']); ?></p>
                                    <p class="text-sm text-gray-500 truncate"><?php echo htmlspecialchars($student['work_school']); ?></p>
                                    <div class="mt-2 flex items-center space-x-2">
                                        <span class="text-xs bg-green-100 text-green-600 px-2 py-1 rounded-full">Active</span>
                                        <span class="text-xs text-gray-500">@<?php echo htmlspecialchars($student['username']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats -->
                            <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between text-sm">
                                <div class="text-center">
                                    <p class="text-gray-500">Joined</p>
                                    <p class="font-semibold text-gray-800"><?php echo date('M d, Y', strtotime($student['created_at'])); ?></p>
                                </div>
                                <div class="text-center">
                                    <p class="text-gray-500">Last Login</p>
                                    <p class="font-semibold text-gray-800"><?php echo $student['last_login'] ? date('M d', strtotime($student['last_login'])) : 'Never'; ?></p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <p id="noResults" class="text-center text-gray-500 mt-8 hidden">No students found matching your search.</p>
            <?php endif; ?>
        </div>

    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const studentCards = document.querySelectorAll('.student-card');
            let visibleCount = 0;

            studentCards.forEach(card => {
                const name = card.getAttribute('data-name');
                const email = card.getAttribute('data-email');
                const school = card.getAttribute('data-school');

                if (name.includes(searchTerm) || email.includes(searchTerm) || school.includes(searchTerm)) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            // Show/hide no results message
            const noResults = document.getElementById('noResults');
            if (visibleCount === 0 && searchTerm !== '') {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        });
    </script>

</body>
</html>
