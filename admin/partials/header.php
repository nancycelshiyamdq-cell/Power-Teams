<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

$name = $_SESSION['username'];
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
                <img src="../assets/logo_w.png" alt="Logo" class="h-10 w-10 object-contain" />
                <span class="text-xl font-bold hidden sm:block">POWER TEAM</span>
            </div>
        </div>

        <!-- Right: User Info -->
        <div class="flex items-center gap-3 bg-red-800 px-4 py-2 rounded-lg">
            <i class="fas fa-user-shield"></i>
            <span class="font-medium hidden sm:block"><?= ucfirst(htmlspecialchars($_SESSION['username'])); ?></span>
        </div>
    </div>
</header>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-white shadow-2xl transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 flex flex-col">
    <!-- Sidebar Header -->
    <div class="bg-gradient-to-br from-red-700 to-red-600 p-6 text-white flex-shrink-0">
        <div class="flex items-center gap-3 mb-2">
            <img src="../assets/logo_w.png" alt="Logo" class="h-12 w-12 object-contain" />
            <div>
                <h2 class="text-lg font-bold">POWER TEAM</h2>
                <p class="text-xs text-red-100">Admin Panel</p>
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

            <a href="create_member.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'create_member.php' ? 'active' : '' ?>">
                <i class="fas fa-user-plus w-5"></i>
                <span>Create Member</span>
            </a>

            <a href="master_manage.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'master_manage.php' ? 'active' : '' ?>">
                <i class="fas fa-cog w-5"></i>
                <span>Master Manage</span>
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
        white-space: nowrap;
        overflow: visible;
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
        min-width: 1.25rem;
        text-align: center;
        flex-shrink: 0;
    }
    .sidebar-link span {
        flex: 1;
        overflow: visible;
        text-overflow: clip;
        white-space: nowrap;
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
