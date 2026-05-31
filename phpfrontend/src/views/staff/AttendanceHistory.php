<?php
// 1. App State & Query Parameters
$currentTab    = $_GET['tab'] ?? 'clock-events';
$currentView   = $_GET['view'] ?? 'list';
$currentStaff  = $_GET['staff'] ?? 'all';
$currentStatus = $_GET['status'] ?? 'all';
$searchQuery   = $_GET['search'] ?? '';

// 2. Mock Attendance Data (Used for both List Rows and Calendar Indicators)
$mockAttendance = [
    ['id' => 1, 'date' => '2026-03-11', 'staff' => 'Lerato Khumalo', 'type' => 'Clock In', 'timestamp' => '2026-03-11 08:12 AM', 'device' => 'Mobile App', 'location' => 'Main HQ', 'syncStatus' => 'Synced', 'hours' => '8.0 hrs', 'clockOut' => '04:12 PM'],

    ['id' => 2, 'date' => '2026-03-12', 'staff' => 'Thabo Ndlovu', 'type' => 'Clock Out', 'timestamp' => '2026-03-12 05:10 PM', 'device' => 'Web Portal', 'location' => 'Remote', 'syncStatus' => 'Pending', 'hours' => '8.2 hrs', 'clockIn' => '09:00 AM'],

    ['id' => 3, 'date' => '2026-03-13', 'staff' => 'Amanda Sithole', 'type' => 'Clock In', 'timestamp' => '2026-03-13 07:55 AM', 'device' => 'Biometric Scanner', 'location' => 'Branch Office', 'syncStatus' => 'Synced', 'hours' => '8.5 hrs', 'clockOut' => '04:25 PM'],

    ['id' => 4, 'date' => '2026-03-14', 'staff' => 'Sipho Dlamini', 'type' => 'Clock In', 'timestamp' => '2026-03-14 08:30 AM', 'device' => 'Mobile App', 'location' => 'Warehouse', 'syncStatus' => 'Failed', 'hours' => '7.5 hrs', 'clockOut' => '04:00 PM'],

    ['id' => 5, 'date' => '2026-03-15', 'staff' => 'Naledi Mokoena', 'type' => 'Clock Out', 'timestamp' => '2026-03-15 05:05 PM', 'device' => 'Desktop App', 'location' => 'Main HQ', 'syncStatus' => 'Synced', 'hours' => '8.0 hrs', 'clockIn' => '09:05 AM'],

    ['id' => 6, 'date' => '2026-03-16', 'staff' => 'Brian Zulu', 'type' => 'Clock In', 'timestamp' => '2026-03-16 08:20 AM', 'device' => 'Tablet', 'location' => 'Remote', 'syncStatus' => 'Pending', 'hours' => '7.8 hrs', 'clockOut' => '04:08 PM'],

    ['id' => 7, 'date' => '2026-03-17', 'staff' => 'Grace Mabena', 'type' => 'Clock In', 'timestamp' => '2026-03-17 08:00 AM', 'device' => 'Mobile App', 'location' => 'Main HQ', 'syncStatus' => 'Synced', 'hours' => '8.0 hrs', 'clockOut' => '04:00 PM'],

    ['id' => 8, 'date' => '2026-03-18', 'staff' => 'Peter Nkosi', 'type' => 'Clock Out', 'timestamp' => '2026-03-18 05:20 PM', 'device' => 'Web Portal', 'location' => 'Branch Office', 'syncStatus' => 'Synced', 'hours' => '8.3 hrs', 'clockIn' => '09:00 AM'],

    ['id' => 9, 'date' => '2026-03-19', 'staff' => 'Faith Tshabalala', 'type' => 'Clock In', 'timestamp' => '2026-03-19 08:40 AM', 'device' => 'Biometric Scanner', 'location' => 'Warehouse', 'syncStatus' => 'Pending', 'hours' => '7.9 hrs', 'clockOut' => '04:35 PM'],

    ['id' => 10, 'date' => '2026-03-20', 'staff' => 'Daniel Mthembu', 'type' => 'Clock In', 'timestamp' => '2026-03-20 08:15 AM', 'device' => 'Desktop App', 'location' => 'Remote', 'syncStatus' => 'Synced', 'hours' => '8.1 hrs', 'clockOut' => '04:20 PM'],

    ['id' => 11, 'date' => '2026-03-21', 'staff' => 'Ayanda Nene', 'type' => 'Clock Out', 'timestamp' => '2026-03-21 05:00 PM', 'device' => 'Mobile App', 'location' => 'Main HQ', 'syncStatus' => 'Failed', 'hours' => '8.0 hrs', 'clockIn' => '09:00 AM'],

    ['id' => 12, 'date' => '2026-03-22', 'staff' => 'Jason Mabuza', 'type' => 'Clock In', 'timestamp' => '2026-03-22 08:05 AM', 'device' => 'Tablet', 'location' => 'Branch Office', 'syncStatus' => 'Synced', 'hours' => '8.4 hrs', 'clockOut' => '04:29 PM'],

    ['id' => 13, 'date' => '2026-03-23', 'staff' => 'Precious Khoza', 'type' => 'Clock In', 'timestamp' => '2026-03-23 08:11 AM', 'device' => 'Web Portal', 'location' => 'Remote', 'syncStatus' => 'Pending', 'hours' => '8.0 hrs', 'clockOut' => '04:11 PM'],

    ['id' => 14, 'date' => '2026-03-24', 'staff' => 'Chris Baloyi', 'type' => 'Clock Out', 'timestamp' => '2026-03-24 05:12 PM', 'device' => 'Biometric Scanner', 'location' => 'Warehouse', 'syncStatus' => 'Synced', 'hours' => '8.2 hrs', 'clockIn' => '09:00 AM'],

    ['id' => 15, 'date' => '2026-03-25', 'staff' => 'Linda Molefe', 'type' => 'Clock In', 'timestamp' => '2026-03-25 07:58 AM', 'device' => 'Desktop App', 'location' => 'Main HQ', 'syncStatus' => 'Synced', 'hours' => '8.6 hrs', 'clockOut' => '04:34 PM'],

    ['id' => 16, 'date' => '2026-03-26', 'staff' => 'Kevin Hlatshwayo', 'type' => 'Clock In', 'timestamp' => '2026-03-26 08:18 AM', 'device' => 'Mobile App', 'location' => 'Remote', 'syncStatus' => 'Failed', 'hours' => '7.6 hrs', 'clockOut' => '03:54 PM'],

    ['id' => 17, 'date' => '2026-03-27', 'staff' => 'Nomsa Zwane', 'type' => 'Clock Out', 'timestamp' => '2026-03-27 05:30 PM', 'device' => 'Tablet', 'location' => 'Branch Office', 'syncStatus' => 'Pending', 'hours' => '8.5 hrs', 'clockIn' => '09:00 AM'],

    ['id' => 18, 'date' => '2026-03-28', 'staff' => 'Richard Gumede', 'type' => 'Clock In', 'timestamp' => '2026-03-28 08:09 AM', 'device' => 'Web Portal', 'location' => 'Warehouse', 'syncStatus' => 'Synced', 'hours' => '8.0 hrs', 'clockOut' => '04:09 PM'],

    ['id' => 19, 'date' => '2026-03-29', 'staff' => 'Samantha Buthelezi', 'type' => 'Clock In', 'timestamp' => '2026-03-29 08:33 AM', 'device' => 'Mobile App', 'location' => 'Main HQ', 'syncStatus' => 'Synced', 'hours' => '7.7 hrs', 'clockOut' => '04:10 PM'],

    ['id' => 20, 'date' => '2026-03-30', 'staff' => 'Mpho Radebe', 'type' => 'Clock Out', 'timestamp' => '2026-03-30 05:18 PM', 'device' => 'Desktop App', 'location' => 'Remote', 'syncStatus' => 'Pending', 'hours' => '8.3 hrs', 'clockIn' => '09:00 AM'],
];

