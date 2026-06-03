<?php

$isAdminDashboard = true;

$title = 'Admin Dashboard';

$actions = [
    ['label' => 'QR Generator', 'href' => app_url('/scan-qr'), 'variant' => 'light'],
    ['label' => 'Attendance Logs', 'href' => app_url('/admin-dashboard/attendance'), 'variant' => 'light'],
    ['label' => 'Settings', 'href' => app_url('/admin-dashboard/settings'), 'variant' => 'light'],
];
$sheetsConnected = false;

ob_start();


?>

<div class="app-shell">

    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="main-panel">

        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content container-fluid p-4 p-lg-5 admin-dashboard-page" x-data="attendanceDashboard()">
            <div class="dashboard-page-header d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="display-6 fw-bold">Admin Dashboard</h1>
                    <p class="text-muted mb-0">Live overview of your team's attendance.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary" @click="refresh()" :disabled="refreshing">
                        <i class="bi bi-arrow-repeat" :class="{ 'dashboard-spin': refreshing }" aria-hidden="true"></i>
                        <span x-text="refreshing ? 'Syncing...' : 'Sync now'"></span>
                    </button>
                </div>
            </div>

            <?php require __DIR__ . '/../partials/dashboard-cards.php'; ?>

            <div class="dashboard-section mt-4">
                <?php require __DIR__ . '/../partials/quick-actions.php'; ?>
            </div>

            <div class="row g-4 dashboard-layout mt-1">
                <div class="col-12 col-xl-5">
                    <?php require __DIR__ . '/../partials/onsite-card.php'; ?>
                </div>
                <div class="col-12 col-xl-7">
                    <?php require __DIR__ . '/../partials/activity-card.php'; ?>
                </div>
            </div>

            <section class="dashboard-card sheets-card mt-4 p-4">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-file-earmark-spreadsheet sheets-icon" aria-hidden="true"></i>
                        <div>
                            <h2 class="h5 dashboard-card-title mb-1">Sheets Integration</h2>
                            <p class="dashboard-card-subtitle mb-0">Sync attendance data to Google Sheets.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted small"><?= $sheetsConnected ? 'Connected' : 'Not connected' ?></span>
                        <button type="button" class="btn btn-outline-secondary" @click="connectSheets()">Connect</button>
                    </div>
                </div>
            </section>

        </main>

    </div>

</div>

<?php

$content = ob_get_clean();

require __DIR__ . '/../layouts/app.php';
