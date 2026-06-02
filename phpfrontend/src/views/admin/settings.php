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
    <title>Admin Settings - Clock-It</title>
    
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
            margin-bottom: 1.75rem;
        }

        .custom-card .card-header {
            background-color: rgba(9, 60, 93, 0.01) !important;
            border-bottom: 1px solid rgba(9, 60, 93, 0.06);
            padding: 1.2rem 1.5rem;
            color: var(--deep-navy);
            font-weight: 700;
        }

        /* Custom Global Action Buttons */
        .btn-brand-solid {
            background-color: var(--mid-blue);
            border: 1px solid var(--mid-blue);
            color: #FFFFFF;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(59, 117, 151, 0.25);
        }
        .btn-brand-solid:hover {
            background-color: var(--deep-navy);
            border-color: var(--deep-navy);
            color: #FFFFFF;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(9, 60, 93, 0.3);
        }

        /* Input Controls Extensions */
        .form-control:focus, .form-select:focus {
            border-color: var(--mid-blue);
            box-shadow: 0 0 0 0.25rem rgba(59, 117, 151, 0.15);
        }

        .form-check-input:checked {
            background-color: var(--mid-blue);
            border-color: var(--mid-blue);
        }

        /* Contextual Metric Badges */
        .settings-icon-frame {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background-color: rgba(59, 117, 151, 0.08);
            color: var(--mid-blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
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

            .settings-icon-frame {
                background-color: rgba(111, 170, 208, 0.1);
                color: var(--mid-blue);
            }

            .form-control, .form-select {
                background-color: #0B131A;
                border-color: rgba(255, 255, 255, 0.1);
                color: #FFFFFF;
            }

            .form-control:focus, .form-select:focus {
                background-color: #0B131A;
                color: #FFFFFF;
            }

            .text-muted {
                color: #A3B8CC !important;
            }
            
            .bg-light {
                background-color: rgba(255, 255, 255, 0.02) !important;
            }
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data="{ 
    sidebarOpen: true,
    isSaving: false,
    settings: {
        companyName: 'Acme Corp Corporate Logistics',
        timezone: 'America/New_York',
        currency: 'USD',
        dataRetentionDays: 90,
        purgeExpiredLogs: true,
        maxClockInDistance: 100,
        requireGPS: true,
        enableWifiLock: false,
        authorizedBSSID: '',
        googleSheetsEnabled: false,
        spreadsheetId: '',
        syncInterval: 'realtime'
    },

    saveApplicationSettings() {
        this.isSaving = true;
        
        // Emulated persistence handshake pipeline
        setTimeout(() => {
            this.isSaving = false;
            alert('System runtime telemetry states saved successfully.');
        }, 1200);
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

                <a href="<?= route_url('/admin-dashboard/attendance') ?>" class="sidebar-nav-link">
                    <i class="bi bi-journal-check fs-5"></i>
                    <span>Attendance Log</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/qr-generator') ?>" class="sidebar-nav-link">
                    <i class="bi bi-qr-code fs-5"></i>
                    <span>QR Code Generator</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/settings') ?>" class="sidebar-nav-link active">
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
                    <h5 class="mb-0 fw-bold" style="color: var(--deep-navy);">System Configurations</h5>
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
                        <h2 class="fw-bold mb-1" style="color: var(--deep-navy);">Preferences Matrix</h2>
                        <p class="text-muted small">Configure deployment runtime contexts, geo-fencing barriers, and database persistence parameters.</p>
                    </div>

                    <form @submit.prevent="saveApplicationSettings()">
                        <div class="row">
                            <div class="col-lg-8">
                                
                                <div class="card custom-card">
                                    <div class="card-header d-flex align-items-center gap-3">
                                        <div class="settings-icon-frame">
                                            <i class="bi bi-building fs-5"></i>
                                        </div>
                                        <h5 class="mb-0 fw-bold">Company Profile Settings</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label small fw-bold font-monospace text-muted text-uppercase">Registered Corporate Entity Name</label>
                                                <input type="text" class="form-control py-2.5 rounded-3" x-model="settings.companyName" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold font-monospace text-muted text-uppercase">System Core Localization Timezone</label>
                                                <select class="form-select py-2.5 rounded-3" x-model="settings.timezone">
                                                    <option value="America/New_York">Eastern Standard Time (EST)</option>
                                                    <option value="Europe/London">Greenwich Mean Time (GMT)</option>
                                                    <option value="Africa/Johannesburg">South African Standard Time (SAST)</option>
                                                    <option value="Asia/Tokyo">Japan Standard Time (JST)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold font-monospace text-muted text-uppercase">Base Operating Ledger Currency</label>
                                                <select class="form-select py-2.5 rounded-3" x-model="settings.currency">
                                                    <option value="USD">United States Dollar ($)</option>
                                                    <option value="EUR">Euro (€)</option>
                                                    <option value="GBP">Great British Pound (£)</option>
                                                    <option value="ZAR">South African Rand (R)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card custom-card">
                                    <div class="card-header d-flex align-items-center gap-3">
                                        <div class="settings-icon-frame">
                                            <i class="bi bi-geo-alt fs-5"></i>
                                        </div>
                                        <h5 class="mb-0 fw-bold">Location-Based Geofencing Configuration</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-4">
                                            <label class="form-label small fw-bold font-monospace text-muted text-uppercase d-flex justify-content-between">
                                                <span>Maximum Handshake Radius Variance (Meters)</span>
                                                <span class="text-primary fw-bold font-monospace" x-text="settings.maxClockInDistance + 'm'"></span>
                                            </label>
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="small text-muted">10m</span>
                                                <input type="range" class="form-range" min="10" max="500" step="5" x-model="settings.maxClockInDistance">
                                                <span class="small text-muted">500m</span>
                                            </div>
                                            <div class="form-text small text-muted mt-1">Defines the maximum