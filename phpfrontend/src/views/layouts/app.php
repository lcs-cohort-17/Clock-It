<?php
$showShell = $showShell ?? true;
$bodyClass = $bodyClass ?? '';
$pageTitle = $pageTitle ?? ($title ?? $brand['name']);
$pageEyebrow = $pageEyebrow ?? $brand['tagline'];
$jsonState = json_encode($clientState, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$stylePath = __DIR__ . '/../../../public/assets/css/style.css';
$scriptPath = __DIR__ . '/../../../public/assets/js/app.js';
$assetVersion = (string) max(
    is_file($stylePath) ? filemtime($stylePath) : time(),
    is_file($scriptPath) ? filemtime($scriptPath) : time()
);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Clock-It') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>?v=<?= e($assetVersion) ?>">
    <script>
        window.ClockItSeed = <?= $jsonState ?: '{}' ?>;
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="<?= asset_url('js/app.js') ?>?v=<?= e($assetVersion) ?>"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="<?= e($bodyClass) ?>" x-data="appShell()" x-bind:class="{ 'nav-open': mobileNavOpen }">
<?php if ($showShell): ?>
    <div class="aurora aurora-one"></div>
    <div class="aurora aurora-two"></div>
    <div class="app-frame">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="app-main">
            <?php include __DIR__ . '/../partials/header.php'; ?>
            <main class="app-content">
                <?= $content ?>
            </main>
        </div>
    </div>
<?php else: ?>
    <?= $content ?>
<?php endif; ?>
</body>
</html>
