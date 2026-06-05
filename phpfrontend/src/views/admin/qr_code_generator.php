<?php
//Updated code
$title = 'QR Code Generator';
$isAdminDashboard = true;

ob_start();
?>

<div class="app-shell" x-data="qrGenerator()">
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content container-fluid p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="display-6 fw-bold mb-1">QR Code Generator</h1>
                    <p class="text-muted mb-0">Create, preview, and save active QR codes for staff clock-in and clock-out.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-main px-4" @click="openGenerateModal()" :disabled="generatingQr" type="button">
                        <i class="bi bi-qr-code me-2" aria-hidden="true"></i>
                        <span x-text="generatingQr ? 'Generating...' : 'Generate QR Code'"></span>
                    </button>
                </div>
            </div>

            <div class="dashboard-section">
                <div class="row g-4">
                    <div class="col-xl-5 col-lg-6" x-show="activeQr" x-cloak>
                        <div class="card qr-code-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h5 class="mb-0" x-text="activeQr ? typeLabel(activeQr.type) + ' QR Code' : ''"></h5>
                                    </div>
                                    <span class="badge" :class="activeQr?.secondsLeft > 0 ? 'bg-success' : 'bg-danger'" x-text="activeQr?.secondsLeft > 0 ? 'Active' : 'Expired'"></span>
                                </div>

                                <div class="qr-code-image-frame mb-3" :class="isExpired(activeQr) ? 'is-expired' : ''">
                                    <img class="qr-code-image" :src="activeQr?.imageUrl" alt="Generated QR code">
                                    <div class="qr-expired-overlay" x-show="isExpired(activeQr)" x-cloak>EXPIRED</div>
                                </div>

                                <div class="qr-code-details text-start">
                                    <p class="mb-2"><strong>Type:</strong> <span x-text="activeQr ? typeLabel(activeQr.type) : ''"></span></p>
                                    <p class="mb-2"><strong>Date & Time:</strong> <span x-text="activeQr ? formatDateTime(activeQr.createdAt) : ''"></span></p>
                                    <p class="mb-1"><strong>QR details:</strong></p>
                                    <pre class="qr-scan-text" x-text="activeQr?.text"></pre>
                                </div>

                                <div class="d-flex align-items-center justify-content-between mt-3 gap-3 flex-wrap">
                                    <span class="qr-countdown text-muted" x-text="activeQr?.secondsLeft + 's remaining'"></span>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button class="btn btn-outline-secondary" @click="downloadQr(activeQr)" :disabled="!activeQr" type="button">
                                            <i class="bi bi-download me-1"></i>Download
                                        </button>
                                        <button class="btn btn-main" @click="saveActiveQr()" :disabled="!activeQr || activeQr.secondsLeft <= 0" type="button">
                                            <i class="bi bi-save me-1"></i>Save QR Code
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Saved QR Codes</h5>
                        <span class="text-muted small" x-text="savedQrCodes.length + ' saved'"></span>
                    </div>

                    <div class="row g-4">
                        <template x-for="qr in savedQrCodes" :key="qr.savedId">
                            <div class="col-xl-4 col-md-6">
                                <div class="card qr-code-card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="qr-code-image-frame qr-code-image-frame-sm mb-3" :class="isExpired(qr) ? 'is-expired' : ''">
                                            <img class="qr-code-image" :src="qr.imageUrl" alt="Saved QR code">
                                            <div class="qr-expired-overlay" x-show="isExpired(qr)" x-cloak>EXPIRED</div>
                                        </div>

                                        <h6 class="mb-2" x-text="typeLabel(qr.type)"></h6>
                                        <p class="text-muted small mb-2"><strong>Date & Time:</strong> <span x-text="formatDateTime(qr.createdAt)"></span></p>
                                        <pre class="qr-scan-text mb-3" x-text="qr.text"></pre>
                                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                                            <button class="btn btn-outline-secondary btn-sm" @click="downloadQr(qr)" type="button">
                                                <i class="bi bi-download me-1"></i>Download
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm" @click="deleteSavedQr(qr)" type="button">
                                                <i class="bi bi-trash me-1"></i>Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div class="col-12" x-show="savedQrCodes.length === 0">
                            <div class="card qr-code-card border-0 shadow-sm">
                                <div class="card-body text-center text-muted py-5">
                                    No saved QR codes yet.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- QR Type Selection Modal — placed outside app-shell to escape overflow:hidden -->
