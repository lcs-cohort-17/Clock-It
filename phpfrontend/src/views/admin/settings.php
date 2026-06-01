<?php ob_start(); ?>
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-primary fw-semibold small mb-2">Admin</p>
            <h1 class="h3 mb-0">Settings</h1>
        </div>
        <a class="btn btn-outline-secondary" href="/admin-dashboard">Back</a>
    </div>

    <section class="card">
        <div class="card-body">
            <h2 class="h5">Demo settings</h2>
            <p class="text-body-secondary mb-0">Frontend-only settings page for <?= htmlspecialchars($user['name']) ?>.</p>
        </div>
    </section>
</main>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>
