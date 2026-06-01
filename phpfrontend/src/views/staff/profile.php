<?php

declare(strict_types=1);

ob_start();
?>

<div class="app-shell">
    <?php require __DIR__ . '/../partials/staff_sidebar.php'; ?>

    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content">
            <section class="container-fluid p-4 p-lg-5">
                <div class="page-card p-4 p-lg-5">
                    <h1 class="staff-history-title mb-2">Profile</h1>
                    <p class="text-muted mb-4">Review your Clock It account details.</p>

                    <dl class="row mb-0">
                        <dt class="col-sm-4">Name</dt>
                        <dd class="col-sm-8"><?= e($user['name']) ?></dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8"><?= e($user['email']) ?></dd>

                        <dt class="col-sm-4">Employee ID</dt>
                        <dd class="col-sm-8"><?= e($user['employeeId']) ?></dd>
                    </dl>
                </div>
            </section>
        </main>
    </div>
</div>

<?php
$content = ob_get_clean();

require __DIR__ . '/../layouts/app.php';
