<?php

$isAdminDashboard = true;

$title = 'Admin Dashboard';

ob_start();


?>

<div class="d-flex">

    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="flex-grow-1">

        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="container-fluid p-4">

            <h1 class="display-6 fw-bold">
                Admin Dashboard
            </h1>

            <p class="text-muted mb-4">
                Live overview of your team's attendance.
            </p>

            <div class="row g-4">

                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <small class="text-muted">
                                Currently Onsite
                            </small>

                            <h2>
                                <?= $stats['currentlyOnsite'] ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <small class="text-muted">
                                Total Staff Today
                            </small>

                            <h2>
                                <?= $stats['totalStaffToday'] ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <small class="text-muted">
                                Pending Sync
                            </small>

                            <h2>
                                <?= $stats['pendingSync'] ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <small class="text-muted">
                                Total Events
                            </small>

                            <h2>
                                <?= $stats['totalEvents'] ?>
                            </h2>
                        </div>
                    </div>
                </div>

            </div>

        </main>

    </div>

</div>

<?php

$content = ob_get_clean();

require __DIR__ . '/../layouts/app.php';