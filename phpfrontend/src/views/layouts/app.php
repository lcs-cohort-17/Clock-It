<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clock-It</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
    <script src="/assets/js/app.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="min-vh-100 d-flex flex-column">
    <div class="flex-grow-1">
        <?= $content ?? '' ?>
    </div>
    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
