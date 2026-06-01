<?php

declare(strict_types=1);

ob_start();
?>

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
