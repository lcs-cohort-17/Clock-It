<?php
/**
 * Attendance History Page
 * Staff view of their attendance records
 */
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

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
    <title>Attendance History - Clock-It</title>
    <link href="<?= asset_url('vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_url('vendor/bootstrap-icons/font/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>
</head>
<body x-data="{ 
    currentPage: 1,
    itemsPerPage: 10,
    sortField: 'date',
    sortOrder: 'desc',
    searchQuery: '',
    filterType: 'all',
    records: [
        { id: 1, date: '2026-05-29', time: '09:00:00', type: 'clock-in', location: 'Office' },
        { id: 2, date: '2026-05-29', time: '17:30:00', type: 'clock-out', location: 'Office' },
        { id: 3, date: '2026-05-28', time: '09:15:00', type: 'clock-in', location: 'Office' },
        { id: 4, date: '2026-05-28', time: '17:45:00', type: 'clock-out', location: 'Office' },
        { id: 5, date: '2026-05-27', time: '08:50:00', type: 'clock-in', location: 'Office' },
    ],
    
    get filteredRecords() {
        let filtered = this.records.filter(r => {
            const typeMatch = this.filterType === 'all' || r.type === this.filterType;
            const searchMatch = !this.searchQuery || r.date.includes(this.searchQuery) || r.location.includes(this.searchQuery);
            return typeMatch && searchMatch;
        });
        
        // Sort
        filtered.sort((a, b) => {
            const aVal = a[this.sortField];
            const bVal = b[this.sortField];
            const cmp = aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
            return this.sortOrder === 'asc' ? cmp : -cmp;
        });
        
        return filtered;
    },
    
    get paginatedRecords() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        return this.filteredRecords.slice(start, start + this.itemsPerPage);
    },
    
    get totalPages() {
        return Math.ceil(this.filteredRecords.length / this.itemsPerPage);
    },
    
    sort(field) {
        if (this.sortField === field) {
            this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortField = field;
            this.sortOrder = 'asc';
        }
    }
}" @init="window.themeManager.initTheme()">
    
    <div style="display: flex; min-height: 100vh;">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div style="flex: 1; display: flex; flex-direction: column;">
            <!-- Header -->
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <!-- Page Content -->
            <main class="dashboard-section" style="padding: 2rem;">
                <div class="container-fluid">
                    <h2 class="mb-4">Attendance History</h2>

                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <input 
                                type="text" 
                                class="form-control" 
                                placeholder="Search by date or location..."
                                x-model="searchQuery"
                            >
                        </div>
                        <div class="col-md-6">
                            <select class="form-select" x-model="filterType">
                                <option value="all">All Types</option>
                                <option value="clock-in">Clock In</option>
                                <option value="clock-out">Clock Out</option>
                            </select>
                        </div>
                    </div>

                    <!-- History Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-hover border">
                            <thead class="table-light">
                                <tr>
                                    <th style="cursor: pointer;" @click="sort('date')">
                                        Date
                                        <i class="bi bi-caret-up-fill ms-1" x-show="sortField === 'date' && sortOrder === 'asc'" aria-hidden="true"></i>
                                        <i class="bi bi-caret-down-fill ms-1" x-show="sortField === 'date' && sortOrder === 'desc'" aria-hidden="true"></i>
                                    </th>
                                    <th style="cursor: pointer;" @click="sort('time')">
                                        Time
                                        <i class="bi bi-caret-up-fill ms-1" x-show="sortField === 'time' && sortOrder === 'asc'" aria-hidden="true"></i>
                                        <i class="bi bi-caret-down-fill ms-1" x-show="sortField === 'time' && sortOrder === 'desc'" aria-hidden="true"></i>
                                    </th>
                                    <th>Type</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="record in paginatedRecords" :key="record.id">
                                    <tr>
                                        <td x-text="record.date"></td>
                                        <td x-text="record.time"></td>
                                        <td>
                                            <span class="badge" :class="record.type === 'clock-in' ? 'bg-success' : 'bg-warning'" x-text="record.type.replace('-', ' ').toUpperCase()"></span>
                                        </td>
                                        <td x-text="record.location"></td>
                                    </tr>
                                </template>
                                <tr x-show="paginatedRecords.length === 0">
                                    <td colspan="4" class="text-center text-muted py-4">No records found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav x-show="totalPages > 1" aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                <button class="page-link" @click="currentPage = Math.max(1, currentPage - 1)" type="button">Previous</button>
                            </li>
                            <template x-for="page in Array.from({ length: totalPages }, (_, i) => i + 1)" :key="page">
                                <li class="page-item" :class="{ active: currentPage === page }">
                                    <button class="page-link" @click="currentPage = page" type="button" x-text="page"></button>
                                </li>
                            </template>
                            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                <button class="page-link" @click="currentPage = Math.min(totalPages, currentPage + 1)" type="button">Next</button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </main>
        </div>
    </div>

    <script src="<?= asset_url('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
