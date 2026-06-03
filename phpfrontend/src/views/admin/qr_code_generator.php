<?php
/**
 * QR Code Generator Page
 * Admin page for creating and managing QR codes
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator - Clock-It</title>
    <link href="<?= asset_url('vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_url('vendor/bootstrap-icons/font/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>
</head>
<body x-data="{ 
    sidebarOpen: true,
    showTypeModal: false,
    activeQr: null,
    savedQrCodes: [],
    nextQrId: 1,
    countdownTimer: null,
    nowTimestamp: Date.now(),
    openGenerateModal() {
        this.showTypeModal = true;
    },
    closeGenerateModal() {
        this.showTypeModal = false;
    },
    typeLabel(type) {
        return type === 'clock-in' ? 'Clock In' : 'Clock Out';
    },
    formatDateTime(value) {
        return new Date(value).toLocaleString();
    },
    isExpired(qr) {
        return !qr || new Date(qr.expiresAt).getTime() <= this.nowTimestamp;
    },
    buildQrCode(type) {
        return type === 'clock-in' ? 'CLOCK_IN' : 'CLOCK_OUT';
    },
    buildQrText(qr) {
        const date = new Date(qr.createdAt);
        return [
            `Status: ${qr.type === 'clock-in' ? 'Clocked In' : 'Clocked Out'}`,
            `Date: ${date.toLocaleDateString()}`,
            `Time: ${date.toLocaleTimeString()}`
        ].join('\n');
    },
    async createQrImage(qr) {
        const response = await fetch('<?= route_url('/api/qr-code') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ text: qr.text })
        });

        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.error || 'Unable to generate QR code.');
        }

        return result.imageUrl;
    },
    async generateQr(type) {
        const now = new Date();
        const qr = {
            id: this.nextQrId++,
            type,
            createdAt: now.toISOString(),
            expiresAt: new Date(now.getTime() + 60000).toISOString(),
            secondsLeft: 60,
            code: this.buildQrCode(type),
            text: ''
        };
        qr.text = this.buildQrText(qr);

        try {
            qr.imageUrl = await this.createQrImage(qr);
            this.activeQr = qr;
            this.closeGenerateModal();
            this.startCountdown();
        } catch (error) {
            alert(error.message || 'Unable to generate QR code.');
        }
    },
    startCountdown() {
        if (this.countdownTimer) {
            clearInterval(this.countdownTimer);
        }

        this.countdownTimer = setInterval(() => {
            this.nowTimestamp = Date.now();

            if (!this.activeQr) {
                clearInterval(this.countdownTimer);
                this.countdownTimer = null;
                return;
            }

            const secondsLeft = Math.max(0, Math.ceil((new Date(this.activeQr.expiresAt) - new Date()) / 1000));
            this.activeQr.secondsLeft = secondsLeft;

            if (secondsLeft <= 0) {
                clearInterval(this.countdownTimer);
                this.countdownTimer = null;
            }
        }, 1000);
    },
    saveActiveQr() {
        if (!this.activeQr || this.activeQr.secondsLeft <= 0) return;
        this.savedQrCodes.unshift({ ...this.activeQr, savedId: Date.now(), savedAt: new Date().toISOString() });
    },
    deleteSavedQr(qr) {
        if (!confirm(`Delete saved ${this.typeLabel(qr.type)} QR code?`)) return;
        this.savedQrCodes = this.savedQrCodes.filter(savedQr => savedQr.savedId !== qr.savedId);
    },
    downloadSavedQr(qr) {
        const image = new Image();
        image.onload = () => {
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = 720;
            canvas.height = 900;

            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, canvas.width, canvas.height);
            context.fillStyle = '#093C5D';
            context.font = 'bold 42px Arial';
            context.textAlign = 'center';
            context.fillText('Clock-It QR Code', canvas.width / 2, 72);
            context.font = 'bold 34px Arial';
            context.fillText(this.typeLabel(qr.type), canvas.width / 2, 132);

            if (this.isExpired(qr)) {
                context.fillStyle = '#000000';
                context.fillRect(160, 180, 400, 400);
                context.fillStyle = '#ffffff';
                context.font = 'bold 54px Arial';
                context.fillText('EXPIRED', canvas.width / 2, 392);
            } else {
                context.drawImage(image, 160, 180, 400, 400);
            }

            context.fillStyle = '#212529';
            context.font = '26px Arial';
            context.fillText(`Date & Time: ${this.formatDateTime(qr.createdAt)}`, canvas.width / 2, 650);
            context.font = '22px Arial';
            context.fillText('QR details:', canvas.width / 2, 715);
            context.font = '20px Arial';
            qr.text.split('\n').forEach((line, index) => {
                context.fillText(line, canvas.width / 2, 750 + (index * 32));
            });

            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/jpeg', 0.92);
            link.download = `clock-it-${qr.type}-qr-${qr.savedId || qr.id}.jpeg`;
            document.body.appendChild(link);
            link.click();
            link.remove();
        };
        image.src = qr.imageUrl;
    }
}" @init="window.themeManager.initTheme()">
    
    <div style="display: flex;">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <div style="flex: 1; display: flex; flex-direction: column;">
            <!-- Header -->
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <!-- Page Content -->
            <main class="dashboard-section" style="padding: 2rem;">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4>QR Code Generator</h4>
                        <button class="btn btn-success" @click="openGenerateModal()" type="button">
                            <i class="bi bi-qr-code me-1" aria-hidden="true"></i>Generate QR Code
                        </button>
                    </div>

                    <!-- Current QR Code -->
                    <div class="row mb-4" x-show="activeQr" x-cloak>
                        <div class="col-xl-5 col-lg-6">
                            <div class="card border-0 shadow-sm qr-code-card" :class="isExpired(activeQr) ? 'is-expired' : ''">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0" x-text="activeQr ? typeLabel(activeQr.type) + ' QR Code' : ''"></h5>
                                    <span class="badge" :class="activeQr?.secondsLeft > 0 ? 'bg-success' : 'bg-danger'" x-text="activeQr?.secondsLeft > 0 ? 'Active' : 'Expired'"></span>
                                </div>
                                <div class="card-body text-center">
                                    <div class="qr-code-image-frame" :class="isExpired(activeQr) ? 'is-expired' : ''">
                                        <img class="qr-code-image" :src="activeQr?.imageUrl" alt="Generated QR code">
                                        <div class="qr-expired-overlay" x-show="isExpired(activeQr)" x-cloak>EXPIRED</div>
                                    </div>
                                    <div class="qr-code-details mt-3">
                                        <p class="mb-1"><strong>Type:</strong> <span x-text="activeQr ? typeLabel(activeQr.type) : ''"></span></p>
                                        <p class="mb-1"><strong>Date & Time:</strong> <span x-text="activeQr ? formatDateTime(activeQr.createdAt) : ''"></span></p>
                                        <p class="mb-0"><strong>QR details:</strong></p>
                                        <pre class="qr-scan-text" x-text="activeQr?.text"></pre>
                                    </div>
                                    <div class="qr-countdown mt-3">
                                        <span x-text="activeQr?.secondsLeft"></span>s remaining
                                    </div>
                                    <button class="btn btn-primary mt-3" @click="saveActiveQr()" :disabled="!activeQr || activeQr.secondsLeft <= 0" type="button">
                                        <i class="bi bi-save me-1" aria-hidden="true"></i>Save QR Code
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Saved QR Codes -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Saved QR Codes</h5>
                        <span class="text-muted small" x-text="savedQrCodes.length + ' saved'"></span>
                    </div>

                    <div class="row">
                        <template x-for="qr in savedQrCodes" :key="qr.savedId">
                            <div class="col-xl-4 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm qr-code-card" :class="isExpired(qr) ? 'is-expired' : ''">
                                    <div class="card-body text-center">
                                        <div class="qr-code-image-frame qr-code-image-frame-sm" :class="isExpired(qr) ? 'is-expired' : ''">
                                            <img class="qr-code-image qr-code-image-sm" :src="qr.imageUrl" alt="Saved QR code">
                                            <div class="qr-expired-overlay" x-show="isExpired(qr)" x-cloak>EXPIRED</div>
                                        </div>
                                        <h6 class="mt-3 mb-2" x-text="typeLabel(qr.type)"></h6>
                                        <p class="text-muted small mb-2">
                                            <strong>Date & Time:</strong> <span x-text="formatDateTime(qr.createdAt)"></span>
                                        </p>
                                        <pre class="qr-scan-text" x-text="qr.text"></pre>
                                        <div class="d-flex gap-2 justify-content-center mt-3">
                                            <button class="btn btn-sm btn-outline-secondary" @click="downloadSavedQr(qr)" type="button">
                                                <i class="bi bi-download me-1" aria-hidden="true"></i>Download JPEG
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" @click="deleteSavedQr(qr)" type="button">
                                                <i class="bi bi-trash me-1" aria-hidden="true"></i>Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div class="col-12" x-show="savedQrCodes.length === 0">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center text-muted py-5">
                                    No saved QR codes yet.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Type Selection Modal -->
                    <div class="modal fade show user-form-modal" x-show="showTypeModal" x-cloak tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="generateQrTitle">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="generateQrTitle">Generate QR Code</h5>
                                    <button type="button" class="btn-close" aria-label="Close" @click="closeGenerateModal()"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <button class="btn btn-outline-success w-100 qr-type-choice" @click="generateQr('clock-in')" type="button">
                                                <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                                                <span>Clock In</span>
                                            </button>
                                        </div>
                                        <div class="col-sm-6">
                                            <button class="btn btn-outline-warning w-100 qr-type-choice" @click="generateQr('clock-out')" type="button">
                                                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                                <span>Clock Out</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-backdrop fade show" x-show="showTypeModal" x-cloak></div>
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
    <script src="<?= asset_url('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
