<?php

$isAdminDashboard = true;

$title = 'Admin Dashboard';

ob_start();


?>

<div class="app-shell">

    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="main-panel">

        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content container-fluid p-4">

            <h1 class="display-6 fw-bold">
                Admin Dashboard
            </h1>

            <p class="text-muted mb-4">
                Live overview of your team's attendance.
            </p>

            <?php require __DIR__ . '/../partials/dashboard-cards.php'; ?>

        </main>

    </div>

</div>

<?php

$content = ob_get_clean();

require __DIR__ . '/../layouts/app.php';
