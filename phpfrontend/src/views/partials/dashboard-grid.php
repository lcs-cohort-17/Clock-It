<?php
/**
 * Staff Dashboard Grid Component
 * Main dashboard display with status, clock, and quick action cards
 */

// Capture real-time authentication profile attributes 
$status = $_SESSION['attendance_status'] ?? 'Clocked Out';
$location = $_SESSION['attendance_location'] ?? 'OFFSITE';

// Determine dynamic color mapping tokens based on current tracking state
$isClockedIn = (strtolower($status) === 'clocked in');
$statusBadgeBg = $isClockedIn ? 'rgba(156, 176, 122, 0.15)' : 'rgba(9, 60, 93, 0.06)';
$statusBadgeColor = $isClockedIn ? 'var(--olive-green)' : 'var(--deep-navy)';
?>

<section class="dashboard-workspace-grid" 
         x-data="{ currentTime: '<?php echo date('h:i A'); ?>' }" 
         x-init="setInterval(() => { currentTime = new Intl.DateTimeFormat('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }).format(new Date()); }, 1000)">
  
    <div class="card status-hero-card mb-4">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center g-4">
                
                <div class="col-md-7 col-lg-8">
                    <span class="text-muted d-block font-monospace tracking-wider small text-uppercase mb-1">
                        Current Tracking State
                    </span>
                    <h1 class="display-5 fw-extrabold mb-3 tracking-tight" style="color: var(--deep-navy); font-weight: 800;">
                        <?php echo htmlspecialchars($status); ?>
                    </h1>
                    
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-3 border" 
                         style="background-color: <?php echo $statusBadgeBg; ?>; color: <?php echo $statusBadgeColor; ?>; border-color: rgba(9, 60, 93, 0.05) !important;">
                        <i class="bi bi-geo-alt-fill animate-pulse"></i>
                        <span class="font-monospace small fw-bold text-uppercase">
                            Location Parameter: <?php echo htmlspecialchars($location); ?>
                        </span>
                    </div>

                    <p class="text-muted small mb-0 mt-2">
                        <i class="bi bi-info-circle me-1"></i> Shift logs adjust automatically relative to server localization metrics.
                    </p>
                </div>

                <div class="col-md-5 col-lg-4 text-md-end">
                    <div class="clock-telemetry-panel d-inline-block p-4 rounded-4 text-center text-md-end">
                        <span class="text-muted font-monospace d-block small text-uppercase tracking-widest mb-1">
                            System Time (Local)
                        </span>
                        <div class="display-6 fw-bold font-monospace tracking-tight" 
                             style="color: var(--deep-navy); font-size: 2.5rem;" 
                             x-text="currentTime">
                             <?php echo date('h:i A'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4 opacity-10" style="color: var(--deep-navy);">

            <div class="qr-action-container">
                <button class="btn btn-qr-scan w-100 d-flex align-items-center justify-content-center gap-3 p-3 text-uppercase tracking-wider fw-bold" 
                        @click="window.location.href='<?= route_url('/scan-qr') ?>'" 
                        type="button">
                    <i class="bi bi-qr-code-scan fs-4"></i>
                    <span>Scan Deployment QR Node to Authenticate</span>
                </button>

                <div class="d-flex align-items-center gap-2 mt-3 text-muted justify-content-center">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <p class="mb-0 small font-monospace text-uppercase tracking-wide">
                        Edge Encryption Enabled — Off-grid structural logs will sync on connection restoration.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        
        <div class="col">
            <div class="card module-action-card h-100" @click="window.location.href='<?= route_url('/calendar') ?>'">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="module-icon-avatar mb-3">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <h4 class="fw-bold h5 mb-2" style="color: var(--deep-navy);">Duty Roster Ledger</h4>
                        <p class="text-muted small mb-0">Review past punches, aggregated hour reports, and monthly compliance maps.</p>
                    </div>
                    <div class="mt-4 pt-2 d-flex align-items-center text-decoration-none fw-bold small text-uppercase module-action-link">
                        <span>Access Ledger</span>
                        <i class="bi bi-arrow-right ms-2 transition-transform"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card module-action-card h-100" @click="window.location.href='<?= route_url('/leave') ?>'">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="module-icon-avatar mb-3">
                            <i class="bi bi-file-earmark-medical"></i>
                        </div>
                        <h4 class="fw-bold h5 mb-2" style="color: var(--deep-navy);">Time Off Protocols</h4>
                        <p class="text-muted small mb-0">File new operational absence dockets and query validation pipelines.</p>
                    </div>
                    <div class="mt-4 pt-2 d-flex align-items-center text-decoration-none fw-bold small text-uppercase module-action-link">
                        <span>Query Pipeline</span>
                        <i class="bi bi-arrow-right ms-2 transition-transform"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card module-action-card h-100" @click="window.location.href='<?= route_url('/profile') ?>'">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="module-icon-avatar mb-3">
                            <i class="bi bi-person-bounding-box"></i>
                        </div>
                        <h4 class="fw-bold h5 mb-2" style="color: var(--deep-navy);">Profile Node</h4>
                        <p class="text-muted small mb-0">Modify security passphrases, notification endpoints, and individual context parameters.</p>
                    </div>
                    <div class="mt-4 pt-2 d-flex align-items-center text-decoration-none fw-bold small text-uppercase module-action-link">
                        <span>Adjust Identity</span>
                        <i class="bi bi-arrow-right ms-2 transition-transform"></i>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</section>

<style>
    /* Scoped Design Architecture Elements */
    :root {
        --deep-navy: #093C5D;
        --mid-blue: #3B7597;
        --olive-green: #9CB07A;
        --light-gray: #F5F5F5;
    }

    .status-hero-card {
        background-color: #FFFFFF;
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(9, 60, 93, 0.04);
    }

    .clock-telemetry-panel {
        background-color: var(--light-gray);
        border: 1px solid rgba(9, 60, 93, 0.05);
    }

    /* Core Action Scanners */
    .btn-qr-scan {
        background-color: var(--deep-navy);
        color: #FFFFFF;
        border-radius: 14px;
        border: none;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 15px rgba(9, 60, 93, 0.15);
    }

    .btn-qr-scan:hover {
        background-color: var(--mid-blue);
        color: #FFFFFF;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 117, 151, 0.25);
    }

    .btn-qr-scan:active {
        transform: translateY(0);
    }

    /* Standardized Modular Action Infrastructure Panels */
    .module-action-card {
        background-color: #FFFFFF;
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(9, 60, 93, 0.02);
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .module-action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(9, 60, 93, 0.08);
    }

    .module-icon-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background-color: rgba(59, 117, 151, 0.08);
        color: var(--mid-blue);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }

    .module-action-link {
        color: var(--mid-blue);
        letter-spacing: 0.5px;
        transition: color 0.2s ease;
    }

    .module-action-card:hover .module-action-link {
        color: var(--deep-navy);
    }

    .module-action-card:hover .module-action-link i {
        transform: translateX(4px);
    }

    .transition-transform {
        transition: transform 0.2s ease;
    }

    /* Keyframe Animations */
    @keyframes pulse {
        0% { opacity: 0.6; }
        50% { opacity: 1; }
        100% { opacity: 0.6; }
    }
    
    .animate-pulse {
        animation: pulse 2s infinite ease-in-out;
    }
</style>