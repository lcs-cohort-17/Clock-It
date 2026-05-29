<?php ob_start(); ?>
<main class="container py-4">
    <section class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Admin Dashboard</h1>
            <p class="text-body-secondary mb-0">Manage attendance and export clock-event logs for <?= htmlspecialchars($user['name']) ?>.</p>
        </div>

        <div
            x-data="adminAttendanceExport($el.dataset.apiEndpoint)"
            data-api-endpoint="/api/attendance/clock-events"
            class="d-flex flex-column align-items-start align-items-md-end gap-2"
        >
            <button
                type="button"
                class="btn btn-primary d-inline-flex align-items-center gap-2"
                x-on:click="exportLogs"
                x-bind:disabled="loading"
                x-bind:aria-busy="loading.toString()"
            >
                <span x-show="!loading" aria-hidden="true">&darr;</span>
                <span x-show="loading" x-cloak class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                <span x-text="loading ? 'Exporting...' : 'Export Logs to CSV'">Export Logs to CSV</span>
            </button>

            <div
                x-show="error"
                x-cloak
                class="alert alert-danger py-2 px-3 mb-0"
                role="alert"
                x-text="error"
            ></div>
        </div>
    </section>

    <section class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-body-secondary mb-1">Onsite</p>
                    <p class="h3 mb-0"><?= (int) $stats['currentlyOnsite'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-body-secondary mb-1">Staff today</p>
                    <p class="h3 mb-0"><?= (int) $stats['totalStaffToday'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-body-secondary mb-1">Pending sync</p>
                    <p class="h3 mb-0"><?= (int) $stats['pendingSync'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-body-secondary mb-1">Events</p>
                    <p class="h3 mb-0"><?= (int) $stats['totalEvents'] ?></p>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Currently onsite</h2>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($onsiteStaff as $staff): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($staff['name']) ?></div>
                                <div class="small text-body-secondary"><?= htmlspecialchars($staff['employeeId']) ?></div>
                            </div>
                            <span class="badge text-bg-success"><?= htmlspecialchars($staff['clockedInAt']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Recent events</h2>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Staff member</th>
                                <th>Event</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?= htmlspecialchars($event['userName']) ?></td>
                                    <td><?= htmlspecialchars($event['type']) ?></td>
                                    <td><?= htmlspecialchars($event['timestamp']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>
