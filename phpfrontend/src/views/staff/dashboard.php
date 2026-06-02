<section class="page-stack" x-data="staffDashboard()">
    <div class="hero-panel">
        <span :class="statusClass(summary.status)" x-text="summary.status"></span>
        <h2 class="hero-title mt-3">Welcome back, <?= e($user['name']) ?>.</h2>
        <p class="hero-copy">Your current attendance state, shift timing, QR actions, and recent history are synced from the shared frontend dataset.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon"><?= ui_icon('clock') ?></div>
            <span>Clock in</span>
            <strong x-text="formatTime(summary.clockIn?.timestamp)"></strong>
            <small x-text="summary.clockIn ? summary.clockIn.device : 'No clock in today'"></small>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon"><?= ui_icon('logout') ?></div>
            <span>Clock out</span>
            <strong x-text="formatTime(summary.clockOut?.timestamp)"></strong>
            <small x-text="summary.clockOut ? summary.clockOut.device : 'Clock out pending'"></small>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon"><?= ui_icon('spark') ?></div>
            <span>Total hours</span>
            <strong x-text="totalHoursLabel"></strong>
            <small>Calculated from current session</small>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon"><?= ui_icon('shield') ?></div>
            <span>Status</span>
            <strong style="font-size:1.4rem" x-text="summary.status"></strong>
            <small x-text="summary.clockIn?.status || 'Ready'"></small>
        </div>
    </div>

    <div class="action-grid">
        <a class="action-card" href="<?= route_url('/staff/scan-qr') ?>">
            <?= ui_icon('qr') ?>
            <strong>Scan QR</strong>
            <span>Clock in or clock out with global QR state validation.</span>
        </a>
        <a class="action-card" href="<?= route_url('/staff/history') ?>">
            <?= ui_icon('clock') ?>
            <strong>History</strong>
            <span>Review list and calendar attendance records.</span>
        </a>
        <a class="action-card" href="<?= route_url('/staff/calendar') ?>">
            <?= ui_icon('calendar') ?>
            <strong>Calendar</strong>
            <span>See work schedule, attendance, leave, and holidays.</span>
        </a>
        <a class="action-card" href="<?= route_url('/staff/profile') ?>">
            <?= ui_icon('users') ?>
            <strong>Profile</strong>
            <span>Maintain contact and account settings.</span>
        </a>
    </div>

    <div class="split-grid">
        <section class="surface">
            <div class="surface-header">
                <div>
                    <h2>Today's Activity</h2>
                    <p class="surface-subtitle">Clock events for the current day.</p>
                </div>
                <span class="badge-soft violet" x-text="`${todayEvents.length} events`"></span>
            </div>
            <div class="list-stack">
                <template x-for="record in todayEvents" :key="record.id">
                    <div class="mini-row">
                        <div>
                            <strong x-text="record.type"></strong>
                            <span x-text="`${record.device} / ${record.qrUsed}`"></span>
                        </div>
                        <span :class="statusClass(record.status)" x-text="record.status"></span>
                    </div>
                </template>
            </div>
        </section>

        <aside class="surface">
            <div class="surface-header">
                <div>
                    <h2>Upcoming Schedule</h2>
                    <p class="surface-subtitle">Next assigned shifts.</p>
                </div>
            </div>
            <div class="list-stack">
                <template x-for="shift in upcomingSchedule" :key="shift.date + shift.shift">
                    <div class="mini-row">
                        <div>
                            <strong x-text="formatDate(shift.date)"></strong>
                            <span x-text="shift.location"></span>
                        </div>
                        <span class="badge-soft violet" x-text="shift.shift"></span>
                    </div>
                </template>
            </div>
        </aside>
    </div>

    <section class="surface">
        <div class="surface-header">
            <h2>Recent Attendance History</h2>
            <a class="btn btn-outline-light" href="<?= route_url('/staff/history') ?>">Open history</a>
        </div>
        <div class="table-shell">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Device</th>
                        <th>Status</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="record in recentHistory" :key="record.id">
                        <tr>
                            <td x-text="record.type"></td>
                            <td x-text="record.device"></td>
                            <td><span :class="statusClass(record.status)" x-text="record.status"></span></td>
                            <td x-text="formatDateTime(record.timestamp)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </section>
</section>
