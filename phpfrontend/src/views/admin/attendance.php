<?php
/**
 * Attendance Log Page
 * Admin page for viewing and managing attendance records
 */
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . route_url('/login'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Log - Clock-It</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>
</head>
<body x-data="{ 
    sidebarOpen: true,
    selectedTab: 'all',
    searchQuery: '',
    sortField: 'date',
    sortOrder: 'desc',
    attendanceRecords: [
        { id: 1, employee: 'John Doe', type: 'clock-in', time: '2026-05-29 09:00:00', date: '2026-05-29' },
        { id: 2, employee: 'Jane Smith', type: 'clock-in', time: '2026-05-29 09:15:00', date: '2026-05-29' },
        { id: 3, employee: 'Bob Johnson', type: 'clock-out', time: '2026-05-29 17:30:00', date: '2026-05-29' }
    ],
    get filteredRecords() {
        return this.attendanceRecords.filter(record => 
            record.employee.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            record.type.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
    }
}" @init="window.themeManager.initTheme()">
    
    <div style="display: flex;">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div style="flex: 1; display: flex; flex-direction: column;">
            <!-- Header -->
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <!-- Page Content -->
            <main class="dashboard-section" style="padding: 2rem;">
                <div class="container-fluid">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <input 
                                type="text" 
                                class="form-control" 
                                placeholder="Search by employee or type..."
                                x-model="searchQuery"
                            >
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-secondary w-100">
                                <i class="bi bi-download me-1" aria-hidden="true"></i>Export CSV
                            </button>
                        </div>
                    </div>

                    <!-- Records Table -->
                    <div class="table-responsive">
                        <table class="table table-hover border">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Date & Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="record in filteredRecords" :key="record.id">
                                    <tr>
                                        <td x-text="record.employee"></td>
                                        <td>
                                            <span class="badge" :class="record.type === 'clock-in' ? 'bg-success' : 'bg-warning'" x-text="record.type.replace('-', ' ').toUpperCase()"></span>
                                        </td>
                                        <td x-text="new Date(record.time).toLocaleString()"></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary">View</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function logoutUser() {
            if (confirm('Are you sure you want to sign out?')) {
                window.location.href = '<?= route_url('/logout') ?>';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
