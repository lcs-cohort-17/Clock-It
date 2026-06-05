<?php /** @var string $content */ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Clock-It') ?></title>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('theme');
            const theme = savedTheme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.dataset.theme = theme;
            document.documentElement.classList.toggle('dark', theme === 'dark');
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/usermanagement.css')) ?>">
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/attendance_log.css')) ?>">
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/attendance_history.css')) ?>">
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/calendar.css')) ?>">
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/dashboard.css')) ?>">
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/admin-leave-requests.css')) ?>">

    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script defer src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script defer src="<?= e(app_url('/assets/js/theme.js')) ?>"></script>
    <script defer src="<?= e(app_url('/assets/js/app.js')) ?>"></script>
    <script defer src="<?= e(app_url('/assets/js/calendar.js')) ?>"></script>
    <script defer src="<?= e(app_url('/assets/js/usermanagement.js')) ?>"></script>
    <script defer src="<?= e(app_url('/assets/js/dashboard.js')) ?>"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="sidebar-collapsed">
<?= $content ?>
<button type="button" class="sidebar-backdrop" data-sidebar-close aria-label="Close navigation"></button>
</body>
</html>
