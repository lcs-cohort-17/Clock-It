<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../../Data/data.php';

$currentUser = is_array($user ?? null) ? $user : current_user();
$loggedInEmployeeId = $currentUser['employeeId'] ?? null;
$allAttendanceHistory = $mockAttendanceData ?? [];
$attendanceHistory = [];
$attendanceApiUrl = null;

if (is_string($loggedInEmployeeId) && $loggedInEmployeeId !== '') {
    $attendanceHistory = array_values(array_filter(
        $allAttendanceHistory,
        static fn (array $record): bool => ($record['employeeId'] ?? null) === $loggedInEmployeeId
    ));
    $attendanceApiUrl = app_url('/api/attendance.php?employee_id=' . rawurlencode($loggedInEmployeeId));
}

$displayUserName = $attendanceHistory[0]['employeeName'] ?? $currentUser['name'] ?? 'Staff User';

ob_start();
?>

<div class="app-shell">
    <?php require __DIR__ . '/../partials/staff_sidebar.php'; ?>

    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content">
            <div class="attendance-page-shell">

                <script>
                    window.ATTENDANCE_DATA = {
                        history: <?= json_encode($attendanceHistory, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                        currentEmployeeId: <?= json_encode($loggedInEmployeeId) ?>,
                        currentUser: <?= json_encode($currentUser, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                        displayUserName: <?= json_encode($displayUserName) ?>,
                        apiUrl: <?= json_encode($attendanceApiUrl) ?>
                    };
                </script>

                <?php require __DIR__ . '/../partials/staff_attendance_history.php'; ?>
                <script src="<?= e(app_url('/assets/js/attendance_history_page.js')) ?>"></script>
            </div>
        </main>
    </div>
</div>

<?php
$content = ob_get_clean();

require __DIR__ . '/../layouts/app.php';
