<?php

declare(strict_types=1);

$attendance = require __DIR__ . '/../../data/mock_attendance.php';
$status = strtolower($_GET['status'] ?? 'all');
$search = trim($_GET['search'] ?? '');
$currentView = strtolower($_GET['view'] ?? 'history');

if (!in_array($currentView, ['calendar', 'history'], true)) {
    $currentView = 'history';
}

$viewUrl = static function (string $view) use ($status, $search): string {
    return app_url('/history') . '?' . http_build_query([
        'view' => $view,
        'search' => $search,
        'status' => $status,
    ]);
};

$filteredRecords = array_values(array_filter(
    $attendance,
    static function (array $record) use ($status, $search): bool {
        if ($status !== 'all' && strtolower($record['syncStatus']) !== $status) {
            return false;
        }

        if ($search === '') {
            return true;
        }

        $needle = strtolower($search);

        return str_contains(strtolower($record['staff']), $needle)
            || str_contains(strtolower($record['location']), $needle);
    }
));

$attendanceEvents = [
    '2026-05-12' => 'Clocked in at 08:45 AM, out at 04:45 PM',
    '2026-05-18' => 'Clocked in at 09:00 AM, out at 05:00 PM',
    '2026-05-22' => 'Clocked in at 09:15 AM, out at 05:30 PM',
    '2026-05-25' => 'Clocked in at 09:00 AM, out at 05:00 PM',
];

ob_start();
?>

<div class="app-shell">
    <?php require __DIR__ . '/../partials/staff_sidebar.php'; ?>

    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content">
            <section class="staff-history container-fluid p-4 p-lg-5">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
                    <div>
                        <h1 class="staff-history-title mb-1">Attendance History</h1>
                        <p class="text-muted mb-0">Review your attendance activity and sync status.</p>
                    </div>

                    <button type="button" class="btn btn-main" onclick="downloadStaffHistory()">
                        <i class="bi bi-download" aria-hidden="true"></i>
                        Export CSV
                    </button>
                </div>

                <nav class="staff-history-toggle mb-4" aria-label="Attendance history view">
                    <a href="<?= e($viewUrl('calendar')) ?>"
                       class="staff-history-toggle-link <?= $currentView === 'calendar' ? 'active' : '' ?>"
                       <?= $currentView === 'calendar' ? 'aria-current="page"' : '' ?>>
                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                        Calendar View
                    </a>
                    <a href="<?= e($viewUrl('history')) ?>"
                       class="staff-history-toggle-link <?= $currentView === 'history' ? 'active' : '' ?>"
                       <?= $currentView === 'history' ? 'aria-current="page"' : '' ?>>
                        <i class="bi bi-clock-history" aria-hidden="true"></i>
                        History
                    </a>
                </nav>

                <?php if ($currentView === 'calendar'): ?>
                    <section
                        class="page-card calendar-container p-4 mb-4"
                        x-data="initCalendar(<?= e(json_encode($attendanceEvents, JSON_THROW_ON_ERROR)) ?>)"
                        x-init="generateCalendar()"
                    >
                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                            <div>
                                <h2 class="h5 fw-bold mb-1" x-text="monthNames[currentMonth] + ' ' + currentYear"></h2>
                                <p class="text-muted mb-0">Dates with green dots include Clocked in and Clocked out records.</p>
                            </div>

                            <div class="btn-group" aria-label="Calendar month navigation">
                                <button type="button" class="btn btn-outline-secondary" @click="prevMonth()">
                                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" @click="nextMonth()">
                                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>

                        <div class="calendar-grid">
                            <div class="calendar-weekdays">
                                <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday): ?>
                                    <div><?= e($weekday) ?></div>
                                <?php endforeach; ?>
                            </div>

                            <div class="calendar-days">
                                <template x-for="day in days" :key="day.id">
                                    <button
                                        type="button"
                                        class="calendar-day"
                                        :class="{ 'calendar-day-today': day.isToday }"
                                        :disabled="!day.dateNumber"
                                        :data-bs-toggle="day.hasEvent ? 'popover' : null"
                                        :data-bs-content="day.eventInfo"
                                        data-bs-trigger="focus"
                                        data-bs-placement="top"
                                        title="Attendance Info"
                                    >
                                        <span x-text="day.dateNumber"></span>
                                        <span class="green-dot bg-success" x-show="day.hasEvent" aria-hidden="true"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </section>
                <?php else: ?>
                    <form method="get"
                          action="<?= e(app_url('/history')) ?>"
                          class="page-card p-3 mb-4">
                        <input type="hidden" name="view" value="history">
                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label class="form-label" for="history-search">Search</label>
                                <input id="history-search"
                                       type="search"
                                       name="search"
                                       value="<?= e($search) ?>"
                                       class="form-control"
                                       placeholder="Search staff or location">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label" for="history-status">Status</label>
                                <select id="history-status"
                                        name="status"
                                        class="form-select"
                                        onchange="this.form.submit()">
                                    <?php foreach (['all' => 'All statuses', 'synced' => 'Synced', 'pending' => 'Pending', 'failed' => 'Failed'] as $value => $label): ?>
                                        <option value="<?= e($value) ?>" <?= $status === $value ? 'selected' : '' ?>>
                                            <?= e($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>

                    <div class="page-card staff-history-table">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Event</th>
                                        <th>Time</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($filteredRecords === []): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">No attendance records found.</td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php foreach ($filteredRecords as $record): ?>
                                        <tr>
                                            <td><?= e($record['date']) ?></td>
                                            <td><?= e($record['type']) ?></td>
                                            <td><?= e($record['timestamp']) ?></td>
                                            <td><?= e($record['location']) ?></td>
                                            <td>
                                                <span class="history-status history-status-<?= e(strtolower($record['syncStatus'])) ?>">
                                                    <?= e($record['syncStatus']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>

<script>
function downloadStaffHistory() {
    const rows = <?= json_encode($filteredRecords, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const csv = [
        ['Date', 'Event', 'Time', 'Location', 'Status'],
        ...rows.map((row) => [row.date, row.type, row.timestamp, row.location, row.syncStatus]),
    ]
        .map((row) => row.map((cell) => `"${String(cell).replaceAll('"', '""')}"`).join(','))
        .join('\n');
    const link = document.createElement('a');
    link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
    link.download = 'attendance-history.csv';
    link.click();
    URL.revokeObjectURL(link.href);
}
</script>

<?php
$content = ob_get_clean();

require __DIR__ . '/../layouts/app.php';
