<?php
/**
 * Staff Dashboard / Home Page
 * Main dashboard for staff members
 */
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

// Verify staff access
if (!isset($_SESSION['user_id'])) {
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
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>
</head>
<body x-data="{ 
    currentTime: '<?php echo date('h:i A'); ?>',
    currentStatus: 'Clocked Out',
    currentLocation: 'OFFSITE',
    todaysActivity: {
        firstClockIn: null,
        lastClockOut: null,
        totalHours: 0
    }
}" @init="window.themeManager.initTheme(); setInterval(() => { currentTime = new Intl.DateTimeFormat('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }).format(new Date()); }, 1000)">
    
    <div style="display: flex; min-height: 100vh;">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div style="flex: 1; display: flex; flex-direction: column;">
            <!-- Header -->
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <!-- Dashboard Grid -->
            <?php include __DIR__ . '/../partials/dashboard-grid.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>