<?php
require_once __DIR__ . '/google_sheets_integration.php';

if (!function_exists('render_google_sheets_settings_card')) {
    function render_google_sheets_settings_card(): void {
        $isConnected    = (bool)   ($_SESSION['gs_connected']       ?? false);
        $connectedSheet = (string) ($_SESSION['gs_connected_sheet'] ?? '');
        $connectedSince = (string) ($_SESSION['gs_connected_since'] ?? '');
        $modal          = (string) ($_GET['modal']                  ?? '');
        $badgeClass     = $isConnected ? 'integration-badge connected' : 'integration-badge disconnected';
        ?>
        <div class="card settings-card google-sheets-settings-card">
            <div class="settings-card-header">
                <div class="settings-icon">
                    <?= google_sheets_icon('google-sheets-icon') ?>
                </div>
                <div class="settings-card-title">
                    <h2>Google Sheets Integration</h2>
                    <p class="muted">Two-way sync of attendance data with auto field mapping.</p>
                </div>
            </div>

            <div class="settings-status">
                <span class="<?= $badgeClass ?>"><?= $isConnected ? 'Connected' : 'Not connected' ?></span>
            </div>

            <?php if ($isConnected): ?>
                <dl class="integration-details">
                    <div>
                        <dt>Connected sheet</dt>
                        <dd><?= htmlspecialchars($connectedSheet) ?></dd>
                    </div>
                    <div>
                        <dt>Connected since</dt>
                        <dd><?= htmlspecialchars($connectedSince) ?></dd>
                    </div>
                </dl>
                <a class="btn settings-action danger" href="/admin/settings?modal=disconnect">Disconnect</a>
            <?php else: ?>
                <a class="btn settings-action" href="/admin/settings?modal=connect">Connect Google Sheet</a>
            <?php endif; ?>
        </div>

        <?php if ($modal === 'connect'): ?>
            <div class="modal-backdrop"></div>
            <div class="settings-modal modal-content modal-dialog" role="dialog" aria-modal="true" aria-labelledby="connect-google-title">
                <h3 id="connect-google-title">Connect to Google Sheets</h3>
                <p>Please review the setup information before connecting.</p>
                <ul>
                    <li>A new Google Sheet will be created for attendance data.</li>
                    <li>Future clock-ins and clock-outs will sync automatically.</li>
                    <li>You can disconnect at any time from Settings.</li>
                </ul>
                <form method="post" action="/admin/settings">
                    <input type="hidden" name="action" value="connect_google_sheets">
                    <a class="btn settings-action secondary" href="/admin/settings">Cancel</a>
                    <button type="submit" class="btn settings-action">Confirm &amp; Connect</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($modal === 'disconnect'): ?>
            <div class="modal-backdrop"></div>
            <div class="settings-modal modal-content modal-dialog" role="dialog" aria-modal="true" aria-labelledby="disconnect-google-title">
                <h3 id="disconnect-google-title">Disconnect Google Sheets?</h3>
                <p>Are you sure? This will stop all automatic data syncing.</p>
                <p>Your existing sheets will not be deleted.</p>
                <form method="post" action="/admin/settings">
                    <input type="hidden" name="action" value="disconnect_google_sheets">
                    <a class="btn settings-action secondary" href="/admin/settings">Keep connected</a>
                    <button class="btn settings-action danger" type="submit">Yes, disconnect</button>
                </form>
            </div>
        <?php endif; ?>
        <?php
    }
}

if (!function_exists('render_security_settings_card')) {
    function render_security_settings_card(): void {
        $timeout = (int)    ($_SESSION['security_timeout'] ?? 30);
        $enableEncryption = (bool) ($_SESSION['enable_encryption'] ?? true);
        $error   = (string) ($_SESSION['security_error']   ?? '');
        $success = (string) ($_SESSION['security_success'] ?? '');
        ?>
        <div class="card settings-card security-settings-card">
            <div class="settings-card-header">
                <div>
                    <h2>Session Timeout</h2>
                    <p class="muted">Session management, encryption, and access control.</p>
                </div>
            </div>

            <form method="post" action="/admin/settings" class="settings-form">
                <input type="hidden" name="action" value="save_security">

                <?php if ($error !== ''): ?>
                    <p class="alert error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <?php if ($success !== ''): ?>
                    <p class="alert success"><?= htmlspecialchars($success) ?></p>
                <?php endif; ?>

                <div class="settings-field">
                    <label for="security-timeout">Session Timeout (minutes)</label>
                    <input id="security-timeout" type="number" name="security_timeout" min="1" value="<?= htmlspecialchars((string) $timeout) ?>" class="form-control settings-input">
                </div>

                <div class="settings-field">
                    <div class="settings-option-row">
                        <div>
                            <p class="settings-option-label">Data encryption (in transit &amp; at rest)</p>
                            <p class="settings-note">Recommended for compliance.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="enable_encryption" <?= $enableEncryption ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="settings-field settings-info-block">
                    <p class="settings-info-title">Role-based access (RBAC)</p>
                    <p class="settings-note">Always enforced. Staff cannot access admin areas. All role checks are server-side.</p>
                </div>

                <button type="submit" class="btn settings-action">Save Security Settings</button>
            </form>
        </div>
        <?php
    }
}

if (!function_exists('render_data_retention_settings_card')) {
    function render_data_retention_settings_card(): void {
        $days    = (int)   ($_SESSION['retention_days']    ?? 365);
        $records = (array) ($_SESSION['retention_records'] ?? []);
        $error   = (string)($_SESSION['retention_error']   ?? '');
        $success = (string)($_SESSION['retention_success'] ?? '');
        ?>
        <div class="card settings-card data-retention-settings-card">
            <div class="settings-card-header">
                <div>
                    <h2>Data Retention</h2>
                    <p class="muted">Auto-purge attendance records older than the threshold.</p>
                </div>
            </div>

            <form method="post" action="/admin/settings" class="settings-form">
                <input type="hidden" name="action" value="save_retention_days">

                <label for="retention-days">Keep records for (days)</label>
                <input
                    id="retention-days"
                    name="retention_days"
                    type="number"
                    min="1"
                    step="1"
                    value="<?= htmlspecialchars((string) $days) ?>"
                    class="form-control settings-input"
                    required
                >

                <?php if ($error !== ''): ?>
                    <p class="alert error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <div class="settings-form-actions">
                    <button class="btn settings-action" type="submit" name="action" value="save_retention_days">Save retention days</button>
                    <button class="btn settings-action danger" type="submit" name="action" value="purge_retention">Purge Old Records</button>
                </div>
            </form>
        </div>
        <?php
    }
}
