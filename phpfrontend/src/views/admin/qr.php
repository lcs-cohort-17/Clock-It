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
    <title>QR Code Generator - Clock-It</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
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

        /* Fixed Navigation Sidebar Container Layout */
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

        /* Core Component Workspace Frame */
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

        /* System Grid Visual Cards */
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
            box-shadow: 0 8px 25px rgba(9, 60, 93, 0.06);
        }

        /* Interactive Elements Custom Action Hooks */
        .btn-brand-solid {
            background-color: var(--mid-blue);
            border: 1px solid var(--mid-blue);
            color: #FFFFFF;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-brand-solid:hover {
            background-color: var(--deep-navy);
            border-color: var(--deep-navy);
            color: #FFFFFF;
        }

        .qr-canvas-container {
            background-color: #F8F9FA;
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed rgba(9, 60, 93, 0.1);
        }

        /* Custom Alpine Modal Target Overlays */
        .modal-blur-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(9, 60, 93, 0.4);
            backdrop-filter: blur(4px);
            z-index: 1060;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Dark Mode CSS Variables Configuration Matrix */
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

            .qr-canvas-container {
                background-color: #0B131A;
                border-color: rgba(255, 255, 255, 0.1);
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
            
            .modal-content {
                background-color: #121F2B;
                border: 1px solid rgba(255, 255, 255, 0.1);
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
    showCreateModal: false,
    qrCodes: [
        { id: 1, label: 'Main Warehouse Entrance', type: 'clock-in', location: 'Front Gate Alpha', status: 'active', created: '2026-01-15', token: 'CLK-IN-W1-9982', expiresIn: 'Never' },
        { id: 2, label: 'Corporate Office Exit', type: 'clock-out', location: 'Lobby Glass Doors', status: 'active', created: '2026-03-22', token: 'CLK-OUT-C2-4412', expiresIn: 'Never' },
        { id: 3, label: 'Temporary External Audit Hub', type: 'clock-in', location: 'Conference Hall B', status: 'expired', created: '2026-05-01', token: 'CLK-IN-EX-3321', expiresIn: '2026-05-15' }
    ],
    newQR: {
        label: '',
        type: 'clock-in',
        location: '',
        expiresIn: ''
    },

    generateQR() {
        if (!this.newQR.label || !this.newQR.location) return;
        
        const generatedId = Date.now();
        const expirationValue = this.newQR.expiresIn ? this.newQR.expiresIn : 'Never';
        const pseudorandomToken = 'CLK-' + this.newQR.type.toUpperCase() + '-' + Math.floor(1000 + Math.random() * 9000);

        this.qrCodes.unshift({
            id: generatedId,
            label: this.newQR.label,
            type: this.newQR.type,
            location: this.newQR.location,
            status: 'active',
            created: new Date().toISOString().split('T')[0],
            token: pseudorandomToken,
            expiresIn: expirationValue
        });

        // Reset inputs and detach frame context modal
        this.newQR = { label: '', type: 'clock-in', location: '', expiresIn: '' };
        this.showCreateModal = false;
    },

    downloadQR(qrItem) {
        const canvasElement = document.getElementById('canvas-qr-' + qrItem.id);
        if (!canvasElement) return;
        
        const dataURL = canvasElement.toDataURL('image/png');
        const downloadAnchor = document.createElement('a');
        downloadAnchor.href = dataURL;
        downloadAnchor.download = 'QR_' + qrItem.label.replace(/\s+/g, '_') + '.png';
        document.body.appendChild(downloadAnchor);
        downloadAnchor.click();
        document.body.removeChild(downloadAnchor);
    },

    duplicateQR(qrItem) {
        const duplicateInstance = Object.assign({}, qrItem, {
            id: Date.now(),
            label: qrItem.label + ' (Copy)',
            created: new Date().toISOString().split('T')[0],
            token: 'CLK-' + qrItem.type.toUpperCase() + '-' + Math.floor(1000 + Math.random() * 9000)
        });
        this.qrCodes.unshift(duplicateInstance);
    },

    revokeQR(qrItem) {
        if (confirm('Are you certain you want to revoke authentication permission for: ' + qrItem.label + '? Devices scanning this asset will be rejected.')) {
            qrItem.status = 'revoked';
        }
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

                <a href="<?= route_url('/admin-dashboard/qr-generator') ?>" class="sidebar-nav-link active">
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
                    <h5 class="mb-0 fw-bold" style="color: var(--deep-navy);">Dynamic Handshake Geofences</h5>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <?php include __DIR__ . '/../partials/theme-toggle.php'; ?>
                    <div class="vr mx-1 opacity-25"></div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-secondary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold text-uppercase" style="width: 36px; height: 36px; border-radius: 50%; font-size: 0.85rem; border: 1px solid rgba(9, 60, 93, 0.1);">
                            <?= substr(htmlspecialchars($_SESSION['user_name'] ?? 'A'), 0, 2); ?>
                        </div>
                        <div class="small fw-semibold d-none d-sm-block text-muted">Admin System</div>
                    </div>
                </div>
            </header>

            <main class="p-4 p-md-5 flex-grow-1">
                <div class="container-fluid p-0">
                    
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-5">
                        <div>
                            <h2 class="fw-bold mb-1" style="color: var(--deep-navy);">QR Core Deployment Grid</h2>
                            <p class="text-muted small mb-0">Generate physical tracking stations with distinct localized token strings and lifecycle windows.</p>
                        </div>
                        <button class="btn btn-brand-solid px-4 py-2.5 d-inline-flex align-items-center gap-2 shadow-sm" @click="showCreateModal = true" type="button">
                            <i class="bi bi-plus-lg fs-5"></i> Sprout New Static Terminal
                        </button>
                    </div>

                    <div class="row">
                        <template x-for="qr in qrCodes" :key="qr.id">
                            <div class="col-xl-6 mb-4">
                                <div class="card custom-card h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex flex-column flex-sm-row gap-4 align-items-start">
                                            
                                            <div class="qr-canvas-container flex-shrink-0 mx-auto mx-sm-0">
                                                <canvas :id="'canvas-qr-' + qr.id" x-init="
                                                    $nextTick(() => {
                                                        QRCode.toCanvas(document.getElementById('canvas-qr-' + qr.id), qr.token, {
                                                            width: 140,
                                                            margin: 1,
                                                            color: {
                                                                dark: '#093C5D',
                                                                light: '#FFFFFF'
                                                            }
                                                        });
                                                    });
                                                "></canvas>
                                            </div>

                                            <div class="flex-grow-1 w-100">
                                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                                    <h5 class="fw-bold mb-0 text-truncate" style="max-width: 220px;" x-text="qr.label"></h5>
                                                    
                                                    <span class="badge px-2.5 py-1.5 rounded-pill font-monospace tracking-wide" 
                                                          :class="{
                                                              'bg-success bg-opacity-10 text-success': qr.status === 'active',
                                                              'bg-danger bg-opacity-10 text-danger': qr.status === 'revoked',
                                                              'bg-warning bg-opacity-10 text-warning': qr.status === 'expired'
                                                          }" 
                                                          x-text="qr.status.toUpperCase()">
                                                    </span>
                                                </div>

                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <span class="small font-monospace text-muted d-block">GATEWAY TYPE</span>
                                                        <span class="small fw-bold text-uppercase d-inline-flex align-items-center gap-1" :class="qr.type === 'clock-in' ? 'text-success' : 'text-primary'">
                                                            <i :class="qr.type === 'clock-in' ? 'bi bi-box-arrow-in-right' : 'bi bi-box-arrow-left'"></i>
                                                            <span x-text="qr.type"></span>
                                                        </span>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="small font-monospace text-muted d-block">PHYSICAL ZONE</span>
                                                        <span class="small fw-semibold text-dark text-truncate d-block" x-text="qr.location"></span>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="small font-monospace text-muted d-block">DEPLOYED DATE</span>
                                                        <span class="small font-monospace fw-medium text-secondary" x-text="qr.created"></span>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="small font-monospace text-muted d-block">LIFECYCLE HORIZON</span>
                                                        <span class="small font-monospace fw-medium" :class="qr.expiresIn !== 'Never' && qr.status === 'active' ? 'text-warning fw-bold' : 'text-muted'" x-text="qr.expiresIn"></span>
                                                    </div>
                                                </div>

                                                <hr class="my-2 opacity-10">

                                                <div class="d-flex flex-wrap gap-2 pt-2">
                                                    <button class="btn btn-sm btn-light border flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1.5 py-2 rounded-3" 
                                                            @click="downloadQR(qr)" :disabled="qr.status === 'revoked'">
                                                        <i class="bi bi-download"></i> Extract
                                                    </button>
                                                    <button class="btn btn-sm btn-light border flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1.5 py-2 rounded-3" 
                                                            @click="duplicateQR(qr)">
                                                        <i class="bi bi-copy"></i> Clone
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1.5 py-2 rounded-3" 
                                                            @click="revokeQR(qr)" :disabled="qr.status !== 'active'">
                                                        <i class="bi bi-shield-x"></i> Revoke
                                                    </button>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal-blur-overlay" x-show="showCreateModal" x-transition x-cloak>
        <div class="modal-dialog modal-dialog-centered w-100" style="max-width: 550px; padding: 1.5rem;">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="modal-header px-4 py-3 bg-light border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-qr-code-scan text-primary"></i> Create Deployment Token
                    </h5>
                    <button type="button" class="btn-close" @click="showCreateModal = false"></button>
                </div>
                <form @submit.prevent="generateQR()">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small font-monospace text-muted text-uppercase fw-bold">Terminal Reference Label</label>
                            <input type="text" class="form-control py-2.5 rounded-3" placeholder="e.g., South Logistics Turnstile" x-model="newQR.label" required>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small font-monospace text-muted text-uppercase fw-bold">Directional Handshake Vector</label>
                                <select class="form-select py-2.5 rounded-3" x-model="newQR.type">
                                    <option value="clock-in">Inbound (Clock-In)</option>
                                    <option value="clock-out">Outbound (Clock-Out)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small font-monospace text-muted text-uppercase fw-bold">Spatial Location Zone</label>
                                <input type="text" class="form-control py-2.5 rounded-3" placeholder="e.g., Building C Floor 1" x-model="newQR.location" required>
                            </div>
                        </div>

                        <div>
                            <label class="form-label small font-monospace text-muted text-uppercase fw-bold">Lifecycle Expiration Threshold (Optional)</label>
                            <input type="date" class="form-control py-2.5 rounded-3 font-monospace" min="<?= date('Y-m-d'); ?>" x-model="newQR.expiresIn">
                            <div class="form-text small text-muted">Leave empty to initialize an unbounded permanent asset verification matrix array loop.</div>
                        </div>
                    </div>
                    <div class="modal-footer px-4 py-3 bg-light border-top d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-light border px-4 py-2 rounded-3" @click="showCreateModal = false">Cancel</button>
                        <button type="submit" class="btn btn-brand-solid px-4 py-2.5 rounded-3">Compile Token Structure</button>
                    </div>
                </form>
            </div>
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