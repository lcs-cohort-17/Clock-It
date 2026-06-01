<?php
require_once __DIR__ . '/includes/icons.php';

if (!function_exists('clockit_asset')) {
    function clockit_asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('clockit_route')) {
    function clockit_route(string $path): string
    {
        return $path === '/' ? '/' : '/' . ltrim($path, '/');
    }
}

$mockUsers = require __DIR__ . '/../data/MockUsers.php';
$mockUsersJson = json_encode(
    $mockUsers,
    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
);
$loginRoute = clockit_route('/login');
$loginApiRoute = clockit_route('/api/login');
$forgotPasswordApiRoute = clockit_route('/api/forgot-password');
$adminRoute = clockit_route('/admin-dashboard');
$staffRoute = clockit_route('/staff-dashboard');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Login | Clock-It', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" href="<?= htmlspecialchars(clockit_asset('favicon.svg'), ENT_QUOTES, 'UTF-8') ?>" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(clockit_asset('assets/css/login.css'), ENT_QUOTES, 'UTF-8') ?>">
    <script>
        window.clockItMockUsers = <?= $mockUsersJson ?: '[]' ?>;
        window.clockItDeviceAssigned = <?= json_encode($deviceAssigned ?? 'staff') ?>;
    </script>
    <script defer src="<?= htmlspecialchars(clockit_asset('assets/js/login.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <div
        class="login-app"
        data-login-page
        data-login-api="<?= htmlspecialchars($loginApiRoute, ENT_QUOTES, 'UTF-8') ?>"
        data-forgot-password-api="<?= htmlspecialchars($forgotPasswordApiRoute, ENT_QUOTES, 'UTF-8') ?>"
        data-admin-route="<?= htmlspecialchars($adminRoute, ENT_QUOTES, 'UTF-8') ?>"
        data-staff-route="<?= htmlspecialchars($staffRoute, ENT_QUOTES, 'UTF-8') ?>"
        data-login-route="<?= htmlspecialchars($loginRoute, ENT_QUOTES, 'UTF-8') ?>"
        x-data="clockitLogin()"
        x-init="init()"
    >
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
