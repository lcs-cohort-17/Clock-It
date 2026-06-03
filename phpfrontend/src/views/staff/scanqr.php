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
                    <h1 class="staff-history-title mb-2">Scan QR</h1>
                    <p class="text-muted mb-4">Scan your site QR code to clock in or out.</p>

                    <div class="staff-empty-state">
                        <i class="bi bi-qr-code-scan me-2" aria-hidden="true"></i>
                        QR scanner workspace
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<?php
$content = ob_get_clean();

require __DIR__ . '/../layouts/app.php';
