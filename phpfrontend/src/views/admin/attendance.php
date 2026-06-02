<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

// If not an admin, kick them completely out of the admin routing context
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>

    <style>
        /* Clock-It Custom Corporate Design System Tokens */
        :root {
            --deep-navy: #093C5D;
            --mid-blue: #3B7597;
            --olive-green: #9CB07A;
            --light-gray: #F5F5F5;
            --transition-speed: 0.3s;
        }

        body {
            background-color: var(--light-gray);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--deep-navy);
            overflow-x: hidden;
        }

        /* Responsive Animated Sidebar Core Layout Frame */
        .app-sidebar {
            background-color: var(--deep-navy);
            color: #FFFFFF;
            min-height: 100vh;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 4px 0 25px rgba(9, 60, 93, 0.15);
            overflow: hidden;
        }

        /* Sidebar Item Links styling */
        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            padding: 0.85rem 1.5rem;
            margin: 0.2rem 1rem;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .sidebar-nav-link:hover {
            color: #FFFFFF;
            background-color: rgba(255, 255, 255, 0.08);
        }

        .sidebar-nav-link.active {
            color: #FFFFFF;
            background-color: var(--mid-blue);
            box-shadow: 0 4px 12px rgba(59, 117, 151, 0.3);
        }

        .sidebar-logout {
            background: transparent;
            border: none;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #FFA3A3;
            padding: 1rem 1.5rem;
            margin: auto 1rem 1.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            text-align: left;
            transition: all 0.2s ease;
        }

        .sidebar-logout:hover {
            background-color: rgba(255, 163, 163, 0.1);
            color: #FF6B6B;
        }

        /* Dynamic Main Workspace Engine Wrapper */
        .main-workspace {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        }

        .app-header {
            background-color: #FFFFFF;
            border-bottom: 1px solid rgba(9, 60, 93, 0.06);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(9, 60, 93, 0.02);
        }

        /* Custom Structure Component Blocks */
        .custom-card {
            background-color: #FFFFFF;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(9, 60, 93, 0.03);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .custom-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(9, 60, 93, 0.06);
        }

        .custom-card .card-header {
            background-color: rgba(9, 60, 93, 0.01) !important;
            border-bottom: 1px solid rgba(9, 60, 93, 0.06);
            padding: 1.2rem 1.5rem;
            color: var(--deep-navy);
            font-weight: 700;
        }

        /* Interactive Filter Tab Controls */
        .nav-tabs-custom {
            display: flex;
            gap: 0.5rem;
            border-bottom: 1px solid rgba(9, 60, 93, 0.08);
            padding: 0 1.5rem;
            background-color: rgba(9, 60, 93, 0.01);
        }

        .tab-link-custom {
            background: transparent;
            border: none;
            color: text-muted;
            padding: 1rem 1rem;
            font-size: 0.9rem;
            font-weight: 600;
            position: relative;
            transition: color 0.2s ease;
        }

        .tab-link-custom:hover {
            color: var(--mid-blue);
        }

        .tab-link-custom.active {
            color: var(--deep-navy);
        }

        .tab-link-custom.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--mid-blue);
            border-radius: 3px 3px 0 0;
        }

        /* Sortable Table Header UI */
        th.sortable-header {
            cursor: pointer;
            user-select: none;
        }
        th.sortable-header:hover {
            background-color: rgba(9, 60, 93, 0.04) !important;
            color: var(--deep-navy) !important;
        }

        /* Custom Action System Buttons */
        .btn-brand-outline {
            border: 1px solid var(--mid-blue);
            color: var(--mid-blue);
            background: transparent;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-brand-outline:hover {
            background-color: rgba(59, 117, 151, 0.08);
            color: var(--deep-navy);
            border-color: var(--deep-navy);
        }

        /* Native App Dark Mode Matrix Overrides */
        [data-bs-theme="dark"] {
            --light-gray: #0B131A;
            --deep-navy: #E6F0F7;
            --mid-blue: #6FAAD0;
            
            .app-header {
                background-color: #121F2B;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            }

            .custom-card {
                background-color: #121F2B !important;
                box-shadow: 0 4px 25px rgba(0, 0, 0, 0.2);
            }

            .custom-card .card-header {
                background-color: rgba(255, 255, 255, 0.02) !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            }

            .nav-tabs-custom {
                border-bottom-color: rgba(255, 255, 255, 0.08);
                background-color: rgba(255, 255, 255, 0.01);
            }
            .tab-link-custom.active {
                color: #FFFFFF;
            }

            .table {
                --bs-table-bg: transparent;
                color: var(--deep-navy);
            }

            .form-control {
                background-color: #0B131A;
                border-color: rgba(255, 255, 255, 0.1);
                color: #FFFFFF;
            }

            .text-muted {
                color: #A3B8CC !important;
            }
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data="{ 
    sidebarOpen: true,
    searchQuery: '',
    selectedTab: 'all',
    sortField: 'time',
    sortOrder: 'desc',
    currentPage: 1,
    perPage: 5,
    
    attendanceRecords: [
        { id: 1, employeeId: 'EMP-001', employee: 'John Doe', type: 'clock-in', time: '2026-05-29 09:00:00', location: 'Headquarters Office' },
        { id: 2, employeeId: 'EMP-002', employee: 'Jane Smith', type: 'clock-in', time: '2026-05-29 09:15:00', location: 'North Logistics Hub' },
        { id: 3, employeeId: 'EMP-003', employee: 'Bob Johnson', type: 'clock-out', time: '2026-05-29 17:30:00', location: 'West Terminal Facility' },
        { id: 4, employeeId: 'EMP-001', employee: 'John Doe', type: 'clock-out', time: '2026-05-29 17:05:00', location: 'Headquarters Office' },
        { id: 5, employeeId: 'EMP-004', employee: 'Alice Vance', type: 'clock-in', time: '2026-05-30 08:45:00', location: 'Headquarters Office' },
        { id: 6, employeeId: 'EMP-002', employee: 'Jane Smith', type: 'clock-out', time: '2026-05-30 16:30:00', location: 'North Logistics Hub' }
    ],

    get filteredRecords() {
        // Step 1: Base Search query and tab segmentation
        let data = this.attendanceRecords.filter(record => {
            const matchesSearch = record.employee.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                  record.employeeId.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                  record.location.toLowerCase().includes(this.searchQuery.toLowerCase());
            
            const matchesTab = this.selectedTab === 'all' || record.type === this.selectedTab;
            
            return matchesSearch && matchesTab;
        });

        // Step 2: Sorting operations
        data.sort((a, b) => {
            let valA = a[this.sortField];
            let valB = b[this.sortField];
            
            if (this.sortField === 'time') {
                return this.sortOrder === 'desc' ? new Date(valB) - new Date(valA) : new Date(valA) - new Date(valB);
            }
            
            if (valA < valB) return this.sortOrder === 'asc' ? -1 : 1;
            if (valA > valB) return this.sortOrder === 'asc' ? 1 : -1;
            return 0;
        });

        return data;
    },

    get pagedRecords() {
        const start = (this.currentPage - 1) * this.perPage;
        return this.filteredRecords.slice(start, start + this.perPage);
    },

    get totalPages() {
        return Math.ceil(this.filteredRecords.length / this.perPage) || 1;
    },

    sortBy(field) {
        if (this.sortField === field) {
            this.sortOrder = this.sortOrder === 'desc' ? 'asc' : 'desc';
        } else {
            this.sortField = field;
            this.sortOrder = 'desc';
        }
        this.currentPage = 1; // Reset window index boundaries
    },

    changeTab(tab) {
        this.selectedTab = tab;
        this.currentPage = 1;
    },

    exportCSV() {
        alert('Compiling operational log metrics registry... Downloading CSV telemetry packet.');
        // Hook link execution redirect route parameter here: window.location.href = route_url('/admin-dashboard/attendance/export');
    }
}" x-init="window.themeManager.initTheme()">
    
    <div style="display: flex; min-height: 100vh;">
        
        <aside class="app-sidebar" :style="{ width: sidebarOpen ? '280px' : '0px' }">
            <div class="p-4 border-bottom border-secondary border-opacity-25" style="min-width: 280px;">
                <h4 class="fw-bold mb-1" style="color: #FFFFFF;"><i class="bi bi-clock-history me-2"></i>Clock-It</h4>
                <p class="small text-white text-opacity-50 mb-0 uppercase tracking-wider font-monospace">Administrative Panel</p>
            </div>

            <nav class="sidebar-nav mt-4" style="min-width: 280px;">
                <a href="<?= route_url('/admin-dashboard') ?>" class="sidebar-nav-link">
                    <i class="bi bi-grid-1x2-fill fs-5"></i>
                    <span>Dashboard Overview</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/users') ?>" class="sidebar-nav-link">
                    <i class="bi bi-people-fill fs-5"></i>
                    <span>User Management</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/attendance') ?>" class="sidebar-nav-link active">
                    <i class="bi bi-journal-check fs-5"></i>
                    <span>Attendance Log</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/qr-generator') ?>" class="sidebar-nav-link">
                    <i class="bi bi-qr-code fs-5"></i>
                    <span>QR Code Generator</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/settings') ?>" class="sidebar-nav-link">
                    <i class="bi bi-sliders fs-5"></i>
                    <span>System Settings</span>
                </a>
            </nav>

            <button class="sidebar-logout" @click="logoutUser()" type="button" style="min-width: 280px;">
                <i class="bi bi-box-arrow-left fs-5"></i>
                <span>Terminate Session</span>
            </button>
        </aside>

        <div class="main-workspace" :style="{ marginLeft: sidebarOpen ? '280px' : '0px' }">
            
            <header class="app-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="btn btn-sm btn-light border d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 8px;" type="button">
                        <i class="bi bi-list fs-5 text-dark"></i>
                    </button>
                    <h5 class="mb-0 fw-bold" style="color: var(--deep-navy);">Transaction Auditing</h5>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <?php include __DIR__ . '/../partials/theme-toggle.php'; ?>
                    
                    <div class="vr mx-1 opacity-25"></div>
                    
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-secondary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold text-uppercase" style="width: 36px; height: 36px; border-radius: 50%; font-size: 0.85rem; border: 1px solid rgba(9, 60, 93, 0.1);">
                            <?= substr(htmlspecialchars($_SESSION['user_name'] ?? 'A'), 0, 2); ?>
                        </div>
                        <div class="small fw-semibold d-none d-sm-block text-muted">
                            <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin System'); ?>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4 p-md-5 flex-grow-1">
                <div class="container-fluid p-0">
                    
                    <div class="mb-4">
                        <h2 class="fw-bold mb-1" style="color: var(--deep-navy);">Attendance Logs</h2>
                        <p class="text-muted small">Monitor chronological ingress, egress, and localized terminal data packets.</p>
                    </div>

                    <div class="card custom-card p-3 mb-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-8 col-lg-9">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input 
                                        type="text" 
                                        class="form-control border-start-0 ps-0" 
                                        placeholder="Query entries via employee identity, index ID, or device terminal location..."
                                        x-model="searchQuery"
                                        @input="currentPage = 1"
                                    >
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3 text-md-end">
                                <button class="btn btn-brand-outline w-100 py-2 d-flex align-items-center justify-content-center gap-2" @click="exportCSV()" type="button">
                                    <i class="bi bi-download"></i>
                                    <span>Export CSV Packet</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card">
                        
                        <div class="nav-tabs-custom">
                            <button class="tab-link-custom" :class="selectedTab === 'all' && 'active'" @click="changeTab('all')" type="button">
                                <i class="bi bi-collection me-1"></i> All Transactions
                            </button>
                            <button class="tab-link-custom" :class="selectedTab === 'clock-in' && 'active'" @click="changeTab('clock-in')" type="button">
                                <i class="bi bi-box-arrow-in-right text-success me-1"></i> Ingress Logs (In)
                            </button>
                            <button class="tab-link-custom" :class="selectedTab === 'clock-out' && 'active'" @click="changeTab('clock-out')" type="button">
                                <i class="bi bi-box-arrow-left text-warning me-1"></i> Egress Logs (Out)
                            </button>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="min-width: 900px;">
                                    <thead class="bg-light table-light">
                                        <tr>
                                            <th class="ps-4 py-3 sortable-header text-uppercase font-monospace small tracking-wider text-muted" @click="sortBy('employeeId')">
                                                Employee ID <i class="bi small" :class="sortField === 'employeeId' ? (sortOrder === 'desc' ? 'bi-sort-down-alt' : 'bi-sort-up') : 'bi-hash'"></i>
                                            </th>
                                            <th class="py-3 sortable-header text-uppercase font-monospace small tracking-wider text-muted" @click="sortBy('employee')">
                                                Staff Resource <i class="bi small" :class="sortField === 'employee' ? (sortOrder === 'desc' ? 'bi-sort-down-alt' : 'bi-sort-up') : 'bi-arrows-expand'"></i>
                                            </th>
                                            <th class="py-3 text-uppercase font-monospace small tracking-wider text-muted">Handshake Metric</th>
                                            <th class="py-3 sortable-header text-uppercase font-monospace small tracking-wider text-muted" @click="sortBy('location')">
                                                Terminal Terminal Node <i class="bi small" :class="sortField === 'location' ? (sortOrder === 'desc' ? 'bi-sort-down-alt' : 'bi-sort-up') : 'bi-geo-alt'"></i>
                                            </th>
                                            <th class="py-3 sortable-header text-uppercase font-monospace small tracking-wider text-muted" @click="sortBy('time')">
                                                Timestamp <i class="bi small" :class="sortField === 'time' ? (sortOrder === 'desc' ? 'bi-sort-down-alt' : 'bi-sort-up') : 'bi-clock'"></i>
                                            </th>
                                            <th class="pe-4 py-3 text-end text-uppercase font-monospace small tracking-wider text-muted">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="record in pagedRecords" :key="record.id">
                                            <tr>
                                                <td class="ps-4 font-monospace fw-semibold text-muted" x-text="record.employeeId"></td>
                                                <td class="fw-bold" style="color: var(--deep-navy);" x-text="record.employee"></td>
                                                <td>
                                                    <span class="badge px-2.5 py-1.5 rounded-2 font-monospace fw-bold"
                                                          :class="record.type === 'clock-in' ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-dark'"
                                                          x-text="record.type.toUpperCase().replace('-', ' ')">
                                                    </span>
                                                </td>
                                                <td class="text-secondary small">
                                                    <i class="bi bi-geo-alt me-1 text-muted"></i><span x-text="record.location"></span>
                                                </td>
                                                <td class="font-monospace text-muted small" x-text="new Date(record.time.replace(/-/g, '/')).toLocaleString()"></td>
                                                <td class="pe-4 text-end">
                                                    <button class="btn btn-sm btn-light border px-2.5 py-1.5 small fw-semibold" type="button">
                                                        <i class="bi bi-eye"></i> Audit
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="filteredRecords.length === 0" x-cloak>
                                            <td colspan="6" class="text-center text-muted py-5 font-monospace">
                                                <i class="bi bi-folder-x text-muted display-6 mb-2 d-block"></i>
                                                Zero logistical tracking events reconcile with provided metric queries.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between p-4 border-top gap-3" x-show="filteredRecords.length > 0" x-cloak>
                                <div class="small text-muted font-monospace">
                                    Showing object array slice <span class="fw-bold text-dark" x-text="Math.min((currentPage - 1) * perPage + 1, filteredRecords.length)"></span> to 
                                    <span class="fw-bold text-dark" x-text="Math.min(currentPage * perPage, filteredRecords.length)"></span> of 
                                    <span class="fw-bold text-dark" x-text="filteredRecords.length"></span> transaction indices.
                                </div>
                                <nav aria-label="Log Registry Pagination Configuration Selector">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item" :class="currentPage === 1 && 'disabled'">
                                            <button class="page-link font-monospace py-2 px-3 fw-bold" @click="currentPage--" :disabled="currentPage === 1" type="button">
                                                <i class="bi bi-chevron-left"></i> Previous
                                            </button>
                                        </li>
                                        <template x-for="page in totalPages" :key="page">
                                            <li class="page-item" :class="currentPage === page && 'active'">
                                                <button class="page-link font-monospace py-2 px-3 fw-bold" @click="currentPage = page" x-text="page" type="button"></button>
                                            </li>
                                        </template>
                                        <li class="page-item" :class="currentPage === totalPages && 'disabled'">
                                            <button class="page-link font-monospace py-2 px-3 fw-bold" @click="currentPage++" :disabled="currentPage === totalPages" type="button">
                                                Next <i class="bi bi-chevron-right"></i>
                                            </button>
                                        </li>
                                    </ul>
                                </nav>
                            </div>

                        </div>
                    </div>
                    
                </div>
            </main>
        </div>
    </div>

    <script>
        function logoutUser() {
            if (confirm('Are you absolutely certain you want to terminate the active session context?')) {
                window.location.href = '<?= route_url('/logout') ?>';
            }
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>