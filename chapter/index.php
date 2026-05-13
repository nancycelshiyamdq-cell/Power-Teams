<?php
include '../config.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['mobile']) && isset($_SESSION['role']) && $_SESSION['role'] === 'chapter') {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = $_POST['mobile'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($mobile && $password) {
        $stmt = $conn->prepare("SELECT * FROM members WHERE mobile = ? LIMIT 1");
        $stmt->execute([$mobile]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($member) {
            // ✅ Check subscription
            $today = date('Y-m-d');
            if (empty($member['sub_date']) || $member['sub_date'] < $today) {
                $error = "Your subscription has ended. Please contact admin.";
            }
            else if (empty($member['chapter_access']) || $member['chapter_access'] != 1) {
                $error = "You don't have chapter access.";
            } 
            else {
                // ✅ Password check
                if ($password === $member['password'] || password_verify($password, $member['password'])) {
    // ✅ Store IDs directly in session
    $_SESSION['mobile'] = $member['mobile'];
    $_SESSION['region'] = $member['region'];       // region_id
    $_SESSION['chapter'] = $member['chapter'];     // chapter_id
    $_SESSION['powerteam'] = $member['powerteam']; // powerteam_id
    $_SESSION['role'] = 'chapter';
     $_SESSION['id'] = $member['id'];

    // ✅ Fetch chapter name and store in session
    $chapterStmt = $conn->prepare("SELECT svalue FROM chapters WHERE id = ?");
    $chapterStmt->execute([$member['chapter']]);
    $_SESSION['chapter_name'] = $chapterStmt->fetchColumn();

    header('Location: dashboard.php');
    exit;
}

            }
        } else {
            $error = 'Invalid mobile number or password.';
        }
    } else {
        $error = 'Please enter both mobile number and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Power Team Captai Login - Power Team</title>
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

    .animate-slideIn {
        animation: slideInRight 0.6s ease-out;
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
<body class="min-h-screen bg-gradient-to-br from-gray-50 via-red-50 to-gray-100 font-['Roboto'] flex items-center justify-center p-4">

  <!-- Background Pattern -->
  <div class="fixed inset-0 opacity-5 pointer-events-none">
      <div class="absolute inset-0" style="background-image: radial-gradient(circle, #c53030 1px, transparent 1px); background-size: 30px 30px;"></div>
  </div>

  <div class="relative w-full max-w-md px-4 sm:px-0">
      <!-- Back Button -->
      <a href="../index.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-red-600 mb-4 sm:mb-6 transition-colors group text-sm sm:text-base">
          <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
          <span>Back to Home</span>
      </a>

      <!-- Login Card -->
      <div class="bg-white rounded-2xl shadow-2xl overflow-hidden animate-slideIn">
          <!-- Header -->
          <div class="bg-gradient-to-r from-red-500 to-red-600 p-6 sm:p-8 text-white text-center relative overflow-hidden">
              <div class="absolute top-0 right-0 w-40 h-40 bg-white opacity-10 rounded-full -mr-20 -mt-20"></div>
              <div class="absolute bottom-0 left-0 w-32 h-32 bg-white opacity-10 rounded-full -ml-16 -mb-16"></div>

              <div class="relative z-10">
                  <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4 shadow-lg">
                      <i class="fas fa-building text-2xl sm:text-3xl text-red-500"></i>
                  </div>
                  <h2 class="text-xl sm:text-2xl font-bold mb-1 sm:mb-2">Power Team Captai Login</h2>
                  <p class="text-red-100 text-xs sm:text-sm">Chapter Management Portal</p>
              </div>
          </div>

          <!-- Form -->
          <div class="p-6 sm:p-8">
              <?php if (!empty($error)) : ?>
                  <div class="mb-4 sm:mb-6 px-3 sm:px-4 py-2 sm:py-3 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg flex items-start gap-2 sm:gap-3 animate-pulse text-sm sm:text-base">
                      <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
                      <span><?= htmlspecialchars($error) ?></span>
                  </div>
              <?php endif; ?>

              <form method="POST" action="" class="space-y-5 sm:space-y-6">
                  <!-- Mobile Field -->
                  <div class="input-group">
                      <input
                          type="tel"
                          id="mobile"
                          name="mobile"
                          required
                          autocomplete="tel"
                          placeholder=" "
                          pattern="[0-9]{10}"
                          class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border-2 border-gray-300 rounded-lg focus:outline-none focus:border-red-500 transition-colors peer" />
                      <label
                          for="mobile"
                          class="absolute left-3 sm:left-4 top-2.5 sm:top-3 text-sm sm:text-base text-gray-500 pointer-events-none">
                          <i class="fas fa-mobile-alt mr-1 sm:mr-2"></i>Mobile Number
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
                          class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border-2 border-gray-300 rounded-lg focus:outline-none focus:border-red-500 transition-colors peer" />
                      <label
                          for="password"
                          class="absolute left-3 sm:left-4 top-2.5 sm:top-3 text-sm sm:text-base text-gray-500 pointer-events-none">
                          <i class="fas fa-lock mr-1 sm:mr-2"></i>Password
                      </label>
                      <button
                          type="button"
                          onclick="togglePassword()"
                          class="absolute right-3 sm:right-4 top-2.5 sm:top-3 text-gray-500 hover:text-red-600 transition-colors">
                          <i class="fas fa-eye text-sm sm:text-base" id="toggleIcon"></i>
                      </button>
                  </div>

                  <!-- Submit Button -->
                  <button
                      type="submit"
                      class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-2.5 sm:py-3 text-sm sm:text-base rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg flex items-center justify-center gap-2">
                      <span>Sign In</span>
                      <i class="fas fa-arrow-right text-sm sm:text-base"></i>
                  </button>
              </form>
          </div>
      </div>

      <!-- Footer -->
      <p class="text-center text-gray-600 text-xs sm:text-sm mt-4 sm:mt-6">
          Powered by <a href="https://www.mdqualityapps.com/" target="_blank" class="text-red-600 hover:text-red-700 font-medium">MD Qualityapps</a>
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
