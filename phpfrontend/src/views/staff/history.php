<?php
/**
 * Attendance History Page
 * Staff view of their attendance records
 */
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . route_url('/login.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History - Clock-It</title>
    
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
        }

        /* Workspace Content Offsets for Fixed Sidebar */
        .main-workspace {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-left: 280px; /* Aligns exactly with sidebar component layout width */
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .main-workspace {
                margin-left: 0;
            }
        }

        /* Modern Custom Form Controls */
        .custom-input, .custom-select {
            border: 1px solid rgba(9, 60, 93, 0.15);
            border-radius: 10px;
            padding: 0.6rem 1rem;
            color: var(--deep-navy);
            background-color: #FFFFFF;
            transition: all 0.2s ease;
        }

        .custom-input:focus, .custom-select:focus {
            border-color: var(--mid-blue);
            box-shadow: 0 0 0 3px rgba(59, 117, 151, 0.15);
            outline: none;
        }

        /* Container Cards */
        .custom-card {
            background-color: #FFFFFF;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(9, 60, 93, 0.04);
            overflow: hidden;
        }

        /* Modern Data Table Overhaul */
        .custom-table {
            margin-bottom: 0;
            vertical-align: middle;
        }

        .custom-table thead th {
            background-color: rgba(9, 60, 93, 0.02);
            color: var(--mid-blue);
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(9, 60, 93, 0.06);
            user-select: none;
        }

        .custom-table tbody td {
            padding: 1.2rem 1.5rem;
            color: var(--deep-navy);
            border-bottom: 1px solid rgba(9, 60, 93, 0.04);
            font-size: 0.95rem;
        }

        .custom-table tbody tr:last-child td {
            border-bottom: none;
        }

        .custom-table tbody tr {
            transition: background-color 0.15s ease;
        }

        .custom-table tbody tr:hover {
            background-color: rgba(59, 117, 151, 0.02);
        }

        /* Custom Semantic Status Badges */
        .badge-clock-in {
            background-color: rgba(156, 176, 122, 0.15);
            color: var(--olive-green);
            font-weight: 700;
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .badge-clock-out {
            background-color: rgba(59, 117, 151, 0.12);
            color: var(--mid-blue);
            font-weight: 700;
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        /* Elegant Minimalist Pagination */
        .custom-pagination .page-item .page-link {
            color: var(--mid-blue);
            border: 1px solid rgba(9, 60, 93, 0.08);
            background-color: #FFFFFF;
            padding: 0.5rem 0.9rem;
            margin: 0 2px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .custom-pagination .page-item:hover .page-link {
            background-color: var(--light-gray);
            color: var(--deep-navy);
            border-color: rgba(9, 60, 93, 0.15);
        }

        .custom-pagination .page-item.active .page-link {
            background-color: var(--deep-navy) !important;
            border-color: var(--deep-navy) !important;
            color: #FFFFFF !important;
        }

        .custom-pagination .page-item.disabled .page-link {
            background-color: transparent;
            color: #C0C0C0;
            border-color: rgba(9, 60, 93, 0.04);
        }

        [x-cloak] { display: none !important; }
    </style>
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
        
        // Sort System
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
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-workspace">
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <main class="p-4 p-md-5">
                <div class="container-fluid p-0">
                    
                    <div class="mb-4">
                        <h2 class="fw-bold tracking-tight" style="color: var(--deep-navy);">Attendance History</h2>
                        <p class="text-muted small">Search, organize, and review your historical verification records.</p>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <div class="position-relative">
                                <input 
                                    type="text" 
                                    class="form-control custom-input w-100 ps-4" 
                                    placeholder="Search entries by date or execution location..."
                                    x-model="searchQuery"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select custom-select" x-model="filterType">
                                <option value="all">All Metrics & Types</option>
                                <option value="clock-in">Clock In Actions</option>
                                <option value="clock-out">Clock Out Actions</option>
                            </select>
                        </div>
                    </div>

                    <div class="custom-card card mb-4">
                        <div class="table-responsive">
                            <table class="table custom-table table-hover">
                                <thead>
                                    <tr>
                                        <th style="cursor: pointer; width: 30%;" @click="sort('date')">
                                            Date 
                                            <span class="ms-1 small text-muted">
                                                <i x-show="sortField !== 'date'" class="bi bi-arrow-down-up opacity-50"></i>
                                                <i x-show="sortField === 'date' && sortOrder === 'asc'" class="bi bi-arrow-up text-primary"></i>
                                                <i x-show="sortField === 'date' && sortOrder === 'desc'" class="bi bi-arrow-down text-primary"></i>
                                            </span>
                                        </th>
                                        <th style="cursor: pointer; width: 25%;" @click="sort('time')">
                                            Time
                                            <span class="ms-1 small text-muted">
                                                <i x-show="sortField !== 'time'" class="bi bi-arrow-down-up opacity-50"></i>
                                                <i x-show="sortField === 'time' && sortOrder === 'asc'" class="bi bi-arrow-up text-primary"></i>
                                                <i x-show="sortField === 'time' && sortOrder === 'desc'" class="bi bi-arrow-down text-primary"></i>
                                            </span>
                                        </th>
                                        <th style="width: 20%;">Type</th>
                                        <th style="width: 25%;">Location Offset</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="record in paginatedRecords" :key="record.id">
                                        <tr>
                                            <td class="fw-semibold" x-text="record.date"></td>
                                            <td class="text-muted" x-text="record.time"></td>
                                            <td>
                                                <span :class="record.type === 'clock-in' ? 'badge-clock-in' : 'badge-clock-out'" x-text="record.type === 'clock-in' ? 'CLOCK IN' : 'CLOCK OUT'"></span>
                                            </td>
                                            <td>
                                                <span class="d-inline-flex align-items-center gap-1">
                                                    <i class="bi bi-geo-alt text-muted small"></i>
                                                    <span x-text="record.location"></span>
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                    
                                    <tr x-show="paginatedRecords.length === 0" x-cloak>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="bi bi-folder-x d-block display-6 mb-2 opacity-50"></i>
                                            <span>No historical database records found matching your parameters.</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <nav x-show="totalPages > 1" aria-label="Page navigation" x-cloak>
                        <ul class="pagination custom-pagination justify-content-center">
                            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                <button class="page-link" @click="currentPage = Math.max(1, currentPage - 1)" type="button">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                            </li>
                            <template x-for="page in Array.from({ length: totalPages }, (_, i) => i + 1)" :key="page">
                                <li class="page-item" :class="{ active: currentPage === page }">
                                    <button class="page-link" @click="currentPage = page" type="button" x-text="page"></button>
                                </li>
                            </template>
                            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                <button class="page-link" @click="currentPage = Math.min(totalPages, currentPage + 1)" type="button">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </li>
                        </ul>
                    </nav>
                    
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>