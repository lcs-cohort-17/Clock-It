<?php

declare(strict_types=1);

$isAdminDashboard = true;
$title = 'Admin Settings';

ob_start();
?>

<div class="app-shell">
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content">
            <section class="settings-page container-fluid p-4 p-lg-5" x-data="settingsManager()" x-init="init()">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div>
                        <h1 class="staff-history-title mb-1">
                            <i class="bi bi-gear-wide-connected me-2" aria-hidden="true"></i>
                            System Configuration
                        </h1>
                        <p class="text-muted mb-0">Manage session and data retention policies.</p>
                    </div>
                    <span class="badge settings-admin-badge px-3 py-2">
                        <i class="bi bi-shield-lock me-1" aria-hidden="true"></i>
                        Admin Panel
                    </span>
                </div>

                <div class="alert alert-danger" x-show="errorMessage" x-cloak x-text="errorMessage"></div>
                <div class="alert alert-success" x-show="successMessage" x-cloak x-text="successMessage"></div>

                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <section class="page-card settings-card p-4 h-100">
                            <h2 class="h5 fw-bold">
                                <i class="bi bi-hourglass-split me-2" aria-hidden="true"></i>
                                Session Timeout
                            </h2>
                            <p class="text-muted">Control user session lifetime.</p>

                            <label for="sessionTimeout" class="form-label fw-semibold">Minutes</label>
                            <input id="sessionTimeout"
                                   type="number"
                                   min="1"
                                   step="1"
                                   class="form-control form-control-lg"
                                   x-model.number="sessionTimeout">

                            <button type="button"
                                    class="btn btn-main w-100 mt-4"
                                    @click="saveSettings"
                                    :disabled="isSaving">
                                <i class="bi bi-save" aria-hidden="true"></i>
                                <span x-text="isSaving ? 'Saving...' : 'Save Settings'"></span>
                            </button>
                        </section>
                    </div>

                    <div class="col-12 col-lg-6">
                        <section class="page-card settings-card p-4 h-100">
                            <h2 class="h5 fw-bold">
                                <i class="bi bi-database me-2" aria-hidden="true"></i>
                                Data Retention
                            </h2>
                            <p class="text-muted">Manage old attendance records.</p>

                            <label for="retentionDays" class="form-label fw-semibold">Keep records for this many days</label>
                            <input id="retentionDays"
                                   type="number"
                                   min="1"
                                   step="1"
                                   class="form-control form-control-lg"
                                   x-model.number="dataRetentionDays">

                            <button type="button"
                                    class="btn settings-purge-btn w-100 mt-4"
                                    data-bs-toggle="modal"
                                    data-bs-target="#purgeConfirmModal">
                                <i class="bi bi-trash" aria-hidden="true"></i>
                                Purge Old Records
                            </button>
                        </section>
                    </div>
                </div>

                <div class="modal fade" id="purgeConfirmModal" tabindex="-1" aria-labelledby="purgeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title fs-5" id="purgeModalLabel">Confirm Purge</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Permanently delete records older than <strong x-text="dataRetentionDays"></strong> days?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn settings-purge-btn" @click="executePurge" :disabled="isPurging">
                                    <span x-text="isPurging ? 'Purging...' : 'Purge Records'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
function settingsManager() {
    const settingsApi = <?= json_encode(app_url('/api/admin/settings'), JSON_THROW_ON_ERROR) ?>;
    const purgeApi = <?= json_encode(app_url('/api/admin/data-retention/purge'), JSON_THROW_ON_ERROR) ?>;

    return {
        sessionTimeout: 30,
        dataRetentionDays: 90,
        isSaving: false,
        isPurging: false,
        errorMessage: '',
        successMessage: '',

        async init() {
            try {
                const response = await fetch(settingsApi);
                if (!response.ok) throw new Error('Unable to load settings.');
                const settings = await response.json();
                this.sessionTimeout = settings.session_timeout;
                this.dataRetentionDays = settings.data_retention_days;
            } catch (error) {
                this.errorMessage = error.message;
            }
        },

        validSettings() {
            return Number.isInteger(this.sessionTimeout)
                && this.sessionTimeout > 0
                && Number.isInteger(this.dataRetentionDays)
                && this.dataRetentionDays > 0;
        },

        async saveSettings() {
            this.errorMessage = '';
            this.successMessage = '';

            if (!this.validSettings()) {
                this.errorMessage = 'Both values must be positive integers.';
                return;
            }

            this.isSaving = true;

            try {
                const response = await fetch(settingsApi, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        session_timeout: this.sessionTimeout,
                        data_retention_days: this.dataRetentionDays,
                    }),
                });

                if (!response.ok) throw new Error('Unable to save settings.');
                this.successMessage = 'Settings saved successfully.';
            } catch (error) {
                this.errorMessage = error.message;
            } finally {
                this.isSaving = false;
            }
        },

        async executePurge() {
            this.errorMessage = '';
            this.successMessage = '';
            this.isPurging = true;

            try {
                const response = await fetch(purgeApi, { method: 'POST' });
                if (!response.ok) throw new Error('Unable to purge old records.');
                this.successMessage = 'Old records purged successfully.';
                bootstrap.Modal.getOrCreateInstance(document.getElementById('purgeConfirmModal')).hide();
            } catch (error) {
                this.errorMessage = error.message;
            } finally {
                this.isPurging = false;
            }
        },
    };
}
</script>

<?php
$content = ob_get_clean();

require __DIR__ . '/../layouts/app.php';
