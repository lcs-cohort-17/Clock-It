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
    <link href="<?= asset_url('vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_url('vendor/bootstrap-icons/font/bootstrap-icons.min.css') ?>">
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
    selectedRecord: null,
    attendanceRecords: [
        { id: 1, employee: 'John Doe', type: 'clock-in', time: '2026-05-29 09:00:00', date: '2026-05-29', device: 'desktop' },
        { id: 2, employee: 'Jane Smith', type: 'clock-in', time: '2026-05-29 09:15:00', date: '2026-05-29', device: 'mobile' },
        { id: 3, employee: 'Bob Johnson', type: 'clock-out', time: '2026-05-29 17:30:00', date: '2026-05-29', device: 'tablet' }
    ],
    get filteredRecords() {
        return this.attendanceRecords.filter(record => 
            record.employee.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            record.type.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            record.device.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
    },
    formatRecordType(type) {
        return type.replace('-', ' ').toUpperCase();
    },
    formatDevice(device) {
        return device.charAt(0).toUpperCase() + device.slice(1);
    },
    formatDateTime(time) {
        return new Date(time).toLocaleString();
    },
    csvValue(value) {
        const quote = String.fromCharCode(34);
        return quote + String(value).replaceAll(quote, quote + quote) + quote;
    },
    exportCsv() {
        const rows = [
            ['Employee', 'Type', 'Date & Time', 'Device'],
            ...this.filteredRecords.map(record => [
                record.employee,
                this.formatRecordType(record.type),
                this.formatDateTime(record.time),
                this.formatDevice(record.device)
            ])
        ];
        const csv = rows
            .map(row => row.map(value => this.csvValue(value)).join(','))
            .join('\r\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');

        link.href = url;
        link.download = 'attendance-records.csv';
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    },
    viewRecord(record) {
        this.selectedRecord = record;
    },
    closeRecordModal() {
        this.selectedRecord = null;
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
                            <button class="btn btn-outline-secondary w-100" @click="exportCsv()" type="button">
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
                                    <th>Device</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="record in filteredRecords" :key="record.id">
                                    <tr>
                                        <td x-text="record.employee"></td>
                                        <td>
                                            <span class="badge" :class="record.type === 'clock-in' ? 'bg-success' : 'bg-warning'" x-text="formatRecordType(record.type)"></span>
                                        </td>
                                        <td x-text="formatDateTime(record.time)"></td>
                                        <td>
                                            <span class="badge bg-secondary" x-text="formatDevice(record.device)"></span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary" @click="viewRecord(record)" type="button">
                                                <i class="bi bi-eye me-1" aria-hidden="true"></i>View
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="filteredRecords.length === 0">
                                    <td colspan="5" class="text-center text-muted py-4">No attendance records found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Attendance Details Modal -->
                    <div class="modal fade show user-form-modal" x-show="selectedRecord" x-cloak tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="attendanceDetailsTitle">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="attendanceDetailsTitle">Attendance Details</h5>
                                    <button type="button" class="btn-close" aria-label="Close" @click="closeRecordModal()"></button>
                                </div>
                                <div class="modal-body" x-show="selectedRecord">
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item d-flex justify-content-between gap-3 px-0">
                                            <span class="text-muted">Employee</span>
                                            <strong x-text="selectedRecord?.employee"></strong>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between gap-3 px-0">
                                            <span class="text-muted">Type</span>
                                            <strong x-text="selectedRecord ? formatRecordType(selectedRecord.type) : ''"></strong>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between gap-3 px-0">
                                            <span class="text-muted">Date & Time</span>
                                            <strong x-text="selectedRecord ? formatDateTime(selectedRecord.time) : ''"></strong>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between gap-3 px-0">
                                            <span class="text-muted">Device</span>
                                            <strong x-text="selectedRecord ? formatDevice(selectedRecord.device) : ''"></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" @click="closeRecordModal()">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-backdrop fade show" x-show="selectedRecord" x-cloak></div>
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
    <script src="<?= asset_url('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
