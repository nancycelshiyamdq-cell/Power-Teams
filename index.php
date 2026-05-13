<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Power Team - BNI Meeting Tracker</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }

    .animate-fadeInUp {
      animation: fadeInUp 0.6s ease-out forwards;
    }

    .animate-float {
      animation: float 3s ease-in-out infinite;
    }

    .card-hover {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card-hover:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 20px 40px rgba(197, 48, 48, 0.3);
    }

    .gradient-bg {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
  </style>
</head>

<body class="gradient-bg min-h-screen font-['Roboto'] overflow-x-hidden">

  <!-- Animated Background Shapes -->
  <div class="fixed inset-0 overflow-hidden pointer-events-none opacity-10">
    <div class="absolute top-20 left-10 w-72 h-72 bg-red-500 rounded-full mix-blend-multiply filter blur-3xl animate-float"></div>
    <div class="absolute top-40 right-10 w-72 h-72 bg-red-700 rounded-full mix-blend-multiply filter blur-3xl animate-float" style="animation-delay: 2s;"></div>
    <div class="absolute -bottom-8 left-1/2 w-72 h-72 bg-red-600 rounded-full mix-blend-multiply filter blur-3xl animate-float" style="animation-delay: 4s;"></div>
  </div>

  <div class="relative min-h-screen flex flex-col lg:flex-row">

    <!-- Left Panel - Branding -->
    <div class="w-full lg:w-2/5 bg-gradient-to-br from-red-700 via-red-600 to-red-500 text-white flex flex-col justify-center items-center p-6 sm:p-8 lg:p-12 relative overflow-hidden min-h-[50vh] lg:min-h-screen">
      <!-- Decorative Elements -->
      <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-32 -mt-32"></div>
      <div class="absolute bottom-0 left-0 w-96 h-96 bg-white opacity-5 rounded-full -ml-48 -mb-48"></div>

      <div class="relative z-10 text-center animate-fadeInUp w-full">
        <div class="mb-6 sm:mb-8 animate-float">
          <img src="assets/logo.png" alt="BNI Logo" class="w-28 h-28 sm:w-32 sm:h-32 lg:w-40 lg:h-40 mx-auto rounded-full shadow-2xl border-4 border-white/30" />
        </div>

        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-3 sm:mb-4 drop-shadow-lg">Power Team</h1>
        <div class="h-1 w-20 sm:w-24 bg-white/50 mx-auto mb-4 sm:mb-6 rounded-full"></div>
        <p class="text-lg sm:text-xl lg:text-2xl font-medium mb-6 sm:mb-8 text-red-100">Meeting Tracker</p>

        <div class="space-y-3 sm:space-y-4 text-left max-w-md mx-auto px-4 sm:px-0">
          <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-lg p-2.5 sm:p-3">
            <i class="fas fa-check-circle text-xl sm:text-2xl flex-shrink-0"></i>
            <span class="text-sm sm:text-base">Track Attendance & Meetings</span>
          </div>
          <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-lg p-2.5 sm:p-3">
            <i class="fas fa-users text-xl sm:text-2xl flex-shrink-0"></i>
            <span class="text-sm sm:text-base">Manage Team Members</span>
          </div>
          <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-lg p-2.5 sm:p-3">
            <i class="fas fa-handshake text-xl sm:text-2xl flex-shrink-0"></i>
            <span class="text-sm sm:text-base">Monitor Referrals & Asks</span>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="relative lg:absolute bottom-4 left-0 right-0 text-center text-xs sm:text-sm text-white/70 mt-6 lg:mt-0 px-4">
        Powered by <a href="https://www.mdqualityapps.com/" target="_blank" class="text-white hover:text-red-200 font-medium transition-colors">MDQuality Apps Solution</a>
      </div>
    </div>

    <!-- Right Panel - Login Options -->
    <div class="w-full lg:w-3/5 flex items-center justify-center p-4 sm:p-6 lg:p-12 min-h-[50vh] lg:min-h-screen">
      <div class="w-full max-w-2xl">
        <div class="text-center mb-8 sm:mb-10 lg:mb-12 animate-fadeInUp px-4">
          <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800 mb-2 sm:mb-3">Welcome Back!</h2>
          <p class="text-gray-600 text-base sm:text-lg">Select your role to continue</p>
        </div>

        <div class="grid gap-4 sm:gap-5 lg:gap-6 grid-cols-1 sm:grid-cols-2 px-4 sm:px-0">
          <!-- Super Admin Card -->
       

          <!-- Admin Card -->
          <a href="./admin/index.php" class="card-hover group bg-white rounded-2xl p-6 sm:p-7 lg:p-8 shadow-lg border-2 border-transparent hover:border-red-500 animate-fadeInUp" style="animation-delay: 0.2s;">
            <div class="flex flex-col items-center text-center">
              <div class="w-16 h-16 sm:w-18 sm:h-18 lg:w-20 lg:h-20 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 transition-transform shadow-lg">
                <i class="fas fa-user-tie text-2xl sm:text-3xl text-white"></i>
              </div>
              <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-1 sm:mb-2">Coordinator</h3>
              <p class="text-gray-600 text-xs sm:text-sm">Manage team operations</p>
            </div>
          </a>

          <!-- Chapter Lead Card -->
          <a href="./chapter/index.php" class="card-hover group bg-white rounded-2xl p-6 sm:p-7 lg:p-8 shadow-lg border-2 border-transparent hover:border-red-500 animate-fadeInUp" style="animation-delay: 0.3s;">
            <div class="flex flex-col items-center text-center">
              <div class="w-16 h-16 sm:w-18 sm:h-18 lg:w-20 lg:h-20 bg-gradient-to-br from-red-400 to-red-500 rounded-2xl flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 transition-transform shadow-lg">
                <i class="fas fa-building text-2xl sm:text-3xl text-white"></i>
              </div>
              <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-1 sm:mb-2">Power Team Captain</h3>
              <p class="text-gray-600 text-xs sm:text-sm">Lead your power team</p>
            </div>
          </a>

          <!-- Member Card -->
          <a href="./member/index.php" class="card-hover group bg-white rounded-2xl p-6 sm:p-7 lg:p-8 shadow-lg border-2 border-transparent hover:border-red-500 animate-fadeInUp" style="animation-delay: 0.4s;">
            <div class="flex flex-col items-center text-center">
              <div class="w-16 h-16 sm:w-18 sm:h-18 lg:w-20 lg:h-20 bg-gradient-to-br from-red-300 to-red-400 rounded-2xl flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 transition-transform shadow-lg">
                <i class="fas fa-user text-2xl sm:text-3xl text-white"></i>
              </div>
              <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-1 sm:mb-2">Member</h3>
              <p class="text-gray-600 text-xs sm:text-sm">Access your portal</p>
            </div>
          </a>
        </div>
      </div>
    </div>

  </div>

</body>

</html>
