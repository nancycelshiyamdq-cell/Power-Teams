<!-- Navbar -->
<?php
session_start();
include '../auth.php';
validate('chapter');

// Get session values
$region = $_SESSION['region'];
$chapter = $_SESSION['chapter'];
$powerteam = $_SESSION['powerteam'];

// Fetch chapter name and logo
$chapter_name = '';
$logo_path = '';

if (!empty($chapter)) {
    $stmt = $conn->prepare("SELECT svalue, image FROM chapters WHERE id = :chapter");
    $stmt->execute([':chapter' => $chapter]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $chapter_name = $row['svalue'] ?? '';
    $logo_path = $row['image'] ?? null;
}

// Get the base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$baseUrl  = $protocol . $_SERVER['HTTP_HOST'] . '/powerteam/';

// Determine which image to use
$chapterImage = !empty($logo_path) ? "uploads/chapters/" . $logo_path : "assets/logo_w.png";
?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link rel="stylesheet" href="./css/sidebar.css" />
<script src="https://cdn.tailwindcss.com"></script>

<!-- Top Header Bar -->
<header class="fixed top-0 left-0 right-0 bg-gradient-to-r from-red-700 to-red-600 shadow-lg z-50">
    <div class="flex items-center justify-between px-4 py-3 text-white">
        <!-- Left: Logo + Toggle -->
        <div class="flex items-center gap-4">
            <button id="sidebar-toggle" class="text-2xl hover:bg-red-800 p-2 rounded-lg transition-all duration-200" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div class="flex items-center gap-3">
                <img src="<?= $baseUrl . htmlspecialchars($chapterImage) ?>" alt="Logo" class="h-10 w-10 object-contain rounded-full" />
                <span class="text-xl font-bold hidden sm:block">POWER TEAM</span>
            </div>
        </div>

        <!-- Right: User Info -->
        <div class="flex items-center gap-3 bg-red-800 px-4 py-2 rounded-lg">
            <i class="fas fa-user-shield"></i>
            <span class="font-medium hidden sm:block"><?= htmlspecialchars($chapter_name); ?></span>
        </div>
    </div>
</header>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-white shadow-2xl transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 flex flex-col">
    <!-- Sidebar Header -->
    <div class="bg-gradient-to-br from-red-700 to-red-600 p-6 text-white flex-shrink-0">
        <div class="flex items-center gap-3 mb-2">
            <img src="<?= $baseUrl . htmlspecialchars($chapterImage) ?>" alt="Logo" class="h-12 w-12 object-contain rounded-full" />
            <div>
                <h2 class="text-lg font-bold">POWER TEAM</h2>
                <p class="text-xs text-red-100">Chapter Management</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-4" style="max-height: calc(100vh - 120px);">
        <div class="px-3 space-y-1">
            <a href="dashboard.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-home w-5"></i>
                <span>Dashboard</span>
            </a>

            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-4">Attendance</div>

            <a href="attendance.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'attendance.php' ? 'active' : '' ?>">
                <i class="fas fa-check-circle w-5"></i>
                <span>Mark Attendance</span>
            </a>

            <a href="attendance_list.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'attendance_list.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar w-5"></i>
                <span>Attendance Data</span>
            </a>

            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-4">Members</div>

            <a href="members.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'members.php' ? 'active' : '' ?>">
                <i class="fas fa-users w-5"></i>
                <span>Add Members</span>
            </a>

            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-4">Referrals</div>

            <a href="referral_entry.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'referral_entry.php' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle w-5"></i>
                <span>Add Ask/Gives</span>
            </a>

            <a href="referral_list.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'referral_list.php' ? 'active' : '' ?>">
                <i class="fas fa-list-alt w-5"></i>
                <span>Ask/Gives List</span>
            </a>

            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-4">Power Dates</div>

            <a href="power_date_add.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'power_date_add.php' ? 'active' : '' ?>">
                <i class="fas fa-calendar-plus w-5"></i>
                <span>Add Power Date</span>
            </a>

            <a href="power_date_status.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'power_date_status.php' ? 'active' : '' ?>">
                <i class="fas fa-calendar-check w-5"></i>
                <span>Power Date Status</span>
            </a>

            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-4">Planning</div>

            <a href="meeting_planner.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'meeting_planner.php' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list w-5"></i>
                <span>Meeting Planner</span>
            </a>
        </div>

        <!-- Logout at Bottom -->
        <div class="px-3 mt-8 pt-4 border-t border-gray-200">
            <a href="../logout.php" class="sidebar-link text-red-600 hover:bg-red-50">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</aside>

<!-- Overlay for mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

<!-- Sidebar Styles -->
<style>
    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        color: #374151;
        font-weight: 500;
        transition: all 0.2s;
        text-decoration: none;
    }
    .sidebar-link:hover {
        background-color: #fef2f2;
        color: #b91c1c;
    }
    .sidebar-link.active {
        background-color: #b91c1c;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .sidebar-link.active:hover {
        background-color: #991b1b;
        color: white;
    }
    .sidebar-link i {
        font-size: 1.125rem;
        width: 1.25rem;
        text-align: center;
    }
</style>

<!-- Sidebar Toggle Script -->
<script>
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    function toggleSidebar() {
        sidebar.classList.toggle('-translate-x-full');
        sidebarOverlay.classList.toggle('hidden');
    }

    sidebarToggle.addEventListener('click', toggleSidebar);
    sidebarOverlay.addEventListener('click', toggleSidebar);

    // Close sidebar on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) {
            toggleSidebar();
        }
    });
</script>

<!-- Main Content Wrapper - Add this after header/sidebar -->
<div class="main-content">
