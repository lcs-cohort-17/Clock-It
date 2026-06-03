<?php
/**
 * Admin Dashboard Page
 * Main dashboard with overview and stats
 */
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

// Verify admin access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . route_url('/login'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Clock-It</title>
    <link href="<?= asset_url('vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_url('vendor/bootstrap-icons/font/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>
</head>
<body x-data="{ 
    sidebarOpen: true, 
    stats: {
        totalEmployees: 45,
        presentToday: 38,
        absentToday: 7,
        totalScans: 342
    },
    recentEvents: []
}" @init="window.themeManager.initTheme()">
    
    <div style="display: flex;">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div style="flex: 1; display: flex; flex-direction: column;">
            <!-- Header -->
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <!-- Dashboard Content -->
            <main class="dashboard-section" style="padding: 2rem;">
                <div class="container-fluid">
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <p class="text-muted small mb-2">Total Employees</p>
                                    <h3 class="card-title stat-value stat-value-primary" x-text="stats.totalEmployees"></h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <p class="text-muted small mb-2">Present Today</p>
                                    <h3 class="card-title stat-value stat-value-success" x-text="stats.presentToday"></h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <p class="text-muted small mb-2">Absent Today</p>
                                    <h3 class="card-title stat-value stat-value-danger" x-text="stats.absentToday"></h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <p class="text-muted small mb-2">Total Scans</p>
                                    <h3 class="card-title stat-value stat-value-info" x-text="stats.totalScans"></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom">
                            <h5 class="mb-0">Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Loading recent events...</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function logoutUser() {
            if (confirm('Are you sure you want to sign out?')) {
                window.location.href = '<?= route_url('/logout') ?>';
            }
        }
    </script>
    <script src="<?= asset_url('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
