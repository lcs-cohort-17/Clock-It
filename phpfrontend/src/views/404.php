<?php
$title = 'Page not found';
ob_start();
?>
<main class="min-vh-100 d-flex align-items-center justify-content-center bg-light px-3">
    <section class="text-center bg-white border rounded-4 shadow-sm p-4 p-md-5" style="max-width: 32rem;">
        <h1 class="h3 fw-bold mb-3">Page not found</h1>
        <p class="text-body-secondary mb-4">The page you were looking for does not exist.</p>
        <a class="btn btn-primary" href="<?= htmlspecialchars(app_url(), ENT_QUOTES) ?>">Return home</a>
    </section>
</main>
<?php $content = ob_get_clean(); require __DIR__ . '/layouts/app.php'; ?>
