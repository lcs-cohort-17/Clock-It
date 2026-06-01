<?php ob_start(); ?>
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-primary fw-semibold small mb-2">Staff</p>
            <h1 class="h3 mb-0">Scan QR</h1>
        </div>
        <a class="btn btn-outline-secondary" href="/staff-dashboard">Back</a>
    </div>

    <section class="card">
        <div class="card-body text-center py-5">
            <div class="border rounded mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 220px; height: 220px;">
                <span class="text-body-secondary">QR scanner demo</span>
            </div>
            <p class="mb-0 text-body-secondary">Frontend demo page for <?= htmlspecialchars($user['name']) ?>.</p>
        </div>
    </section>
</main>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>
