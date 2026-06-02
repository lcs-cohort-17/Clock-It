<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

// If not a staff member, redirect them to login or their admin panel
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'staff') {
    // Elegant fallback: If an admin accidentally accesses a staff link, send them back to admin base
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header('Location: ' . route_url('/admin-dashboard'));
        exit;
    }
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.2.11/minified/html5-qrcode.min.js"></script>
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
            overflow-x: hidden;
        }

        /* Workspace Content Offsets for Fixed Sidebar */
        .main-workspace {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-left: 280px; /* Layout sync with fixed sidebar frame */
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .main-workspace {
                margin-left: 0;
            }
        }

        /* Container Cards */
        .custom-card {
            background-color: #FFFFFF;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(9, 60, 93, 0.04);
            overflow: hidden;
        }

        .custom-card .card-header {
            background-color: rgba(9, 60, 93, 0.02) !important;
            border-bottom: 1px solid rgba(9, 60, 93, 0.06);
            padding: 1.2rem 1.5rem;
            color: var(--deep-navy);
            font-weight: 700;
        }

        .custom-card .card-body {
            padding: 1.5rem;
        }

        /* Form Layout & Control Elements */
        .form-label {
            color: var(--mid-blue);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .custom-input {
            border: 1px solid rgba(9, 60, 93, 0.15);
            border-radius: 10px;
            padding: 0.6rem 1rem;
            color: var(--deep-navy);
            background-color: #FFFFFF;
            transition: all 0.2s ease;
        }

        .custom-input:focus {
            border-color: var(--mid-blue);
            box-shadow: 0 0 0 3px rgba(59, 117, 151, 0.15);
            outline: none;
        }

        /* Action Buttons */
        .btn-brand-primary {
            background-color: var(--deep-navy);
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-brand-primary:hover {
            background-color: var(--mid-blue);
            color: #FFFFFF;
        }

        .btn-brand-secondary {
            background-color: var(--mid-blue);
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-brand-secondary:hover {
            background-color: var(--deep-navy);
            color: #FFFFFF;
        }

        .btn-brand-outline {
            border: 1px solid var(--mid-blue);
            color: var(--mid-blue);
            background: transparent;
            border-radius: 10px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-brand-outline:hover {
            background-color: rgba(59, 117, 151, 0.08);
            color: var(--deep-navy);
            border-color: var(--deep-navy);
        }

        /* Scanner Frame Custom Override */
        #reader {
            border: 2px dashed rgba(9, 60, 93, 0.2) !important;
            background-color: #000000;
        }
        
        #reader video {
            object-fit: cover !important;
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data="{ 
    scannerOpen: false,
    scanResult: '',
    scanError: '',
    manualInput: '',
    lastScan: null,
    scanner: null,
    
    startScanner() {
        this.scannerOpen = true;
        this.$nextTick(() => {
            if (!this.scanner) {
                this.scanner = new Html5Qrcode('reader');
            }
            this.scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                (decodedText) => this.handleScan(decodedText),
                () => {}
            ).catch(() => {
                this.scanner.start(
                    { facingMode: 'user' },
                    { fps: 10, qrbox: { width: 220, height: 220 } },
                    (decodedText) => this.handleScan(decodedText),
                    () => {}
                );
            });
        });
    },
    
    stopScanner() {
        if (this.scanner) {
            this.scanner.stop().then(() => {
                this.scanner = null;
                this.scannerOpen = false;
            });
        }
    },
    
    handleScan(code) {
        const scanType = window.qrUtils.getScanType(code);
        if (!scanType) {
            this.scanError = 'Invalid QR code schema detected. Please verify operational parameter values.';
            return;
        }
        window.qrUtils.recordScan(scanType);
        this.scanResult = code.trim().toUpperCase();
        this.scanError = '';
        this.lastScan = new Date().toLocaleString();
        this.stopScanner();
    },
    
    handleManualScan() {
        if (this.manualInput) {
            this.handleScan(this.manualInput);
            this.manualInput = '';
        }
    },
    
    demo(code) {
        this.handleScan(code);
    }
}" x-init="window.themeManager.initTheme()"> <div style="display: flex; min-height: 100vh;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-workspace">
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <main class="p-4 p-md-5 flex-grow-1">
                <div class="container-fluid p-0">
                    
                    <div class="mb-4">
                        <h2 class="fw-bold tracking-tight" style="color: var(--deep-navy);">Chronograph QR Gateway</h2>
                        <p class="text-muted small">Initialize hardware optics to parse terminal verification matrix configurations.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <h5 class="mb-0 fw-bold fs-6"><i class="bi bi-camera me-2"></i>Optical Capture Lens</h5>
                                </div>
                                <div class="card-body">
                                    <div x-show="scannerOpen" 
                                         id="reader" 
                                         style="width: 100%; min-height: 320px; border-radius: 12px; overflow: hidden; margin-bottom: 1.2rem;"
                                         x-cloak>
                                    </div>

                                    <div class="d-flex gap-2 mb-4">
                                        <button class="btn btn-brand-primary flex-grow-1" @click="startScanner()" x-show="!scannerOpen" type="button">
                                            <i class="bi bi-qr-code-scan me-2"></i>Initialize Scanner
                                        </button>
                                        <button class="btn btn-brand-secondary flex-grow-1" @click="stopScanner()" x-show="scannerOpen" type="button" x-cloak>
                                            <i class="bi bi-camera-video-off me-2"></i>Terminate Capture
                                        </button>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Manual Verification Parameter</label>
                                        <div class="input-group">
                                            <input 
                                                type="text" 
                                                class="form-control custom-input" 
                                                placeholder="CLOCK_IN / CLOCK_OUT"
                                                x-model="manualInput"
                                                @keyup.enter="handleManualScan()"
                                            >
                                            <button class="btn btn-brand-outline" @click="handleManualScan()" type="button">Process</button>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label d-block mb-2">Sandbox Simulation Rails</label>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <button class="btn btn-sm btn-outline-success w-100 py-2 fw-semibold" style="border-radius: 8px;" @click="demo('CLOCK_IN')" type="button">
                                                    <i class="bi bi-box-arrow-in-right me-1"></i>Simulate In
                                                </button>
                                            </div>
                                            <div class="col-6">
                                                <button class="btn btn-sm btn-outline-warning w-100 py-2 fw-semibold" style="border-radius: 8px;" @click="demo('CLOCK_OUT')" type="button">
                                                    <i class="bi bi-box-arrow-left me-1"></i>Simulate Out
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            
                            <div class="card custom-card mb-4" x-show="scanResult" x-cloak>
                                <div class="card-header" :class="scanResult.includes('CLOCK_IN') ? 'bg-success text-white' : 'bg-warning text-dark'">
                                    <h5 class="mb-0 fw-bold fs-6">
                                        <span x-show="scanResult.includes('CLOCK_IN')"><i class="bi bi-check-circle-fill me-2"></i>Ingress Handshake Authorized</span>
                                        <span x-show="scanResult.includes('CLOCK_OUT')"><i class="bi bi-exclamation-triangle-fill me-2"></i>Egress Handshake Authorized</span>
                                    </h5>
                                </div>
                                <div class="card-body text-center py-4">
                                    <div class="display-6 fw-bold mb-2" style="color: var(--deep-navy);" x-text="scanResult"></div>
                                    <p class="text-muted small mb-4" x-text="'System Timestamp: ' + lastScan"></p>
                                    <button class="btn btn-brand-primary px-4" @click="scanResult = ''; lastScan = null;" type="button">
                                        Dismiss Log Feedback
                                    </button>
                                </div>
                            </div>

                            <div class="alert alert-danger d-flex align-items-center border-0 p-3 mb-4" style="border-radius: 12px;" x-show="scanError" x-cloak>
                                <i class="bi bi-shield-slash-fill fs-4 me-3"></i>
                                <div class="small fw-semibold" x-text="scanError"></div>
                            </div>

                            <div class="card custom-card" x-show="!scanResult && !scanError">
                                <div class="card-header">
                                    <h6 class="mb-0 fw-bold small text-uppercase tracking-wider">Gateway Processing Protocol</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0 text-muted small">
                                        <li class="d-flex align-items-start mb-3">
                                            <i class="bi bi-1-circle-fill text-primary me-3 mt-0.5"></i>
                                            <div>Select <strong>Initialize Scanner</strong> to clear permission blocks and deploy environmental camera matrices.</div>
                                        </li>
                                        <li class="d-flex align-items-start mb-3">
                                            <i class="bi bi-2-circle-fill text-primary me-3 mt-0.5"></i>
                                            <div>Center the dynamic organizational QR badge inside the crosshair target boundary markers.</div>
                                        </li>
                                        <li class="d-flex align-items-start mb-3">
                                            <i class="bi bi-3-circle-fill text-primary me-3 mt-0.5"></i>
                                            <div>The optical capture lens intercepts patterns immediately and commits the chronological log automatically.</div>
                                        </li>
                                        <li class="d-flex align-items-start">
                                            <i class="bi bi-bug-fill text-secondary me-3 mt-0.5"></i>
                                            <div>Utilize local bypass parameter rails to test engine reactivity curves if optics are unavailable.</div>
                                        </li>
                                    </ul>
                                </div>
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