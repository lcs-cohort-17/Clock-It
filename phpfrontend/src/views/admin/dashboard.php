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
    <title>Admin Dashboard - Clock-It</title>
    
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

            .table {
                --bs-table-bg: transparent;
                color: var(--deep-navy);
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
    stats: {
        totalEmployees: 45,
        presentToday: 38,
        absentToday: 7,
        totalScans: 342
    },
    recentEvents: [
        { id: 1, name: 'Marcus Vance', action: 'CLOCK_IN', platform: 'QR Gateway Alpha', time: '08:42 AM' },
        { id: 2, name: 'Sarah Jenkins', action: 'CLOCK_IN', platform: 'Manual Panel Override', time: '08:31 AM' },
        { id: 3, name: 'David Miller', action: 'CLOCK_OUT', platform: 'QR Gateway Beta', time: '04:15 PM' }
    ]
}" x-init="window.themeManager.initTheme()">
    
    <div style="display: flex; min-height: 100vh;">
        
        <aside class="app-sidebar" :style="{ width: sidebarOpen ? '280px' : '0px' }">
            <div class="p-4 border-bottom border-secondary border-opacity-25" style="min-width: 280px;">
                <h4 class="fw-bold mb-1" style="color: #FFFFFF;"><i class="bi bi-clock-history me-2"></i>Clock-It</h4>
                <p class="small text-white text-opacity-50 mb-0 uppercase tracking-wider font-monospace">Administrative Panel</p>
            </div>

            <nav class="sidebar-nav mt-4" style="min-width: 280px;">
                <a href="<?= route_url('/admin-dashboard') ?>" class="sidebar-nav-link active">
                    <i class="bi bi-grid-1x2-fill fs-5"></i>
                    <span>Dashboard Overview</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/users') ?>" class="sidebar-nav-link">
                    <i class="bi bi-people-fill fs-5"></i>
                    <span>User Management</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/attendance') ?>" class="sidebar-nav-link">
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
                    <h5 class="mb-0 fw-bold" style="color: var(--deep-navy);">Admin Dashboard</h5>
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
                        <h2 class="fw-bold mb-1" style="color: var(--deep-navy);">Overview Statistics</h2>
                        <p class="text-muted small">Real-time attendance operational framework telemetry logs.</p>
                    </div>

                    <div class="row g-4 mb-5">
                        
                        <div class="col-sm-6 col-xl-3">
                            <div class="card custom-card">
                                <div class="card-body d-flex align-items-center p-4">
                                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4 me-3">
                                        <i class="bi bi-building fs-3" style="color: var(--mid-blue) !important;"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted small mb-1 fw-semibold text-uppercase tracking-wider">Total Employees</p>
                                        <h3 class="fw-bold mb-0" x-text="stats.totalEmployees" style="color: var(--deep-navy);"></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card custom-card">
                                <div class="card-body d-flex align-items-center p-4">
                                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-4 me-3">
                                        <i class="bi bi-person-check-fill fs-3" style="color: var(--olive-green) !important;"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted small mb-1 fw-semibold text-uppercase tracking-wider">Present Today</p>
                                        <h3 class="fw-bold mb-0" x-text="stats.presentToday" style="color: var(--olive-green);"></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card custom-card">
                                <div class="card-body d-flex align-items-center p-4">
                                    <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-4 me-3">
                                        <i class="bi bi-person-dash-fill fs-3"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted small mb-1 fw-semibold text-uppercase tracking-wider">Absent Today</p>
                                        <h3 class="fw-bold mb-0 text-danger" x-text="stats.absentToday"></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card custom-card">
                                <div class="card-body d-flex align-items-center p-4">
                                    <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-4 me-3">
                                        <i class="bi bi-qr-code-scan fs-3" style="color: var(--mid-blue) !important;"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted small mb-1 fw-semibold text-uppercase tracking-wider">Total Scans</p>
                                        <h3 class="fw-bold mb-0" x-text="stats.totalScans" style="color: var(--deep-navy);"></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 fw-bold fs-6"><i class="bi bi-activity me-2"></i>Recent Activity Log</h5>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 small fw-semibold">Live System Active</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="min-width: 600px;">
                                    <thead class="bg-light table-light">
                                        <tr>
                                            <th class="ps-4 py-3 text-uppercase font-monospace small tracking-wider text-muted">Employee</th>
                                            <th class="py-3 text-uppercase font-monospace small tracking-wider text-muted">Action</th>
                                            <th class="py-3 text-uppercase font-monospace small tracking-wider text-muted">Gateway Platform</th>
                                            <th class="pe-4 py-3 text-end text-uppercase font-monospace small tracking-wider text-muted">Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="event in recentEvents" :key="event.id">
                                            <tr>
                                                <td class="ps-4 fw-bold" style="color: var(--deep-navy);" x-text="event.name"></td>
                                                <td>
                                                    <span class="badge px-2.5 py-1.5 rounded-2 font-monospace fw-bold"
                                                          :class="event.action === 'CLOCK_IN' ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-dark'"
                                                          x-text="event.action">
                                                    </span>
                                                </td>
                                                <td class="text-muted small" x-text="event.platform"></td>
                                                <td class="pe-4 text-end font-monospace text-muted small" x-text="event.time"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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