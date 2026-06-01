<?php
/**
 * QR Code Generator Page
 * Admin page for creating and managing QR codes
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
    <title>QR Code Generator - Clock-It</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>
</head>
<body x-data="{ 
    sidebarOpen: true,
    showCreateModal: false,
    qrCodes: [
        { id: 1, label: 'Main Entrance', type: 'clock-in', location: 'Front Door', status: 'active', created: '2026-01-15' },
        { id: 2, label: 'Office Exit', type: 'clock-out', location: 'Side Entrance', status: 'active', created: '2026-01-15' }
    ],
    newQR: {
        label: '',
        type: 'clock-in',
        location: '',
        expiresIn: ''
    },
    generateQR() {
        // Code generation logic
        alert('QR Code generated: ' + this.newQR.label);
        this.showCreateModal = false;
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4>Active QR Codes</h4>
                        <button class="btn btn-success" @click="showCreateModal = true" type="button">
                            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Create QR Code
                        </button>
                    </div>

                    <!-- QR Codes Grid -->
                    <div class="row">
                        <template x-for="qr in qrCodes" :key="qr.id">
                            <div class="col-md-6 mb-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title" x-text="qr.label"></h5>
                                        <p class="text-muted small mb-2">
                                            <strong>Type:</strong> <span x-text="qr.type"></span>
                                        </p>
                                        <p class="text-muted small mb-2">
                                            <strong>Location:</strong> <span x-text="qr.location"></span>
                                        </p>
                                        <p class="text-muted small mb-3">
                                            <strong>Created:</strong> <span x-text="qr.created"></span>
                                        </p>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-download me-1" aria-hidden="true"></i>Download</button>
                                            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-copy me-1" aria-hidden="true"></i>Duplicate</button>
                                            <button class="btn btn-sm btn-outline-danger">Revoke</button>
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