<div class="qr-type-modal-overlay" x-data x-show="$store.qrModal.show" x-cloak role="dialog" aria-modal="true" aria-labelledby="qrModalTitle" @keydown.escape.window="$store.qrModal.close()">
    <div class="qr-type-modal-card">
        <div class="qr-type-modal-header">
            <h5 class="qr-type-modal-title" id="qrModalTitle">
                <i class="bi bi-qr-code me-2"></i>Generate QR Code
            </h5>
            <button type="button" class="qr-type-modal-close" aria-label="Close" @click="$store.qrModal.close()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <p class="qr-type-modal-lead">Select the type of QR code to generate:</p>
        <div class="qr-type-modal-options">
            <button class="qr-type-option qr-type-option--in" @click="$store.qrModal.pick('clock-in')" type="button">
                <i class="bi bi-box-arrow-in-right qr-type-option-icon"></i>
                <span class="qr-type-option-label">Clock In</span>
            </button>
            <button class="qr-type-option qr-type-option--out" @click="$store.qrModal.pick('clock-out')" type="button">
                <i class="bi bi-box-arrow-right qr-type-option-icon"></i>
                <span class="qr-type-option-label">Clock Out</span>
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        // Shared store so the modal (outside app-shell) can talk to qrGenerator
        Alpine.store('qrModal', {
            show: false,
            _callback: null,
            open(callback) {
                this._callback = callback;
                this.show = true;
            },
            close() {
                this.show = false;
                this._callback = null;
            },
            pick(type) {
                const cb = this._callback;
                this.close();
                if (cb) cb(type);
            }
        });

        Alpine.data('qrGenerator', () => ({
            showTypeModal: false,
            activeQr: null,
            savedQrCodes: [],
            nextQrId: 1,
            countdownTimer: null,
            nowTimestamp: Date.now(),
            generatingQr: false,

            openGenerateModal() {
                Alpine.store('qrModal').open((type) => this.generateQr(type));
            },

            closeGenerateModal() {
                Alpine.store('qrModal').close();
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
                try {
                    const response = await fetch('<?= route_url('/api/qr-code') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ text: qr.code })
                    });

                    const result = await response.json();
                    if (!response.ok) {
                        throw new Error(result.error || 'Unable to generate QR code.');
                    }

                    return result.imageUrl;
                } catch (error) {
                    return `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(qr.code)}`;
                }
            },

            async generateQr(type) {
                if (this.generatingQr) return;

                this.generatingQr = true;
                const now = new Date();
                const qr = {
                    id: this.nextQrId++,
                    type,
                    createdAt: now.toISOString(),
                    expiresAt: new Date(now.getTime() + 30000).toISOString(),
                    secondsLeft: 30,
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
                } finally {
                    this.generatingQr = false;
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

            downloadQr(qr) {
                if (!qr) return;
                const image = new Image();
                image.onload = () => {
                    try {
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

                        this.downloadImage(canvas.toDataURL('image/jpeg', 0.92), qr);
                    } catch (error) {
                        this.downloadImage(qr.imageUrl, qr);
                    }
                };
                image.onerror = () => this.downloadImage(qr.imageUrl, qr);
                image.crossOrigin = 'anonymous';
                image.src = qr.imageUrl;
            },

            downloadImage(url, qr) {
                const link = document.createElement('a');
                link.href = url;
                link.download = `clock-it-${qr.type}-qr-${qr.savedId || qr.id}.jpeg`;
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                link.remove();
            }
        }));
    });

    function logoutUser() {
        if (confirm('Are you sure you want to sign out?')) {
            window.location.href = '<?= route_url('/logout') ?>';
        }
    }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
