<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../partials/settings_features.php';

// ── POST action handling ──────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    // -- Google Sheets: connect ------------------------------------------------
    if ($action === 'connect_google_sheets') {
        $_SESSION['gs_connected']       = true;
        $_SESSION['gs_connected_sheet'] = $_SESSION['gs_connected_sheet'] ?: 'attendance_export_' . date('Y');
        $_SESSION['gs_connected_since'] = $_SESSION['gs_connected_since'] ?: date('j M Y');
    }

    // -- Google Sheets: disconnect ---------------------------------------------
    if ($action === 'disconnect_google_sheets') {
        $_SESSION['gs_connected']       = false;
        $_SESSION['gs_connected_sheet'] = '';
        $_SESSION['gs_connected_since'] = '';
    }

    // -- Security settings: save -----------------------------------------------
    if ($action === 'save_security') {
        $raw = (string) ($_POST['security_timeout'] ?? '');

        // Valid only if: non-empty, all digits (no sign, no dot), and value > 0
        $isValid = $raw !== ''
            && ctype_digit($raw)
            && (int) $raw > 0;

        if (!$isValid) {
            $_SESSION['security_error'] = 'Please enter a positive whole number for the session timeout.';
            unset($_SESSION['security_success']);
        } else {
            $_SESSION['security_timeout'] = (int) $raw;
            $_SESSION['security_success'] = 'Security settings saved successfully.';
            unset($_SESSION['security_error']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings – Clock-It Admin</title>
</head>
<body>

<!-- ── Sidebar ─────────────────────────────────────────────────────────────── -->
<nav class="sidebar admin-sidebar">
    <div class="sidebar-brand">
        <span class="brand-name">Clock-It</span>
        <span class="brand-role">Admin portal</span>
    </div>
    <ul class="sidebar-nav">
        <li><a href="/admin-dashboard">Dashboard</a></li>
        <li><a href="/admin/settings">Settings</a></li>
    </ul>
</nav>

<!-- ── Main content ─────────────────────────────────────────────────────────── -->
<main class="admin-main">
    <h1>Settings</h1>

    <div class="settings-cards">
        <?php render_google_sheets_settings_card(); ?>
        <?php render_security_settings_card(); ?>
        <?php render_data_retention_settings_card(); ?>
    </div>
</main>

</body>
</html>
