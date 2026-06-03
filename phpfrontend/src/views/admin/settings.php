<?php
/**
 * Admin Settings Page
 * Admin configuration and settings
 */
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

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
    <title>Admin Settings - Clock-It</title>
    <link href="<?= asset_url('vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_url('vendor/bootstrap-icons/font/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>
</head>
<body x-data="{ 
    sidebarOpen: true,
    settings: {
        companyName: 'Acme Corp',
        googleSheetsEnabled: false,
        dataRetentionDays: 90,
        maxClockInDistance: 100,
        requireGPS: false
    }
}" @init="window.themeManager.initTheme()">
    
    <div style="display: flex;">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div style="flex: 1; display: flex; flex-direction: column;">
            <!-- Header -->
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <!-- Page Content -->
            <main class="dashboard-section" style="padding: 2rem;">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- General Settings -->
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">General Settings</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Company Name</label>
                                        <input type="text" class="form-control" x-model="settings.companyName">
                                    </div>
                                </div>
                            </div>

                            <!-- Data Retention -->
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Data Retention</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Retention Period (Days)</label>
                                        <input type="number" class="form-control" x-model="settings.dataRetentionDays">
                                    </div>
                                </div>
                            </div>

                            <!-- Location Settings -->
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Location Settings</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Max Clock-In Distance (meters)</label>
                                        <input type="number" class="form-control" x-model="settings.maxClockInDistance">
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" x-model="settings.requireGPS" id="requireGPS">
                                        <label class="form-check-label" for="requireGPS">
                                            Require GPS for clock-in
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Google Sheets Integration -->
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Google Sheets Integration</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" x-model="settings.googleSheetsEnabled" id="sheetsToggle">
                                        <label class="form-check-label" for="sheetsToggle">
                                            Enable Google Sheets Sync
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Save Button -->
                            <button class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2" aria-hidden="true"></i>Save Settings
                            </button>
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
