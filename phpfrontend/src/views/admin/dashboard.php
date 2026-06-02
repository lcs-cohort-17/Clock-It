<section class="page-stack" x-data="adminDashboard()">
    <div class="hero-panel">
        <span class="badge-soft teal">Live workforce pulse</span>
        <h2 class="hero-title mt-3">Attendance visibility, QR governance, and review queues in one command center.</h2>
        <p class="hero-copy">Clock-It keeps onsite counts, clock-in events, review status, and staff views aligned from the same mock state.</p>
    </div>

    <div class="stats-grid">
        <template x-for="card in metricCards" :key="card.label">
            <div class="stat-card">
                <div class="stat-card-icon" :class="card.accent"><?= ui_icon('spark') ?></div>
                <span x-text="card.label"></span>
                <strong x-text="card.value"></strong>
                <small x-text="card.helper"></small>
            </div>
        </template>
    </div>

    <div class="action-grid">
        <a class="action-card" href="<?= route_url('/admin/qr') ?>">
            <?= ui_icon('qr') ?>
            <strong>Issue global QR</strong>
            <span>Generate, revoke, regenerate, and download clock QR assets.</span>
        </a>
        <a class="action-card" href="<?= route_url('/admin/attendance') ?>">
            <?= ui_icon('clock') ?>
            <strong>Review attendance</strong>
            <span>Resolve pending scans and audit device activity.</span>
        </a>
        <a class="action-card" href="<?= route_url('/admin/users') ?>">
            <?= ui_icon('users') ?>
            <strong>Add employee</strong>
            <span>Create staff and admin records with role controls.</span>
        </a>
        <a class="action-card" href="<?= route_url('/admin/calendar') ?>">
            <?= ui_icon('calendar') ?>
            <strong>Open calendar</strong>
            <span>Scan month-level attendance, leave, and holiday markers.</span>
        </a>
    </div>

    <div class="split-grid">
        <section class="surface">
            <div class="surface-header">
                <div>
                    <h2>Recent Activity</h2>
                    <p class="surface-subtitle">Today sorted by latest timestamp.</p>
                </div>
                <span class="badge-soft violet" x-text="`${recentActivity.length} rows`"></span>
            </div>

            <div class="table-shell">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Device</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="record in recentActivity" :key="record.id">
                            <tr>
                                <td>
                                    <strong x-text="record.employeeName"></strong>
                                    <div class="table-muted" x-text="`${record.employeeId} / ${record.department}`"></div>
                                </td>
                                <td x-text="record.type"></td>
                                <td x-text="record.device"></td>
                                <td><span :class="statusClass(record.status)" x-text="record.status"></span></td>
                                <td x-text="formatTime(record.timestamp)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="surface">
            <div class="surface-header">
                <div>
                    <h2>Live Onsite</h2>
                    <p class="surface-subtitle">Clocked in without a completed clock out.</p>
                </div>
            </div>

            <div class="list-stack">
                <template x-for="person in onsiteStaff" :key="person.employeeId">
                    <div class="mini-row">
                        <div>
                            <strong x-text="person.name"></strong>
                            <span x-text="`${person.department} / in at ${formatTime(person.summary.clockIn.timestamp)}`"></span>
                        </div>
                        <span class="badge-soft teal">Onsite</span>
                    </div>
                </template>
                <template x-if="!onsiteStaff.length">
                    <div class="feedback-panel">No active onsite staff.</div>
                </template>
            </div>
        </aside>
    </div>

    <section class="surface">
        <div class="surface-header">
            <div>
                <h2>Department Presence</h2>
                <p class="surface-subtitle">Active staff currently onsite by department.</p>
            </div>
        </div>
        <div class="three-grid">
            <template x-for="department in departments" :key="department.department">
                <div class="mini-row d-block">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                        <strong x-text="department.department"></strong>
                        <span class="badge-soft" x-text="`${department.onsite}/${department.total}`"></span>
                    </div>
                    <div class="progress" role="progressbar" aria-label="Presence">
                        <div class="progress-bar" :style="`width: ${department.percent}%`"></div>
                    </div>
                </div>
            </template>
        </div>
    </section>
</section>
