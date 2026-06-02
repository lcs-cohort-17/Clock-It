<section class="page-stack" x-data="settingsManager()">
    <section class="surface">
        <div class="surface-header">
            <div>
                <h2>Company Settings</h2>
                <p class="surface-subtitle">Frontend settings model prepared for backend persistence.</p>
            </div>
            <button class="btn btn-primary" type="button" x-on:click="save()"><?= ui_icon('check') ?> Save settings</button>
        </div>

        <template x-if="saved">
            <div class="feedback-panel success mb-3">Settings saved in local state.</div>
        </template>
        <template x-if="error">
            <div class="feedback-panel danger mb-3" x-text="error"></div>
        </template>

        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <label class="form-label">Company name</label>
                <input class="form-control" x-model="settings.company.name">
            </div>
            <div class="col-12 col-lg-6">
                <label class="form-label">Primary location</label>
                <input class="form-control" x-model="settings.company.primaryLocation">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label">Timezone</label>
                <select class="form-select" x-model="settings.company.timezone">
                    <option>Africa/Johannesburg</option>
                    <option>UTC</option>
                    <option>Europe/London</option>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label">Week starts</label>
                <select class="form-select" x-model="settings.company.weekStart">
                    <option>Monday</option>
                    <option>Sunday</option>
                </select>
            </div>
        </div>
    </section>

    <div class="two-grid">
        <section class="surface">
            <div class="surface-header">
                <h2>Attendance Rules</h2>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Grace minutes</label>
                    <input class="form-control" type="number" min="0" x-model.number="settings.attendanceRules.graceMinutes">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Auto clock-out hours</label>
                    <input class="form-control" type="number" min="1" x-model.number="settings.attendanceRules.autoClockOutHours">
                </div>
                <div class="col-12">
                    <label class="mini-row">
                        <span>
                            <strong>Require QR for attendance</strong>
                            <span>Global QR must be active for submissions.</span>
                        </span>
                        <input class="form-check-input" type="checkbox" x-model="settings.attendanceRules.requireQr">
                    </label>
                </div>
                <div class="col-12">
                    <label class="mini-row">
                        <span>
                            <strong>Review offline scans</strong>
                            <span>Pending sync scans enter review.</span>
                        </span>
                        <input class="form-check-input" type="checkbox" x-model="settings.attendanceRules.reviewOfflineScans">
                    </label>
                </div>
            </div>
        </section>

        <section class="surface">
            <div class="surface-header">
                <h2>Session Settings</h2>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Idle timeout minutes</label>
                    <input class="form-control" type="number" min="5" x-model.number="settings.session.idleTimeout">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Remember device days</label>
                    <input class="form-control" type="number" min="1" x-model.number="settings.session.rememberDeviceDays">
                </div>
                <div class="col-12">
                    <label class="mini-row">
                        <span>
                            <strong>MFA placeholder</strong>
                            <span>Prepared for future identity provider integration.</span>
                        </span>
                        <input class="form-check-input" type="checkbox" x-model="settings.session.enforceMfaPlaceholder">
                    </label>
                </div>
            </div>
        </section>
    </div>

    <div class="two-grid">
        <section class="surface">
            <div class="surface-header">
                <h2>Data Retention</h2>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label">Attendance months</label>
                    <input class="form-control" type="number" x-model.number="settings.retention.attendanceMonths">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Audit months</label>
                    <input class="form-control" type="number" x-model.number="settings.retention.auditMonths">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Export window days</label>
                    <input class="form-control" type="number" x-model.number="settings.retention.exportWindowDays">
                </div>
            </div>
        </section>

        <section class="surface">
            <div class="surface-header">
                <h2>Integration Placeholders</h2>
            </div>
            <div class="list-stack">
                <template x-for="integration in settings.integrations" :key="integration.name">
                    <div class="mini-row">
                        <strong x-text="integration.name"></strong>
                        <span class="badge-soft amber" x-text="integration.status"></span>
                    </div>
                </template>
            </div>
        </section>
    </div>
</section>
