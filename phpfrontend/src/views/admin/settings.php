<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../partials/settings_features.php';

$_SESSION['gs_connected'] ??= false;
$_SESSION['gs_connected_sheet'] ??= '';
$_SESSION['gs_connected_since'] ??= '';
$_SESSION['security_timeout'] ??= 30;
$_SESSION['retention_days'] ??= 365;
$_SESSION['retention_records'] ??= [
    ['id' => 1, 'name' => 'Old attendance record', 'date' => date('Y-m-d', strtotime('-420 days'))],
    ['id' => 2, 'name' => 'Recent attendance record', 'date' => date('Y-m-d', strtotime('-10 days'))],
];
unset($_SESSION['security_error'], $_SESSION['security_success'], $_SESSION['retention_error'], $_SESSION['retention_success']);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'connect_google_sheets') {
    $_SESSION['gs_connected'] = true;
    $_SESSION['gs_connected_sheet'] = 'attendance_export_2026';
    $_SESSION['gs_connected_since'] = date('j M Y');
    $_GET = [];
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'disconnect_google_sheets') {
    $_SESSION['gs_connected'] = false;
    $_SESSION['gs_connected_sheet'] = '';
    $_SESSION['gs_connected_since'] = '';
    $_GET = [];
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'save_security') {
    $timeout = trim((string) ($_POST['security_timeout'] ?? ''));

    if ($timeout === '' || !ctype_digit($timeout) || (int) $timeout <= 0) {
        $_SESSION['security_error'] = 'Please enter a positive whole number for the session timeout.';
    } else {
        $_SESSION['security_timeout'] = (int) $timeout;
        $_SESSION['security_success'] = 'Security settings saved successfully.';
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'purge_retention') {
    $days = trim((string) ($_POST['retention_days'] ?? ''));

    if ($days === '' || !ctype_digit($days) || (int) $days <= 0) {
        $_SESSION['retention_error'] = 'Please enter a positive whole number of days.';
    } else {
        $_SESSION['retention_days'] = (int) $days;
        $cutoff = strtotime('-' . (int) $days . ' days');
        $beforeCount = count($_SESSION['retention_records']);
        $_SESSION['retention_records'] = array_values(array_filter(
            $_SESSION['retention_records'],
            static fn (array $record): bool => strtotime($record['date']) >= $cutoff
        ));
        $purgedCount = $beforeCount - count($_SESSION['retention_records']);
        $_SESSION['retention_success'] = $purgedCount . ' old record' . ($purgedCount === 1 ? '' : 's') . ' purged. Recent records were kept.';
    }
}

$pageTitle = 'Settings';
$user = $user ?? ['name' => 'Demo Admin'];

ob_start();
?>
<div class="app-shell">
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>
        <main class="content">
            <h1>Settings</h1>
            <section class="grid two-col">
                <?php render_google_sheets_settings_card(); ?>
                <?php render_security_settings_card(); ?>
                <?php render_data_retention_settings_card(); ?>
            </section>
        </main>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
