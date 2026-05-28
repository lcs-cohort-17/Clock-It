<?php /** @var string $content */ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= htmlspecialchars($title ?? 'Clock-It') ?></title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Global CSS -->
    <link rel="stylesheet" href="/assets/css/app.css">

    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Bootstrap JS -->
    <script
        defer
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>

    <!-- Global JS -->
    <script defer src="/assets/js/app.js"></script>

    <!-- QR Scanner -->
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>

<body>

    <?php require __DIR__ . '/../partials/header.php'; ?>

    <?= $content ?>

    <?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>