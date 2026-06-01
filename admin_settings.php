<?php
/** Admin Settings Page */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Admin Settings | Brand Configuration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --deep-navy: #093C5D;
            --mid-blue: #3B7597;
            --olive-green: #9CB07A;
            --light-gray: #F5F5F5;
        }

        body {
            background-color: var(--light-gray);
            font-family: 'Segoe UI', system-ui, -apple-system, 'Roboto', sans-serif;
        }

        .btn-primary {
            background-color: var(--deep-navy);
            border-color: var(--deep-navy);
            transition: all 0.2s ease;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #052c45;
            border-color: #052c45;
            box-shadow: 0 0 0 0.2rem rgba(9, 60, 93, 0.3);
        }
        .btn-primary:active {
            background-color: #031e2f;
        }

        .btn-outline-danger,
        .btn-danger {
            background-color: var(--olive-green);
            border-color: var(--olive-green);
            color: #2c3e2f;
        }
        .btn-outline-danger:hover,
        .btn-danger:hover {
            background-color: #8aa36e;
            border-color: #7c9462;
            color: #1e2a20;
        }
        .btn-outline-danger {
            border-width: 1px;
            background-color: transparent;
            color: #5e6e48;
        }
        .btn-outline-danger:hover {
            background-color: var(--olive-green);
            color: #2c3e2f;
        }

        .card {
            border: none;
            border-top: 4px solid var(--mid-blue);
            border-radius: 0.75rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        }
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(59, 117, 151, 0.15);
        }
        .card-title {
            color: var(--deep-navy);
            font-weight: 600;
        }

        .form-control:focus {
            border-color: var(--mid-blue);
            box-shadow: 0 0 0 0.2rem rgba(59, 117, 151, 0.25);
        }

        .badge.bg-secondary {
            background-color: var(--mid-blue) !important;
        }

        .alert-success {
            background-color: #eef5e8;
            border-left: 5px solid var(--olive-green);
            color: #2a4b1e;
        }
        .alert-danger {
            background-color: #fdeded;
            border-left: 5px solid #c0392b;
        }

        .modal-header {
            background-color: var(--deep-navy) !important;
            color: white;
        }

        .modal-footer .btn-secondary {
            background-color: #e9ecef;
            color: #1f2f3a;
        }
        .modal-footer .btn-danger {
            background-color: var(--olive-green);
            border-color: var(--olive-green);
            color: #1f2f1a;
        }
        .modal-footer .btn-danger:hover {
            background-color: #7c9462;
        }

        .text-muted {
            color: #6c7a89 !important;
        }
        h1, .h3 {
            color: var(--deep-navy);
        }

        .btn-primary:disabled, .btn-outline-danger:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        [x-cloak] { display: none !important; }
        
        .btn i, .card-title i, .modal-header i {
            margin-right: 0.5rem;
        }
        h1 i {
            margin-right: 0.75rem;
            font-size: 2rem;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container py-5" x-data="settingsManager()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom" style="border-color: rgba(59,117,151,0.2) !important;">
        <div>
            <h1 class="h2 fw-bold">
                <i class="bi bi-gear-wide-connected"></i> System Configuration
            </h1>
            <p class="text-muted mt-1">Manage session & data retention policies</p>
        </div>
        <span class="badge bg-secondary px-3 py-2" style="background-color: #3B7597 !important;">
            <i class="bi bi-shield-lock"></i> Admin Panel
        </span>
    </div>

    <div class="mb-3" x-show="errorMessage" x-cloak>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div x-text="errorMessage"></div>
            <button type="button" class="btn-close" @click="errorMessage = null" aria-label="Close"></button>
        </div>
    </div>
    <div class="mb-3" x-show="successMessage" x-cloak>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div x-text="successMessage"></div>
            <button type="button" class="btn-close" @click="successMessage = null" aria-label="Close"></button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
                    <h5 class="card-title fs-4">
                        <i class="bi bi-hourglass-split"></i> Session Timeout
                    </h5>
                    <p class="text-muted small">Control user session lifetime</p>
                </div>
                <div class="card-body pt-3">
                    <label for="sessionTimeout" class="form-label fw-semibold">
                        <i class="bi bi-clock"></i> Session Timeout <span class="text-muted">(minutes)</span>
                    </label>
                    <input type="number" 
                            id="sessionTimeout"
                            class="form-control form-control-lg" 
                            x-model.number="sessionTimeout"
                            min="1"
                            step="1"
                            placeholder="e.g., 30"
                            :class="{'is-invalid': validationErrorOnSave && (!isValidInteger(sessionTimeout) || sessionTimeout <= 0)}">
                    <div class="form-text small text-muted mt-1">
                        <i class="bi bi-info-circle"></i> Positive integer only. Recommended: 15–120 minutes.
                    </div>
                    <button class="btn btn-primary mt-4 w-100 py-2 d-flex align-items-center justify-content-center gap-2" 
                            @click="saveSettings" 
                            :disabled="isSaving">
                        <span x-show="isSaving" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <i class="bi bi-save" x-show="!isSaving"></i>
                        <span x-text="isSaving ? 'Saving...' : 'Save Settings'"></span>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
                    <h5 class="card-title fs-4">
                        <i class="bi bi-database"></i> Data Retention
                    </h5>
                    <p class="text-muted small">Manage old records lifecycle</p>
                </div>
                <div class="card-body pt-3">
                    <label for="retentionDays" class="form-label fw-semibold">
                        <i class="bi bi-calendar-week"></i> Keep records for <span class="text-muted">(days)</span>
                    </label>
                    <input type="number" 
                            id="retentionDays"
                            class="form-control form-control-lg" 
                            x-model.number="dataRetentionDays"
                            min="1"
                            step="1"
                            placeholder="e.g., 90"
                            :class="{'is-invalid': validationErrorOnSave && (!isValidInteger(dataRetentionDays) || dataRetentionDays <= 0)}">
                    <div class="form-text small text-muted mt-1">
                        <i class="bi bi-trash3"></i> Records older than this will be eligible for purging.
                    </div>
                    <button class="btn btn-outline-danger mt-4 w-100 py-2 d-flex align-items-center justify-content-center gap-2" 
                            @click="openPurgeModal" 
                            :disabled="isPurging">
                        <span x-show="isPurging" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <i class="bi bi-trash" x-show="!isPurging"></i>
                        <span x-text="isPurging ? 'Purging...' : 'Purge Old Records'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="purgeConfirmModal" tabindex="-1" aria-labelledby="purgeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="purgeModalLabel">
                        <i class="bi bi-exclamation-triangle-fill"></i> Confirm Purge
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="fs-6">
                        <i class="bi bi-alarm"></i> You are about to <strong>permanently delete</strong> all records older than <span x-text="dataRetentionDays"></span> days.
                    </p>
                    <p class="mb-0 text-muted small">
                        <i class="bi bi-shield-exclamation"></i> This action cannot be undone. Archived data will be removed from the system.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" @click="executePurge" :disabled="isPurging">
                        <span x-show="isPurging" class="spinner-border spinner-border-sm me-1"></span>
                        <i class="bi bi-check-lg" x-show="!isPurging"></i>
                        Yes, Purge Records
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function settingsManager() {
        return {
            sessionTimeout: null,
            dataRetentionDays: null,
            isLoading: false,
            isSaving: false,
            isPurging: false,
            errorMessage: null,
            successMessage: null,
            validationErrorOnSave: false,
            purgeModalInstance: null,

            async init() {
                await this.fetchSettings();
                this.$nextTick(() => {
                    const modalElement = document.getElementById('purgeConfirmModal');
                    if (modalElement) {
                        this.purgeModalInstance = new bootstrap.Modal(modalElement);
                    }
                });
            },

            isValidInteger(value) {
                return Number.isInteger(value) && value > 0;
            },

            async fetchSettings() {
                this.errorMessage = null;
                this.isLoading = true;
                try {
                    const response = await fetch('/api/admin/settings', {
                        method: 'GET',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }
                    });
                    if (!response.ok) throw new Error(`Failed to load settings (HTTP ${response.status})`);
                    const data = await response.json();
                    if (data.hasOwnProperty('session_timeout')) this.sessionTimeout = data.session_timeout;
                    if (data.hasOwnProperty('data_retention_days')) this.dataRetentionDays = data.data_retention_days;
                    if (!this.isValidInteger(this.sessionTimeout)) this.sessionTimeout = 30;
                    if (!this.isValidInteger(this.dataRetentionDays)) this.dataRetentionDays = 90;
                } catch (err) {
                    console.error(err);
                    this.errorMessage = `❌ Unable to retrieve settings: ${err.message}. Using defaults.`;
                    this.sessionTimeout = 30;
                    this.dataRetentionDays = 90;
                } finally {
                    this.isLoading = false;
                    this.validationErrorOnSave = false;
                }
            },

            async saveSettings() {
                this.validationErrorOnSave = true;
                this.errorMessage = null;
                this.successMessage = null;
                const isTimeoutValid = this.isValidInteger(this.sessionTimeout);
                const isRetentionValid = this.isValidInteger(this.dataRetentionDays);
                if (!isTimeoutValid || !isRetentionValid) {
                    let msg = '';
                    if (!isTimeoutValid && !isRetentionValid) msg = 'Both values must be positive integers (≥ 1).';
                    else if (!isTimeoutValid) msg = 'Session Timeout must be a positive integer (≥ 1).';
                    else msg = 'Data Retention must be a positive integer (≥ 1).';
                    this.errorMessage = msg;
                    return;
                }
                this.isSaving = true;
                try {
                    const payload = {
                        session_timeout: this.sessionTimeout,
                        data_retention_days: this.dataRetentionDays
                    };
                    const response = await fetch('/api/admin/settings', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    if (!response.ok) throw new Error(`Save failed (HTTP ${response.status})`);
                    await this.fetchSettings();
                    this.successMessage = 'Settings saved successfully.';
                    this.validationErrorOnSave = false;
                    setTimeout(() => { if (this.successMessage === 'Settings saved successfully.') this.successMessage = null; }, 5000);
                } catch (err) {
                    this.errorMessage = `⚠️ ${err.message || 'Could not save settings.'}`;
                } finally {
                    this.isSaving = false;
                }
            },

            openPurgeModal() {
                if (this.purgeModalInstance) this.purgeModalInstance.show();
                else {
                    const modalElem = document.getElementById('purgeConfirmModal');
                    if (modalElem) {
                        this.purgeModalInstance = new bootstrap.Modal(modalElem);
                        this.purgeModalInstance.show();
                    }
                }
            },

            async executePurge() {
                if (this.isPurging) return;
                this.isPurging = true;
                this.errorMessage = null;
                this.successMessage = null;
                try {
                    const response = await fetch('/api/admin/data-retention/purge', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }
                    });
                    if (!response.ok) throw new Error(`Purge failed (HTTP ${response.status})`);
                    this.successMessage = 'Old records purged successfully.';
                    setTimeout(() => { if (this.successMessage === 'Old records purged successfully.') this.successMessage = null; }, 5000);
                    if (this.purgeModalInstance) this.purgeModalInstance.hide();
                } catch (err) {
                    this.errorMessage = `❌ Purge failed: ${err.message}`;
                } finally {
                    this.isPurging = false;
                }
            }
        };
    }
</script>
</body>
</html>