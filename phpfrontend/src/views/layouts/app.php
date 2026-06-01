<?php /** @var string $content */ /** @var string $public_base_url */ /** @var string $phpfrontend_base_url */ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Clock-It') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Use public_base_url for assets within the public folder -->
    <link rel="stylesheet" href="<?= $public_base_url ?>/assets/css/admin-style.css">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ sidebarOpen: false }">
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'open': sidebarOpen }">
            <div class="sidebar-logo">
                <h2>Clock-It</h2>
                <p>Attendance Management</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="<?= $public_base_url ?>/admin/dashboard" class="active">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Employees
                </a>
                <a href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Reports
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">AD</div>
                    <div class="user-details">
                        <div class="user-name">Admin User</div>
                        <div class="user-email">admin@clockit.local</div>
                    </div>
                </div>
                <button class="logout-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Log Out
                </button>
            </div>
        </aside>

        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" :class="{ 'show': sidebarOpen }" @click="sidebarOpen = false"></div>

        <!-- Main Content Wrapper -->
        <main class="main-content">
            <!-- Top Navigation -->
            <nav class="top-nav">
                <button class="menu-btn" @click="sidebarOpen = true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="admin-badge">Administrator</div>
            </nav>

            <!-- Page View Content -->
            <div class="page-content">
                <?= $content ?>
            </div>
        </main>
    </div>
</body>
</html>