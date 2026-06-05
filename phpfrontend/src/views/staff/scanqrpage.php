<?php

declare(strict_types=1);

ob_start();
?>

<div class="app-shell">
    <?php require __DIR__ . '/../partials/staff_sidebar.php'; ?>

    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content">
            <section class="scan-page container-fluid p-4 p-lg-5">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-12 col-xl-10">
                            <h1 class="display-5 fw-bold scan-title mb-2">Scan QR Code</h1>
                            <p class="fs-4 scan-lead mb-3">Point your camera at the workplace QR code to clock in or out.</p>

                            <div class="mt-3">
                                <?php require __DIR__ . '/../partials/scanqrcard.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<?php
$content = ob_get_clean();

require __DIR__ . '/../layouts/app.php';
