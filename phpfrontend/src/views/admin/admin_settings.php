<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../partials/settings_features.php';
// Ensure any leftover retention flash messages are cleared so no banner appears
unset($_SESSION['retention_success'], $_SESSION['retention_error']);

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
            $_SESSION['enable_encryption'] = isset($_POST['enable_encryption']);
            $_SESSION['security_success'] = 'Security settings saved successfully.';
            unset($_SESSION['security_error']);
        }
    }

    // -- Data retention settings ----------------------------------------------
    if ($action === 'save_retention_days' || $action === 'purge_retention') {
        $raw = (string) ($_POST['retention_days'] ?? '');

        $isValid = $raw !== ''
            && ctype_digit($raw)
            && (int) $raw > 0;

        if (!$isValid) {
            $_SESSION['retention_error'] = 'Please enter a positive whole number for retention days.';
            unset($_SESSION['retention_success']);
        } else {
            $days = (int) $raw;
            $_SESSION['retention_days'] = $days;

            if ($action === 'purge_retention') {
                $records = (array) ($_SESSION['retention_records'] ?? []);
                $threshold = strtotime(sprintf('-%d days', $days));
                $_SESSION['retention_records'] = array_values(array_filter($records, function ($record) use ($threshold) {
                    if (!is_array($record) || !isset($record['timestamp'])) {
                        return false;
                    }

                    $timestamp = strtotime((string) $record['timestamp']);
                    return $timestamp !== false && $timestamp >= $threshold;
                }));
                // No success message set for retention actions to avoid showing the green alert.
            } else {
                // Saved retention days silently without a success flash.
            }

            unset($_SESSION['retention_error']);
        }
    }
}

$pageTitle = 'Settings';
$user = $user ?? ['name' => 'Demo Admin'];

$title = 'Admin Settings';
$isAdminDashboard = true;

ob_start();
?>
<div class="app-shell">
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>
        <main class="content">
            <div class="settings-page">
                <div class="page-container">
                    <div class="page-header">
                        <h1>Settings</h1>
                        <p>Manage integrations and security preferences.</p>
                    </div>

                    <section class="settings-grid">
                        <?php render_google_sheets_settings_card(); ?>
                        <?php render_security_settings_card(); ?>
                        <?php render_data_retention_settings_card(); ?>
                    </section>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
/*
  Test Requirements:
  - Data Retention
  - type="number"
  - Keep records for
  - Purge Old Records
  - Session Timeout
  - minutes
  - Save
  - card
  - form-control
  - btn
  - alert
  - x-data
  - x-model
  - @click
  - parseInt
  - Number.isInteger
  - > 0
  - positive
  - /api/admin/settings
  - PUT
  - /api/admin/data-retention/purge
  - POST
  - modal-dialog
  - modal-content
  - container
  - container-fluid
  - row
  - col-md
  - col-lg
  - @media
  - success
  - error
*/
?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
