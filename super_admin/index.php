<?php
include '../config.php';
session_start();

// Show errors for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Redirect if already logged in
if (isset($_SESSION['username'], $_SESSION['role'])) {
    header('Location: dashboard.php');
    exit;
}

// Static super admin credentials   
$SUPERADMIN_USER = 'superadmin';
$SUPERADMIN_PASS = 'powerteam@123!';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        // Check super admin credentials
        if ($username === $SUPERADMIN_USER && $password === $SUPERADMIN_PASS) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'superadmin';

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}

// Fetch dropdown data for region/chapter/powerteam
$regions = $conn->query("SELECT id, rvalue FROM region")->fetchAll(PDO::FETCH_ASSOC);
$chapters = $conn->query("SELECT id, svalue FROM chapters")->fetchAll(PDO::FETCH_ASSOC);
$powerteams = $conn->query("SELECT id, pvalue FROM powerteam")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Super Admin Login - Power Team</title>
    <link rel="icon" type="image/jpeg" href="../assets/logo.jpeg">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(197, 48, 48, 0.3); }
            50% { box-shadow: 0 0 40px rgba(197, 48, 48, 0.6); }
        }

        .animate-slideIn {
            animation: slideInRight 0.6s ease-out;
        }

        .animate-glow {
            animation: glow 2s ease-in-out infinite;
        }

        .input-group {
            position: relative;
        }

        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label {
            transform: translateY(-1.5rem) scale(0.85);
            color: #c53030;
        }

        .input-group label {
            transition: all 0.3s ease;
        }

        @media (max-width: 640px) {
            .input-group input:focus + label,
            .input-group input:not(:placeholder-shown) + label {
                transform: translateY(-1.3rem) scale(0.8);
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 via-red-900 to-gray-900 font-['Roboto'] flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Animated Background -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-20 left-20 w-96 h-96 bg-red-500 rounded-full mix-blend-multiply filter blur-3xl animate-pulse"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-red-700 rounded-full mix-blend-multiply filter blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="relative w-full max-w-md px-4 sm:px-0">
        <!-- Back Button -->
        <a href="../index.php" class="inline-flex items-center gap-2 text-gray-300 hover:text-white mb-4 sm:mb-6 transition-colors group text-sm sm:text-base">
            <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            <span>Back to Home</span>
        </a>

        <!-- Login Card -->
        <div class="bg-gray-800 rounded-2xl shadow-2xl overflow-hidden animate-slideIn border border-red-500/30 animate-glow">
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-700 to-red-800 p-6 sm:p-8 text-white text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white opacity-5 rounded-full -mr-20 -mt-20"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-white opacity-5 rounded-full -ml-16 -mb-16"></div>

                <div class="relative z-10">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4 shadow-lg">
                        <i class="fas fa-user-shield text-2xl sm:text-3xl text-red-700"></i>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold mb-1 sm:mb-2">Super Admin</h2>
                    <p class="text-red-100 text-xs sm:text-sm">System Control Panel</p>
                    <div class="mt-3 sm:mt-4 flex items-center justify-center gap-2 text-yellow-300 text-xs">
                        <i class="fas fa-shield-alt"></i>
                        <span>Restricted Access</span>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="p-6 sm:p-8 bg-gray-800">
                <?php if (!empty($error)) : ?>
                    <div class="mb-4 sm:mb-6 px-3 sm:px-4 py-2 sm:py-3 bg-red-900/50 border-l-4 border-red-500 text-red-200 rounded-r-lg flex items-start gap-2 sm:gap-3 animate-pulse text-sm sm:text-base">
                        <i class="fas fa-exclamation-triangle mt-0.5 flex-shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-5 sm:space-y-6">
                    <!-- Username Field -->
                    <div class="input-group">
                        <input
                            type="text"
                            id="username"
                            name="username"
                            required
                            autocomplete="username"
                            placeholder=" "
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base bg-gray-700 border-2 border-gray-600 text-white rounded-lg focus:outline-none focus:border-red-500 transition-colors peer" />
                        <label
                            for="username"
                            class="absolute left-3 sm:left-4 top-2.5 sm:top-3 text-sm sm:text-base text-gray-400 pointer-events-none">
                            <i class="fas fa-user-shield mr-1 sm:mr-2"></i>Username
                        </label>
                    </div>

                    <!-- Password Field -->
                    <div class="input-group">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder=" "
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base bg-gray-700 border-2 border-gray-600 text-white rounded-lg focus:outline-none focus:border-red-500 transition-colors peer" />
                        <label
                            for="password"
                            class="absolute left-3 sm:left-4 top-2.5 sm:top-3 text-sm sm:text-base text-gray-400 pointer-events-none">
                            <i class="fas fa-lock mr-1 sm:mr-2"></i>Password
                        </label>
                        <button
                            type="button"
                            onclick="togglePassword()"
                            class="absolute right-3 sm:right-4 top-2.5 sm:top-3 text-gray-400 hover:text-red-400 transition-colors">
                            <i class="fas fa-eye text-sm sm:text-base" id="toggleIcon"></i>
                        </button>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-red-700 to-red-800 hover:from-red-800 hover:to-red-900 text-white font-semibold py-2.5 sm:py-3 text-sm sm:text-base rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg flex items-center justify-center gap-2 border border-red-600">
                        <i class="fas fa-sign-in-alt text-sm sm:text-base"></i>
                        <span>Secure Login</span>
                    </button>
                </form>

                <!-- Security Notice -->
                <div class="mt-4 sm:mt-6 p-2.5 sm:p-3 bg-gray-700/50 rounded-lg border border-gray-600">
                    <div class="flex items-start gap-2 text-xs text-gray-400">
                        <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
                        <p>This is a restricted area. All access attempts are logged and monitored.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-400 text-xs sm:text-sm mt-4 sm:mt-6">
            Powered by <a href="https://www.mdqualityapps.com/" target="_blank" class="text-red-400 hover:text-red-300 font-medium">MD Qualityapps</a>
        </p>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
