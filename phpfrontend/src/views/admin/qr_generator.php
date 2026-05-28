<?php ob_start(); ?>

<div class="d-flex">

    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="p-4 flex-grow-1">

        <h1 class="mb-4">QR Code Generator</h1>

        <section class="card p-4 shadow-sm">

            <p class="badge bg-primary">
                Generated Workplace Code
            </p>

            <div class="text-center my-4">

                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=CLOCK-IT-SITE-001"
                    alt="QR Code"
                    class="img-fluid"
                >

            </div>

            <h5 class="text-center">
                CLOCK-IT-SITE-001
            </h5>

            <div class="text-center mt-4">
                <button
                    class="btn btn-primary"
                    onclick="alert('QR code regenerated')"
                >
                    Generate QR
                </button>
            </div>

        </section>

    </main>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>