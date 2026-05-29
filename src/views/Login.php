<?php
require_once __DIR__ . '/includes/Icons.php';

$mockUsers = require __DIR__ . '/../data/MockUsers.php';
$mockUsersJson = json_encode(
    $mockUsers,
    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
);
$adminRoute = '/admin-dashboard';
$staffRoute = '/staff-dashboard';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Login | Clock-It', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/Login.css">
    <script>
        window.clockItMockUsers = <?= $mockUsersJson ?: '[]' ?>;
        window.clockItDeviceAssigned = <?= json_encode($deviceAssigned ?? 'staff') ?>;
    </script>
    <script defer src="/assets/js/Login.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <div
        class="login-app"
        data-login-page
        data-login-api="/api/login"
        data-forgot-password-api="/api/forgot-password"
        data-admin-route="<?= htmlspecialchars($adminRoute, ENT_QUOTES, 'UTF-8') ?>"
        data-staff-route="<?= htmlspecialchars($staffRoute, ENT_QUOTES, 'UTF-8') ?>"
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
