<?php /** @var string $content */ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Clock-It') ?></title>
    
    <!-- Theme detection -->
    <script>
        window.clockItBasePath = <?= json_encode($basePath ?? '') ?>;
        (() => {
            const savedTheme = localStorage.getItem('theme');
            const theme = savedTheme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.dataset.theme = theme;
            document.documentElement.classList.toggle('dark', theme === 'dark');
        })();
    </script>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/usermanagement.css')) ?>">
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/attendance_log.css')) ?>">
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/attendance_history.css')) ?>">
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/calendar.css')) ?>">
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/dashboard.css')) ?>">
</head>
<body>
    <?= $content ?>
    
    <button type="button" class="sidebar-backdrop" data-sidebar-close aria-label="Close navigation"></button>
    
    <!-- ============================================ -->
    <!-- SCRIPTS - Exact order matters!              -->
    <!-- ============================================ -->
    
    <!-- 1. Config (must load first) -->
    <script src="<?= e(app_url('/assets/js/config.js')) ?>"></script>
    
    <!-- 2. API Service -->
    <script src="<?= e(app_url('/assets/js/api.js')) ?>"></script>
    
    <!-- 3. Alpine.js (must load before store & components) -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- 4. Global Store -->
    <script src="<?= e(app_url('/assets/js/store.js')) ?>"></script>
    
    <!-- 5. Page Components (registered via alpine:init) -->
    <script src="<?= e(app_url('/assets/js/usermanagement.js')) ?>"></script>
    
    <!-- 6. Theme & UI scripts -->
    <script src="<?= e(app_url('/assets/js/theme.js')) ?>"></script>
    <script src="<?= e(app_url('/assets/js/app.js')) ?>"></script>
    <script src="<?= e(app_url('/assets/js/calendar.js')) ?>"></script>
    <script src="<?= e(app_url('/assets/js/dashboard.js')) ?>"></script>
    <script src="<?= e(app_url('/assets/js/sidebar.js')) ?>"></script>
    
    <!-- 7. Third-party libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</body>
</html>