// 3. Simple PHP Filtering Logic for the List View
$filteredRecords = array_filter($mockAttendance, function($record) use ($currentStatus, $searchQuery) {
    if ($currentStatus !== 'all' && strtolower($record['syncStatus']) !== $currentStatus) {
        return false;
    }
    if (!empty($searchQuery)) {
        $search = strtolower($searchQuery);
        $matchStaff = strpos(strtolower($record['staff']), $search) !== false;
        $matchLocation = strpos(strtolower($record['location']), $search) !== false;
        if (!$matchStaff && !$matchLocation) {
            return false;
        }
    }
    return true;
});

// 4. Map events to clean string formats for Alpine.js calendar tooltips
$calendarEvents = [];
foreach ($mockAttendance as $record) {
    $dateKey = $record['date'];
    if (!isset($calendarEvents[$dateKey])) {
        $calendarEvents[$dateKey] = "Recorded Event: " . $record['type'] . " at " . date('g:i A', strtotime($record['timestamp']));
    } else {
        $calendarEvents[$dateKey] .= " | " . $record['type'] . " at " . date('g:i A', strtotime($record['timestamp']));
    }
}
?>
<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #f1f5f9; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .text-dark-blue { color: #0f2d4a; }
        .rounded-xl { border-radius: 0.75rem !important; }
        .rounded-2xl { border-radius: 1rem !important; }
        
        /* Nav Pills Frame Custom Layout */
        .nav-pills-custom {
            background-color: #e2e8f0;
            padding: 4px;
            display: inline-flex;
            border-radius: 0.75rem;
        }
        .nav-pills-custom .nav-link {
            color: #475569;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .nav-pills-custom .nav-link.active {
            background-color: #ffffff;
            color: #0f2d4a;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        /* Filter Row Inputs Custom Styles */
        .filter-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem;
        }
        .search-container { position: relative; }
        .search-container svg {
            position: absolute; left: 1rem; top: 50%;
            transform: translateY(-50%); color: #94a3b8;
        }
        .form-input-custom { padding-left: 2.75rem !important; }
        .form-select-custom, .form-input-custom {
            border: 1px solid #e2e8f0; background-color: #f8fafc;
            color: #334155; font-size: 0.925rem; height: 46px;
            border-radius: 0.75rem !important;
        }
        .form-select-custom:focus, .form-input-custom:focus {
            border-color: #cbd5e1; box-shadow: none; background-color: #ffffff;
        }

        /* Logs Table Styles */
        .logs-card {
            background-color: #ffffff; border: 1px solid #e2e8f0;
            border-radius: 0.75rem; overflow: hidden;
        }
        .table-custom th {
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;
            color: #64748b; font-weight: 600; background-color: #f8fafc;
            padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0;
        }
        .table-custom td { padding: 1rem 1.5rem; color: #334155; font-size: 0.875rem; }
        .empty-state { padding: 5rem 0; color: #64748b; font-size: 0.95rem; }
        
        /* Export Button */
        .btn-export {
            border: 1px solid #cbd5e1; background-color: #ffffff; color: #334155;
            font-weight: 500; font-size: 0.875rem; padding: 0.5rem 1rem;
            border-radius: 0.5rem; display: inline-flex; align-items: center; gap: 0.5rem;
        }
        .btn-export:hover { background-color: #f8fafc; }
    </style>
</head>
<body>

<div class="container-fluid px-4 py-4" style="max-width: 1400px;">
    
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark-blue mb-1">Attendance Logs</h1>
            <p class="text-muted small mb-0">Full clock-event history with override and audit trail.</p>
        </div>
        <div>
            <button class="btn btn-export shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                </svg>
                Export CSV
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="nav-pills-custom">
            <a href="?tab=clock-events&view=<?= urlencode($currentView) ?>&search=<?= urlencode($searchQuery) ?>&staff=<?= urlencode($currentStaff) ?>&status=<?= urlencode($currentStatus) ?>" 
               class="nav-link <?= $currentTab === 'clock-events' ? 'active' : '' ?>">Clock events</a>
            <a href="?tab=audit-trail&view=<?= urlencode($currentView) ?>&search=<?= urlencode($searchQuery) ?>&staff=<?= urlencode($currentStaff) ?>&status=<?= urlencode($currentStatus) ?>" 
               class="nav-link <?= $currentTab === 'audit-trail' ? 'active' : '' ?>">Audit trail</a>
        </div>

        <div class="nav-pills-custom">
            <a href="?view=list&tab=<?= urlencode($currentTab) ?>&search=<?= urlencode($searchQuery) ?>&staff=<?= urlencode($currentStaff) ?>&status=<?= urlencode($currentStatus) ?>" 
               class="nav-link <?= $currentView === 'list' ? 'active' : '' ?>">List view</a>
            <a href="?view=calendar&tab=<?= urlencode($currentTab) ?>&search=<?= urlencode($searchQuery) ?>&staff=<?= urlencode($currentStaff) ?>&status=<?= urlencode($currentStatus) ?>" 
               class="nav-link <?= $currentView === 'calendar' ? 'active' : '' ?>">Calendar view</a>
        </div>
    </div>

    <div class="filter-card shadow-sm mb-4">
        <form method="GET" class="row g-3">
            <input type="hidden" name="tab" value="<?= htmlspecialchars($currentTab) ?>">
            <input type="hidden" name="view" value="<?= htmlspecialchars($currentView) ?>">
            
            <div class="col-12 col-md-5">
                <div class="search-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" name="search" class="form-control form-input-custom" placeholder="Search name or location" value="<?= htmlspecialchars($searchQuery) ?>">
                </div>
            </div>

            <div class="col-12 col-md-3">
                <select name="staff" class="form-select form-select-custom" onchange="this.form.submit()">
                    <option value="all" <?= $currentStaff === 'all' ? 'selected' : '' ?>>All staff</option>
                    <option value="active" <?= $currentStaff === 'active' ? 'selected' : '' ?>>Active Staff Only</option>
                </select>
            </div>

            <div class="col-12 col-md-4">
                <select name="status" class="form-select form-select-custom" onchange="this.form.submit()">
                    <option value="all" <?= $currentStatus === 'all' ? 'selected' : '' ?>>All statuses</option>
                    <option value="synced" <?= $currentStatus === 'synced' ? 'selected' : '' ?>>Synced</option>
                    <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
        </form>
    </div>

    <?php if ($currentView === 'list'): ?>
        <div class="logs-card shadow-sm">
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Type</th>
                            <th>Timestamp</th>
                            <th>Device</th>
                            <th>Location</th>
                            <th>Sync</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($filteredRecords)): ?>
                            <tr>
                                <td colspan="7" class="text-center empty-state">No records.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($filteredRecords as $row): ?>
                                <tr>
                                    <td class="fw-medium text-dark-blue"><?= htmlspecialchars($row['staff']) ?></td>
                                    <td><span class="badge bg-light text-secondary border px-2.5 py-1.5"><?= htmlspecialchars($row['type']) ?></span></td>
                                    <td><?= htmlspecialchars($row['timestamp']) ?></td>
                                    <td><?= htmlspecialchars($row['device']) ?></td>
                                    <td><?= htmlspecialchars($row['location']) ?></td>
                                    <td>
                                        <span class="text-<?= $row['syncStatus'] === 'Synced' ? 'success' : 'warning' ?> fw-medium">
                                            ● <?= htmlspecialchars($row['syncStatus']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4"><a href="#" class="btn btn-sm btn-link text-decoration-none">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <?php include 'CalendarView.php'; ?>
    <?php endif; ?>

</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $content = ob_get_clean();require __DIR__ . '/../layouts/app.php';
