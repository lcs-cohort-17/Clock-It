<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Data/data.php';

$currentUser = is_array($user ?? null) ? $user : current_user();
$loggedInEmployeeId = $currentUser['employeeId'] ?? null;
$attendanceHistory = [];

if (is_string($loggedInEmployeeId) && $loggedInEmployeeId !== '') {
    $attendanceHistory = array_values(array_filter(
        $mockAttendanceData,
        static fn (array $record): bool => ($record['employeeId'] ?? null) === $loggedInEmployeeId
    ));
}

if ($attendanceHistory === []) {
    $loggedInEmployeeId = 'EMP001';
    $attendanceHistory = array_values(array_filter(
        $mockAttendanceData,
        static fn (array $record): bool => ($record['employeeId'] ?? null) === 'EMP001'
    ));
}

ob_start();
?>

<script>
    window.ATTENDANCE_DATA = {
        history: <?= json_encode($attendanceHistory, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        currentEmployeeId: <?= json_encode($loggedInEmployeeId) ?>,
        employeeName: <?= json_encode($currentUser['name'] ?? '') ?>
    };
</script>

<div class="app-shell">
    <?php require __DIR__ . '/../partials/staff_sidebar.php'; ?>

    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content">
            <?php require __DIR__ . '/../partials/DashboardGrid.php'; ?>
        </main>
    </div>
</div>

<?php
$content = ob_get_clean();

require __DIR__ . '/../layouts/app.php';
