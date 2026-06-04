<?php
require_once __DIR__ . '/includes/icons.php';

if (!function_exists('app_url')) {
    function app_url(string $path = '/'): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

// We don't need mock users anymore, but keep for now to not break things
$mockUsers = $loginUsers ?? require __DIR__ . '/../data/LoginMockUsers.php';
$mockUsersJson = json_encode(
    $mockUsers,
    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
);
$loginRoute = app_url('/login');
$loginApiRoute = app_url('/api/login');
$forgotPasswordApiRoute = app_url('/api/forgot-password');
$socialLoginApiRoute = app_url('/api/social-login');
$adminRoute = app_url('/admin-dashboard');
$staffRoute = app_url('/staff-dashboard');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Login | Clock-It', ENT_QUOTES, 'UTF-8') ?></title>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('theme');
            const theme = savedTheme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.dataset.theme = theme;
            document.documentElement.classList.toggle('dark', theme === 'dark');
        })();
    </script>
    <link rel="icon" href="<?= e(app_url('/favicon.svg')) ?>" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/login.css')) ?>">
    
    <!-- ============================================ -->
    <!-- ADD THESE: Our new scripts -->
    <!-- ============================================ -->
    <script src="<?= e(app_url('/assets/js/config.js')) ?>"></script>
    <script src="<?= e(app_url('/assets/js/api.js')) ?>"></script>
    
    <script>
        // Keep mock users for backward compatibility
        window.clockItMockUsers = <?= $mockUsersJson ?: '[]' ?>;
        window.clockItDeviceAssigned = <?= json_encode($deviceAssigned ?? 'staff') ?>;
    </script>
    
    <script defer src="<?= e(app_url('/assets/js/theme.js')) ?>"></script>
    <script defer src="<?= e(app_url('/assets/js/login.js')) ?>"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="<?= e(app_url('/assets/js/store.js')) ?>"></script>
</head>
<body>
    <div
        class="login-app"
        data-login-page
        data-login-api="<?= htmlspecialchars($loginApiRoute, ENT_QUOTES, 'UTF-8') ?>"
        data-forgot-password-api="<?= htmlspecialchars($forgotPasswordApiRoute, ENT_QUOTES, 'UTF-8') ?>"
        data-social-login-api="<?= htmlspecialchars($socialLoginApiRoute, ENT_QUOTES, 'UTF-8') ?>"
        data-admin-route="<?= htmlspecialchars($adminRoute, ENT_QUOTES, 'UTF-8') ?>"
        data-staff-route="<?= htmlspecialchars($staffRoute, ENT_QUOTES, 'UTF-8') ?>"
        data-login-route="<?= htmlspecialchars($loginRoute, ENT_QUOTES, 'UTF-8') ?>"
        x-data="clockitLogin()"
        x-init="init()"
    >
        <!-- Rest of your existing login page HTML -->
        <button
            type="button"
            class="login-theme-toggle"
            data-theme-toggle
            aria-label="Switch to dark mode"
            aria-pressed="false"
            title="Switch to dark mode"
        >
            <i class="bi bi-sun-fill" aria-hidden="true"></i>
            <span class="login-theme-toggle-track" aria-hidden="true">
                <span class="login-theme-toggle-thumb"></span>
            </span>
            <i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
        </button>

        <main class="login-page">
            <section class="promo-column" aria-label="Clock It product highlights">
                <?php include __DIR__ . '/partials/auth/PromoSection.php'; ?>
            </section>

            <section class="auth-panel" aria-label="Clock It sign in">
                <div class="auth-topbar">
                    <div class="mobile-brand">
                        <div class="mobile-brand-mark">
                            <?= clockit_icon('clock', 'icon') ?>
                        </div>
                        <span>Clock It</span>
                    </div>
                </div>

                <div class="form-container">
                    <?php include __DIR__ . '/partials/auth/LoginForm.php'; ?>
                </div>
            </section>
        </main>

        <?php include __DIR__ . '/partials/auth/ForgotPasswordModal.php'; ?>
    </div>
</body>
</html>