<section class="page-stack" x-data="scanQr()">
    <div class="split-grid">
        <section class="surface">
            <div class="surface-header">
                <div>
                    <h2>QR Scanner</h2>
                    <p class="surface-subtitle">Simulated scanner using active global QR tokens.</p>
                </div>
                <span :class="statusClass(status)" x-text="status"></span>
            </div>

            <div class="scanner-stage">
                <div class="scanner-frame" aria-label="QR scanner preview">
                    <div class="scanner-code">
                        <?php for ($i = 0; $i < 25; $i++): ?>
                            <span style="<?= $i % 3 === 0 || $i % 7 === 0 ? '' : 'opacity:.3' ?>"></span>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-12 col-lg-8">
                    <label class="form-label" for="manual-code">QR token</label>
                    <input id="manual-code" class="form-control" placeholder="GCI, GCO, CLOCK_IN, CLOCK_OUT, or token" x-model="manualCode">
                </div>
                <div class="col-12 col-lg-4 d-flex align-items-end">
                    <button class="btn btn-primary w-100" type="button" x-on:click="scanManual()"><?= ui_icon('qr') ?> Submit scan</button>
                </div>
            </div>

            <template x-if="feedback">
                <div class="feedback-panel mt-3" :class="feedback.level" x-text="feedback.message"></div>
            </template>
        </section>

        <aside class="surface">
            <div class="surface-header">
                <div>
                    <h2>Quick Actions</h2>
                    <p class="surface-subtitle">Frontend scan buttons for both global QR types.</p>
                </div>
            </div>

            <div class="list-stack">
                <button class="mini-row text-start" type="button" x-on:click="scan('clockIn')">
                    <span>
                        <strong>Scan Global Clock In QR</strong>
                        <span x-text="tokens.clockIn?.token"></span>
                    </span>
                    <span :class="statusClass(tokens.clockIn?.status)" x-text="tokens.clockIn?.status"></span>
                </button>
                <button class="mini-row text-start" type="button" x-on:click="scan('clockOut')">
                    <span>
                        <strong>Scan Global Clock Out QR</strong>
                        <span x-text="tokens.clockOut?.token"></span>
                    </span>
                    <span :class="statusClass(tokens.clockOut?.status)" x-text="tokens.clockOut?.status"></span>
                </button>
            </div>

            <div class="metadata-grid mt-3">
                <div class="metadata-item">
                    <span>Clock in</span>
                    <strong x-text="formatTime(summary.clockIn?.timestamp)"></strong>
                </div>
                <div class="metadata-item">
                    <span>Clock out</span>
                    <strong x-text="formatTime(summary.clockOut?.timestamp)"></strong>
                </div>
                <div class="metadata-item">
                    <span>Current state</span>
                    <strong x-text="status"></strong>
                </div>
                <div class="metadata-item">
                    <span>Today</span>
                    <strong x-text="`${summary.records.length} events`"></strong>
                </div>
            </div>
        </aside>
    </div>
</section>
