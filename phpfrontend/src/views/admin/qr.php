<section class="page-stack" x-data="qrManager()">
    <div class="hero-panel">
        <span class="badge-soft teal">Global QR controls</span>
        <h2 class="hero-title mt-3">Generate production-style QR assets for clock in and clock out flows.</h2>
        <p class="hero-copy">Each QR token carries status, creator, usage, expiry, and lifecycle metadata for backend integration later.</p>
    </div>

    <template x-if="feedback">
        <div class="feedback-panel success" x-text="feedback"></div>
    </template>

    <div class="qr-grid">
        <article class="qr-card">
            <div class="surface-header">
                <div>
                    <h2>Global Clock In QR</h2>
                    <p class="surface-subtitle">Used by staff to open a work session.</p>
                </div>
                <span :class="statusClass(tokenFor('clockIn').status)" x-text="tokenFor('clockIn').status"></span>
            </div>

            <div class="qr-preview">
                <canvas id="qr-clock-in" aria-label="Global Clock In QR"></canvas>
            </div>

            <div class="metadata-grid">
                <div class="metadata-item">
                    <span>Token</span>
                    <strong x-text="tokenFor('clockIn').token"></strong>
                </div>
                <div class="metadata-item">
                    <span>Usage</span>
                    <strong x-text="`${tokenFor('clockIn').usageCount || 0} scans`"></strong>
                </div>
                <div class="metadata-item">
                    <span>Generated</span>
                    <strong x-text="formatDateTime(tokenFor('clockIn').generatedAt)"></strong>
                </div>
                <div class="metadata-item">
                    <span>Expires</span>
                    <strong x-text="formatDateTime(tokenFor('clockIn').expiresAt)"></strong>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                <button class="btn btn-primary" type="button" x-on:click="generate('clockIn')"><?= ui_icon('plus') ?> Generate</button>
                <button class="btn btn-outline-light" type="button" x-on:click="regenerate('clockIn')"><?= ui_icon('spark') ?> Regenerate</button>
                <button class="btn btn-outline-light" type="button" x-on:click="download('clockIn')"><?= ui_icon('download') ?> PNG</button>
                <button class="btn btn-outline-light" type="button" x-on:click="copyToken('clockIn')"><?= ui_icon('check') ?> Copy token</button>
                <button class="btn btn-danger" type="button" x-on:click="revoke('clockIn')"><?= ui_icon('x') ?> Revoke</button>
            </div>
        </article>

        <article class="qr-card">
            <div class="surface-header">
                <div>
                    <h2>Global Clock Out QR</h2>
                    <p class="surface-subtitle">Used by staff to close a work session.</p>
                </div>
                <span :class="statusClass(tokenFor('clockOut').status)" x-text="tokenFor('clockOut').status"></span>
            </div>

            <div class="qr-preview">
                <canvas id="qr-clock-out" aria-label="Global Clock Out QR"></canvas>
            </div>

            <div class="metadata-grid">
                <div class="metadata-item">
                    <span>Token</span>
                    <strong x-text="tokenFor('clockOut').token"></strong>
                </div>
                <div class="metadata-item">
                    <span>Usage</span>
                    <strong x-text="`${tokenFor('clockOut').usageCount || 0} scans`"></strong>
                </div>
                <div class="metadata-item">
                    <span>Generated</span>
                    <strong x-text="formatDateTime(tokenFor('clockOut').generatedAt)"></strong>
                </div>
                <div class="metadata-item">
                    <span>Expires</span>
                    <strong x-text="formatDateTime(tokenFor('clockOut').expiresAt)"></strong>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                <button class="btn btn-primary" type="button" x-on:click="generate('clockOut')"><?= ui_icon('plus') ?> Generate</button>
                <button class="btn btn-outline-light" type="button" x-on:click="regenerate('clockOut')"><?= ui_icon('spark') ?> Regenerate</button>
                <button class="btn btn-outline-light" type="button" x-on:click="download('clockOut')"><?= ui_icon('download') ?> PNG</button>
                <button class="btn btn-outline-light" type="button" x-on:click="copyToken('clockOut')"><?= ui_icon('check') ?> Copy token</button>
                <button class="btn btn-danger" type="button" x-on:click="revoke('clockOut')"><?= ui_icon('x') ?> Revoke</button>
            </div>
        </article>
    </div>
</section>
