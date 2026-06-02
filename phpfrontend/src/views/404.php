<main class="auth-shell">
    <section class="auth-visual">
        <a href="<?= route_url('/login') ?>" class="brand-mark">
            <span class="brand-symbol"><?= ui_icon('spark') ?></span>
            <span class="brand-copy">
                <span><?= e($brand['name']) ?></span>
                <small><?= e($brand['tagline']) ?></small>
            </span>
        </a>
        <h1>Page not found.</h1>
        <p class="mt-3">The workspace route you requested does not exist.</p>
    </section>
    <section class="auth-card">
        <span class="badge-soft coral">404</span>
        <h2>Return to a valid workspace</h2>
        <a class="btn btn-primary" href="<?= route_url('/login') ?>">Back to sign in</a>
    </section>
</main>
