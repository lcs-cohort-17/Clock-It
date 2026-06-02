<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

// If not a staff member, redirect them to login or their admin panel
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'staff') {
    // Elegant fallback: If an admin accidentally accesses a staff link, send them back to admin base
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header('Location: ' . route_url('/admin-dashboard'));
        exit;
    }
    header('Location: ' . route_url('/login'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Clock-It</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>

    <style>
        /* Design System Token Mapping */
        :root {
            --deep-navy: #093C5D;
            --mid-blue: #3B7597;
            --olive-green: #9CB07A;
            --light-gray: #F5F5F5;
        }

        body {
            background-color: var(--light-gray);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--deep-navy);
            overflow-x: hidden;
        }

        /* Workspace Content Offsets for the Fixed Sidebar */
        .main-workspace {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-left: 280px; /* Aligns exactly with sidebar component layout width */
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Smooth responsive fallback layout for smaller screen containers */
        @media (max-width: 991.98px) {
            .main-workspace {
                margin-left: 0;
            }
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data="{ 
    currentTime: '<?php echo date('h:i A'); ?>',
    currentStatus: '<?php echo $_SESSION['attendance_status'] ?? 'Clocked Out'; ?>',
    currentLocation: '<?php echo $_SESSION['attendance_location'] ?? 'OFFSITE'; ?>',
    todaysActivity: {
        firstClockIn: null,
        lastClockOut: null,
        totalHours: 0
    }
}" x-init="window.themeManager.initTheme(); setInterval(() => { currentTime = new Intl.DateTimeFormat('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }).format(new Date()); }, 1000)">
    
    <div style="display: flex; min-height: 100vh;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-workspace">
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <main class="p-4 p-md-5 flex-grow-1">
                <div class="container-fluid p-0">
                    <?php include __DIR__ . '/../partials/dashboard-grid.php'; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>