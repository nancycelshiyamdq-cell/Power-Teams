

<aside id="sidebar">
    <h2>⚡ POWER TEAM</h2>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">🏠 Dashboard</a>
        <a href="create_admin.php" class="<?= basename($_SERVER['PHP_SELF']) === 'create_admin.php' ? 'active' : '' ?>">➕ Create Admin</a>
        <a href="create_member.php" class="<?= basename($_SERVER['PHP_SELF']) === 'create_member.php' ? 'active' : '' ?>">➕ Create Member</a>
        <a href="../logout.php">🚪 Logout</a>
    </nav>
</aside>


<script>
    const toggleBtn = document.querySelector('.menu-toggle');
    const sidebar = document.getElementById('sidebar');

    toggleBtn.addEventListener('click', () => {
        console.log('clic');
        sidebar.classList.toggle('active');
    });
</script>