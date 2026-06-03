<?php

declare(strict_types=1);

$attendanceHistory = require __DIR__ . '/../../data/staff_attendance_history.php';

ob_start();
?>

<div class="app-shell">
    <?php require __DIR__ . '/../partials/staff_sidebar.php'; ?>

    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content">
            <div class="staff-history container-fluid p-4 p-lg-5">
                <script>
                    window.ATTENDANCE_DATA = {
                        history: <?= json_encode($attendanceHistory, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
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
