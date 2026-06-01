<?php ob_start(); ?>
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-primary fw-semibold small mb-2">Staff</p>
            <h1 class="h3 mb-0">Calendar</h1>
        </div>
        <a class="btn btn-outline-secondary" href="/staff-dashboard">Back</a>
    </div>

    <section class="card">
        <div class="card-body">
            <p class="text-body-secondary mb-0">Calendar demo for <?= htmlspecialchars($user['name']) ?>.</p>
        </div>
    </section>
</main>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>
