<?php
/**
 * Scan QR Code Page
 * Staff QR scanning interface
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
    <title>Scan QR Code - Clock-It</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.2.11/minified/html5-qrcode.min.js"></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>
</head>
<body x-data="{
    scannerOpen: false,
    scanResult: '',
    scanError: '',
    manualInput: '',
    lastScan: null,
    scanner: null,
    scannerStarting: false,

    startScanner: async function () {
        if (this.scannerOpen || this.scannerStarting) return;

        this.scanError = '';
        this.scanResult = '';
        this.scannerStarting = true;
        this.scannerOpen = true;

        await this.$nextTick();

        try {
            if (!this.scanner) {
                this.scanner = new Html5Qrcode('reader');
            }

            const cameras = await Html5Qrcode.getCameras();

            if (!cameras || cameras.length === 0) {
                throw new Error('No camera found');
            }

            await this.scanner.start(
                cameras[0].id,
                {
                    fps: 10,
                    qrbox: 220
                },
                (decodedText) => this.handleScan(decodedText)
            );

        } catch (err) {
            console.error(err);
            this.scanError = 'Camera blocked or not available.';
            this.scannerOpen = false;
            this.scanner = null;
        }

        this.scannerStarting = false;
    },

    stopScanner: async function () {
        if (this.scanner) {
            try {
                await this.scanner.stop();
                await this.scanner.clear();
            } catch (e) {}
        }

        this.scanner = null;
        this.scannerOpen = false;
        this.scannerStarting = false;
    },

    handleScan: function (code) {
        const scanType = window.qrUtils.getScanType(code);

        if (!scanType) {
            this.scanError = 'Invalid QR code';
            return;
        }

        window.qrUtils.recordScan(scanType);

        this.scanResult = code.trim().toUpperCase();
        this.lastScan = new Date().toLocaleString();
        this.scanError = '';

        this.stopScanner();
    },

    handleManualScan: function () {
        if (this.manualInput) {
            this.handleScan(this.manualInput);
            this.manualInput = '';
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
                    <h2 class="mb-4">Scan QR Code</h2>

                    <!-- Scanner View -->
                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">QR Scanner</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Camera Feed -->
                                    <div x-show="scannerOpen" id="reader" style="width: 100%; min-height: 300px; border-radius: 0.5rem; overflow: hidden; margin-bottom: 1rem;"></div>

                                    <!-- Scanner Controls -->
                                    <div class="d-flex gap-2 mb-3">
                                        <button class="btn btn-primary flex-grow-1" @click="startScanner()" x-show="!scannerOpen" :disabled="scannerStarting" type="button">
                                            <i class="bi bi-camera-video me-1" aria-hidden="true"></i><span x-text="scannerStarting ? 'Starting...' : 'Start Scanner'"></span>
                                        </button>
                                        <button class="btn btn-secondary flex-grow-1" @click="stopScanner()" x-show="scannerOpen" type="button">
                                            <i class="bi bi-stop-circle me-1" aria-hidden="true"></i>Stop Scanner
                                        </button>
                                    </div>

                                    <!-- Manual Input -->
                                    <div class="mb-3">
                                        <label class="form-label small">Or enter code manually:</label>
                                        <div class="input-group">
                                            <input 
                                                type="text" 
                                                class="form-control" 
                                                placeholder="CLOCK_IN or CLOCK_OUT"
                                                x-model="manualInput"
                                                @keyup.enter="handleManualScan()"
                                            >
                                            <button class="btn btn-outline-secondary" @click="handleManualScan()" type="button">Submit</button>
                                        </div>
                                    </div>
                        <div class="col-lg-6">
                            <!-- Scan Result -->
                            <div class="card border-0 shadow-sm" x-show="scanResult">
                                <div class="card-header" :class="scanResult.includes('CLOCK_IN') ? 'bg-success' : 'bg-warning'" style="color: white;">
                                    <h5 class="mb-0">
                                        <i class="bi" :class="scanResult.includes('CLOCK_IN') ? 'bi-check-circle' : 'bi-stop-circle'" aria-hidden="true"></i>
                                        <span x-text="scanResult.includes('CLOCK_IN') ? 'Clock In Recorded' : 'Clock Out Recorded'"></span>
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <p class="lead mb-2" x-text="scanResult"></p>
                                    <p class="text-muted small" x-text="'Recorded at: ' + lastScan"></p>
                                    <button class="btn btn-primary" @click="scanResult = ''; lastScan = null;" type="button">
                                        <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Done
                                    </button>
                                </div>
                            </div>

                            <!-- Error Message -->
                            <div class="alert alert-danger" x-show="scanError" x-text="scanError"></div>

                            <!-- Instructions -->
                            <div class="alert alert-info" x-show="!scanResult && !scanError">
                                <h6 class="alert-heading"><i class="bi bi-info-circle me-1" aria-hidden="true"></i>Instructions</h6>
                                <ul class="mb-0 small">
                                    <li>Click "Start Scanner" to activate your device camera</li>
                                    <li>Position the QR code in front of your camera</li>
                                    <li>The scanner will automatically detect and process the code</li>
                                    <li>Use demo buttons if you don't have a QR code available</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
