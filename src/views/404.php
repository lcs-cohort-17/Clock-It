<?php
if (!function_exists('clockit_route')) {
    function clockit_route(string $path): string
    {
        return $path === '/' ? '/' : '/' . ltrim($path, '/');
    }
}

ob_start();
?>
<main class="login-page"><section class="login-card"><h1>Page not found</h1><a href="<?= htmlspecialchars(clockit_route('/'), ENT_QUOTES, 'UTF-8') ?>">Return home</a></section></main>
<?php $content = ob_get_clean(); require __DIR__ . '/layouts/app.php'; ?>
