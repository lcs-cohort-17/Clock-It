<?php
/**
 * attendance.php  (src/views/admin/attendance.php)
 *
 * Attendance Logs page — Clock Events + Audit Trail tabs.
 * Data is PHP-seeded via window.ATTENDANCE_DATA; all UI logic
 * is handled by Alpine.js with utility functions from attendance_log_utils.js.
 *
 * Layout: uses ob_start() / ob_get_clean() pattern to inject $content
 * into the shared app.php shell (which provides <html>, <head>, Bootstrap,
 * Alpine.js, the sidebar, and the topbar header).
 *
 * What to update when the API is ready:
 *   - Replace the $clockEvents and $auditTrail arrays below with API responses.
 *   - Everything else (filtering, sorting, CSV export, modal) stays unchanged.
 */

// ─────────────────────────────────────────────────────────────
// Mock data  (swap for API calls: GET /api/admin/attendance
//             and GET /api/admin/attendance/audit — BE-06)
// ─────────────────────────────────────────────────────────────
$clockEvents = [
    [
        'id'        => 1,
        'staff'     => 'Amara Nwosu',
        'type'      => 'Clock In',
        'timestamp' => '2026-05-27 08:02:14',
        'device'    => 'Terminal A',
        'location'  => 'Main Office',
        'sync'      => 'Synced',
    ],
    [
        'id'        => 2,
        'staff'     => 'Amara Nwosu',
        'type'      => 'Clock Out',
        'timestamp' => '2026-05-27 17:05:33',
        'device'    => 'Terminal A',
        'location'  => 'Main Office',
        'sync'      => 'Synced',
    ],
    [
        'id'        => 3,
        'staff'     => 'Sipho Dlamini',
        'type'      => 'Clock In',
        'timestamp' => '2026-05-27 07:58:01',
        'device'    => 'Terminal B',
        'location'  => 'Warehouse',
        'sync'      => 'Synced',
    ],
    [
        'id'        => 4,
        'staff'     => 'Sipho Dlamini',
        'type'      => 'Clock Out',
        'timestamp' => '2026-05-27 16:30:44',
        'device'    => 'Terminal B',
        'location'  => 'Warehouse',
        'sync'      => 'Pending',
    ],
    [
        'id'        => 5,
        'staff'     => 'Naledi Khumalo',
        'type'      => 'Clock In',
        'timestamp' => '2026-05-27 09:15:22',
        'device'    => 'Mobile App',
        'location'  => 'Remote',
        'sync'      => 'Synced',
    ],
    [
        'id'        => 6,
        'staff'     => 'Naledi Khumalo',
        'type'      => 'Clock Out',
        'timestamp' => '2026-05-27 18:01:09',
        'device'    => 'Mobile App',
        'location'  => 'Remote',
        'sync'      => 'Synced',
    ],
    [
        'id'        => 7,
        'staff'     => 'Themba Mthembu',
        'type'      => 'Clock In',
        'timestamp' => '2026-05-26 08:45:00',
        'device'    => 'Terminal A',
        'location'  => 'Main Office',
        'sync'      => 'Synced',
    ],
    [
        'id'        => 8,
        'staff'     => 'Themba Mthembu',
        'type'      => 'Clock Out',
        'timestamp' => '2026-05-26 17:30:18',
        'device'    => 'Terminal A',
        'location'  => 'Main Office',
        'sync'      => 'Synced',
    ],
];

$auditTrail = [
    [
        'id'        => 1,
        'timestamp' => '2026-05-27 14:30:00',
        'actor'     => 'Admin Jane',
        'action'    => 'EDIT',
        'details'   => 'Modified clock-out time for Amara Nwosu (17:00 → 17:05)',
    ],
    [
        'id'        => 2,
        'timestamp' => '2026-05-27 11:12:45',
        'actor'     => 'Admin Jane',
        'action'    => 'OVERRIDE',
        'details'   => 'Manual clock-in added for Sipho Dlamini',
    ],
    [
        'id'        => 3,
        'timestamp' => '2026-05-26 16:55:10',
        'actor'     => 'Admin Kobus',
        'action'    => 'DELETE',
        'details'   => 'Removed duplicate clock-out entry for Naledi Khumalo',
    ],
    [
        'id'        => 4,
        'timestamp' => '2026-05-26 09:03:22',
        'actor'     => 'Admin Kobus',
        'action'    => 'EDIT',
        'details'   => 'Corrected device from "Unknown" to "Terminal A" for Themba Mthembu',
    ],
];

// Pre-compute lists for PHP-rendered dropdown options
$staffList = array_values(array_unique(array_column($clockEvents, 'staff')));
$typeList  = array_values(array_unique(array_column($clockEvents, 'type')));
sort($staffList);
sort($typeList);
?>
<?php ob_start(); ?>

<style>
    /* ── Page ── */
    body {
        background-color: #e8ecf0;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
                     'Helvetica Neue', Arial, sans-serif;
        min-height: 100vh;
    }

    /* ── Page header typography ── */
    .page-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1a2332;
        line-height: 1.2;
        margin-bottom: 4px;
    }
    .page-subtitle {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 0;
    }

    /* ── Tab toggle ──
       Active tab:   white card, bold, bordered.
       Inactive tab: transparent, muted text.
    */
    .tab-group {
        display: flex;
        gap: 4px;
    }
    .tab-btn {
        padding: 6px 18px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid transparent;
        background: transparent;
        color: #94a3b8;
        transition: background 0.15s ease, color 0.15s ease,
                    border-color 0.15s ease, box-shadow 0.15s ease;
        white-space: nowrap;
    }
    .tab-btn:hover:not(.active) {
        color: #475569;
    }
    .tab-btn.active {
        background: #ffffff;
        color: #1e293b;
        font-weight: 700;
        border-color: #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    /* ── Filter bar ── */
    .filter-bar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 16px;
    }

    /* ── Search input ── */
    .search-wrapper {
        position: relative;
    }
    .search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }
    .search-input {
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 8px 12px 8px 36px;
        font-size: 0.875rem;
        color: #1e293b;
        background: #ffffff;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .search-input::placeholder {
        color: #94a3b8;
    }
    .search-input:focus {
        border-color: #1e293b;
        box-shadow: 0 0 0 3px rgba(30, 41, 59, 0.08);
    }

    /* ── Custom dropdown ──
       Selected item: green #6aad2d, white checkmark.
    */
    .custom-dropdown {
    position: relative;
    z-index: 200;
    }
    .custom-dropdown-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 0.875rem;
        color: #1e293b;
        background: #ffffff;
        cursor: pointer;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        gap: 8px;
    }
    .custom-dropdown-btn:focus,
    .custom-dropdown-btn[aria-expanded="true"] {
        border-color: #1e293b;
        box-shadow: 0 0 0 3px rgba(30, 41, 59, 0.08);
    }
    .custom-dropdown-btn .chevron {
        flex-shrink: 0;
        color: #94a3b8;
        transition: transform 0.2s ease;
    }
    .custom-dropdown-btn[aria-expanded="true"] .chevron {
        transform: rotate(180deg);
    }
    .custom-dropdown-menu {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        z-index: 100;
        overflow: hidden;
    }
    .custom-dropdown-item {
        display: flex;
        align-items: center;
        padding: 10px 14px;
        font-size: 0.875rem;
        color: #1e293b;
        cursor: pointer;
        user-select: none;
        transition: background 0.1s ease;
        gap: 8px;
    }
    .custom-dropdown-item:not(.selected):hover {
    background: #6aad2d;
    color: #ffffff;
    }
    .custom-dropdown-item.selected {
        background: #6aad2d;
        color: #ffffff;
    }
    .item-check {
        width: 16px;
        flex-shrink: 0;
    }
    .item-check.hidden {
        visibility: hidden;
    }

    /* ── Data table card ── */
    .data-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    /* ── Table ── */
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    .data-table thead th {
        padding: 12px 16px;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #94a3b8;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
        white-space: nowrap;
    }
    .data-table tbody td {
        padding: 14px 16px;
        font-size: 0.875rem;
        color: #374151;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .data-table tbody tr:last-child td {
        border-bottom: none;
    }
    .data-table tbody tr:hover td {
        background: #f8fafc;
    }

    /* ── No-records row ── */
    .no-records {
        text-align: center;
        padding: 56px 16px;
        color: #94a3b8;
        font-size: 0.875rem;
    }

    /* ── Sort button (Timestamp column in audit trail) ── */
    .sort-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: none;
        border: none;
        cursor: pointer;
        color: #94a3b8;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 0;
        transition: color 0.15s ease;
    }
    .sort-btn:hover {
        color: #1e293b;
    }
    .sort-icon {
        display: inline-flex;
        flex-direction: column;
        gap: 1px;
        line-height: 1;
    }

    /* ── Export / action buttons ──
       Idle: white bg, slate border.
       Hover: green #6aad2d bg, white text.
    */
    .export-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: #ffffff;
        color: #1e293b;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        text-decoration: none;
        white-space: nowrap;
    }
    .export-btn:hover {
        background: #6aad2d;
        border-color: #6aad2d;
        color: #ffffff;
    }

    /* ── Request Leave/Sick button ── */
    .request-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: #ffffff;
        color: #1e293b;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        white-space: nowrap;
    }
    .request-btn:hover {
    background: #6aad2d;
    border-color: #6aad2d;
    color: #ffffff;
    }

    /* ── Sync badges ── */
    .sync-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .sync-badge.synced  { background: #dcfce7; color: #16a34a; }
    .sync-badge.pending { background: #fef9c3; color: #a16207; }
    .sync-badge.failed  { background: #fee2e2; color: #dc2626; }

    /* ── Action edit button ── */
    .action-btn {
        padding: 4px 10px;
        font-size: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        transition: border-color 0.15s ease, color 0.15s ease;
    }
    .action-btn:hover {
        border-color: #94a3b8;
        color: #1e293b;
    }

    /* ── Audit action badges ── */
    .audit-action {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
    }
    .audit-action.edit     { background: #dbeafe; color: #1d4ed8; }
    .audit-action.override { background: #fef3c7; color: #b45309; }
    .audit-action.delete   { background: #fee2e2; color: #dc2626; }
    .audit-action.create   { background: #dcfce7; color: #16a34a; }

    /* ── Modal backdrop ──
       Controlled by Alpine x-show, not Bootstrap's JS.
    */
    .modal-backdrop-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        overflow-y: auto;
    }
    .modal-box {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        width: 100%;
        max-width: 500px;
        position: relative;
    }
    .modal-header-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px 16px;
        border-bottom: 1px solid #e2e8f0;
    }
    .modal-title-custom {
        font-size: 1rem;
        font-weight: 700;
        color: #1a2332;
        margin: 0;
    }
    .modal-close-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: #94a3b8;
        padding: 4px;
        border-radius: 4px;
        line-height: 1;
        transition: color 0.15s ease, background 0.15s ease;
    }
    .modal-close-btn:hover {
        color: #1e293b;
        background: #f1f5f9;
    }
    .modal-body-custom {
        padding: 24px;
    }
    .modal-footer-custom {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 16px 24px 20px;
        border-top: 1px solid #e2e8f0;
    }

    /* ── Tab transition ── */
    [x-cloak] { display: none !important; }
</style>

<div class="app-shell">
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <div class="main-panel">
        <?php // require __DIR__ . '/../partials/header.php'; // add when ready ?>
        <main class="content">

            <!-- ── PHP → JS data bridge ─────────────────────── -->
            <script>
                window.ATTENDANCE_DATA = {
                    clockEvents: <?= json_encode($clockEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                    auditTrail:  <?= json_encode($auditTrail,  JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
                };
            </script>

            <!-- ── Utility functions (pure, injectable, testable) ── -->
            <script src="/assets/js/attendance_log_utils.js"></script>

            <!-- ═══════════════════════════════════════════════════
                 Page root — single Alpine component
                 ═══════════════════════════════════════════════════ -->
            <div
                class="container-fluid py-4 px-4"
                x-data="attendancePage()"
                x-effect="window.ExportCSVButton && window.ExportCSVButton.setRows(filteredClockEvents)"
                x-cloak
            >

                <!-- ── Page header ──────────────────────────── -->
                 <?php require __DIR__ . '/../partials/header.php'; ?>
                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                        <h1 class="page-title">Attendance Logs</h1>
                        <p class="page-subtitle">Full clock-event history with override and audit trail.</p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <!-- Request Leave/Sick — Ticket 1 -->
                        <button class="request-btn" @click="openModal()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Request Leave/Sick
                        </button>

                        <?php include __DIR__ . '/../partials/export_csv_button.php'; ?>
                    </div>
                </div>

                <!-- ── Tab toggle ────────────────────────────── -->
                <div class="tab-group mb-3" role="tablist">
                    <button
                        class="tab-btn"
                        :class="{ active: activeTab === 'clock-events' }"
                        @click="activeTab = 'clock-events'"
                        role="tab"
                        :aria-selected="activeTab === 'clock-events'"
                    >Clock events</button>

                    <button
                        class="tab-btn"
                        :class="{ active: activeTab === 'audit-trail' }"
                        @click="activeTab = 'audit-trail'"
                        role="tab"
                        :aria-selected="activeTab === 'audit-trail'"
                    >Audit trail</button>
                </div>


                <!-- ═══════════════════════════════════════════
                     CLOCK EVENTS TAB
                     ═══════════════════════════════════════════ -->
                <div
                    x-show="activeTab === 'clock-events'"
                    x-transition:enter="transition-opacity duration-150"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <!-- Filter bar -->
                    <div class="filter-bar mb-3">
                        <div class="row g-3 align-items-center">

                            <!-- Search -->
                            <div class="col-12 col-md-5">
                                <div class="search-wrapper">
                                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="11" cy="11" r="8"/>
                                        <path d="m21 21-4.35-4.35"/>
                                    </svg>
                                    <input
                                        type="text"
                                        class="search-input"
                                        placeholder="Search name or location"
                                        x-model.debounce.200ms="search"
                                        aria-label="Search staff name or location"
                                    >
                                </div>
                            </div>

                            <!-- All staff dropdown -->
                            <div class="col-12 col-md-4">
                                <div class="custom-dropdown" @click.away="staffDropdownOpen = false">
                                    <button
                                        class="custom-dropdown-btn"
                                        @click="staffDropdownOpen = !staffDropdownOpen"
                                        :aria-expanded="staffDropdownOpen"
                                        aria-haspopup="listbox"
                                    >
                                        <span x-text="staffFilter || 'All staff'"></span>
                                        <svg class="chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <polyline points="6 9 12 15 18 9"/>
                                        </svg>
                                    </button>

                                    <div
                                        class="custom-dropdown-menu"
                                        x-show="staffDropdownOpen"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 -translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 -translate-y-1"
                                        role="listbox"
                                    >
                                        <div
                                            class="custom-dropdown-item"
                                            :class="{ selected: staffFilter === '' }"
                                            @click="staffFilter = ''; staffDropdownOpen = false"
                                            role="option"
                                            :aria-selected="staffFilter === ''"
                                        >
                                            <svg class="item-check" :class="{ hidden: staffFilter !== '' }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            All staff
                                        </div>

                                        <?php foreach ($staffList as $staff): ?>
                                        <div
                                            class="custom-dropdown-item"
                                            :class="{ selected: staffFilter === <?= json_encode($staff) ?> }"
                                            @click="staffFilter = '<?= htmlspecialchars($staff, ENT_QUOTES) ?>'; staffDropdownOpen = false"
                                            role="option"
                                            :aria-selected="staffFilter === '<?= htmlspecialchars($staff, ENT_QUOTES) ?>'
                                        >
                                            <svg class="item-check" :class="{ hidden: staffFilter !== <?= json_encode($staff) ?> }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            <?= htmlspecialchars($staff) ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- All statuses dropdown -->
                            <div class="col-12 col-md-3">
                                <div class="custom-dropdown" @click.away="statusDropdownOpen = false">
                                    <button
                                        class="custom-dropdown-btn"
                                        @click="statusDropdownOpen = !statusDropdownOpen"
                                        :aria-expanded="statusDropdownOpen"
                                        aria-haspopup="listbox"
                                    >
                                        <span x-text="statusFilter || 'All statuses'"></span>
                                        <svg class="chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <polyline points="6 9 12 15 18 9"/>
                                        </svg>
                                    </button>

                                    <div
                                        class="custom-dropdown-menu"
                                        x-show="statusDropdownOpen"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 -translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 -translate-y-1"
                                        role="listbox"
                                    >
                                        <div
                                            class="custom-dropdown-item"
                                            :class="{ selected: statusFilter === '' }"
                                            @click="statusFilter = ''; statusDropdownOpen = false"
                                            role="option"
                                            :aria-selected="statusFilter === ''"
                                        >
                                            <svg class="item-check" :class="{ hidden: statusFilter !== '' }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            All statuses
                                        </div>

                                        <?php foreach ($typeList as $type): ?>
                                        <div
                                            class="custom-dropdown-item"
                                            :class="{ selected: statusFilter === <?= json_encode($type) ?> }"
                                            @click="statusFilter = '<?= htmlspecialchars($type, ENT_QUOTES) ?>'; statusDropdownOpen = false"
                                            role="option"
                                            :aria-selected="statusFilter === <?= json_encode($type) ?>"
                                        >
                                            <svg class="item-check" :class="{ hidden: statusFilter !== <?= json_encode($type) ?> }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            <?= htmlspecialchars($type) ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Clock events table -->
                    <div class="data-card">
                        <table class="data-table" aria-label="Clock events">
                            <thead>
                                <tr>
                                    <th scope="col">Staff</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Timestamp</th>
                                    <th scope="col">Device</th>
                                    <th scope="col">Location</th>
                                    <th scope="col">Sync</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="filteredClockEvents.length === 0">
                                    <tr>
                                        <td colspan="7" class="no-records">No records.</td>
                                    </tr>
                                </template>

                                <template x-for="event in filteredClockEvents" :key="event.id">
                                    <tr>
                                        <td x-text="event.staff"></td>
                                        <td x-text="event.type"></td>
                                        <td x-text="event.timestamp"></td>
                                        <td x-text="event.device"></td>
                                        <td x-text="event.location"></td>
                                        <td>
                                            <span
                                                class="sync-badge"
                                                :class="{
                                                    synced:  event.sync === 'Synced',
                                                    pending: event.sync === 'Pending',
                                                    failed:  event.sync === 'Failed'
                                                }"
                                                x-text="event.sync"
                                            ></span>
                                        </td>
                                        <td>
                                            <button class="action-btn" :aria-label="'Edit ' + event.staff + ' record'">Edit</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div><!-- /clock-events tab -->


                <!-- ═══════════════════════════════════════════
                     AUDIT TRAIL TAB
                     ═══════════════════════════════════════════ -->
                <div
                    x-show="activeTab === 'audit-trail'"
                    x-transition:enter="transition-opacity duration-150"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <!-- Export audit button -->
                    <div class="d-flex justify-content-end mb-2">
                        <button class="export-btn" @click="exportAuditCSV()" aria-label="Export audit trail as CSV">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            Export audit
                        </button>
                    </div>

                    <!-- Audit trail table -->
                    <div class="data-card">
                        <table class="data-table" aria-label="Audit trail">
                            <thead>
                                <tr>
                                    <th scope="col">
                                        <button
                                            class="sort-btn"
                                            @click="toggleAuditSort()"
                                            :aria-label="'Sort by timestamp ' + (auditSortDir === 'desc' ? 'ascending' : 'descending')"
                                        >
                                            Timestamp
                                            <span class="sort-icon" aria-hidden="true">
                                                <template x-if="auditSortDir === 'desc'">
                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <line x1="12" y1="5" x2="12" y2="19"/>
                                                        <polyline points="19 12 12 19 5 12"/>
                                                    </svg>
                                                </template>
                                                <template x-if="auditSortDir === 'asc'">
                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <line x1="12" y1="19" x2="12" y2="5"/>
                                                        <polyline points="5 12 12 5 19 12"/>
                                                    </svg>
                                                </template>
                                            </span>
                                        </button>
                                    </th>
                                    <th scope="col">Actor</th>
                                    <th scope="col">Action</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="sortedAuditTrail.length === 0">
                                    <tr>
                                        <td colspan="4" class="no-records">No records.</td>
                                    </tr>
                                </template>

                                <template x-for="entry in sortedAuditTrail" :key="entry.id">
                                    <tr>
                                        <td x-text="entry.timestamp"></td>
                                        <td x-text="entry.actor"></td>
                                        <td>
                                            <span
                                                class="audit-action"
                                                :class="{
                                                    'edit':     entry.action === 'EDIT',
                                                    'override': entry.action === 'OVERRIDE',
                                                    'delete':   entry.action === 'DELETE',
                                                    'create':   entry.action === 'CREATE'
                                                }"
                                                x-text="entry.action"
                                            ></span>
                                        </td>
                                        <td x-text="entry.details"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div><!-- /audit-trail tab -->


                <!-- ═══════════════════════════════════════════
                     LEAVE / SICK REQUEST MODAL  (Ticket 1)
                     Bootstrap modal classes, Alpine.js state control.
                     Click backdrop or ✕ to close. No Bootstrap JS needed.
                     ═══════════════════════════════════════════ -->
                <div
                    class="modal-backdrop-overlay"
                    x-show="showModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click.self="closeModal()"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="modalTitle"
                >
                    <div
                        class="modal-box"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        @keydown.escape.window="closeModal()"
                    >
                        <!-- Modal header -->
                        <div class="modal-header-custom">
                            <h5 class="modal-title-custom" id="modalTitle">Request Leave / Sick</h5>
                            <button class="modal-close-btn" @click="closeModal()" aria-label="Close modal">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Modal body -->
                        <div class="modal-body-custom">

                            <!-- Success alert -->
                            <div
                                x-show="formSuccess"
                                class="alert alert-success d-flex align-items-center gap-2 mb-0"
                                role="status"
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Request submitted successfully!
                            </div>

                            <!-- Form — hidden after successful submit -->
                            <div x-show="!formSuccess">

                                <div class="mb-3">
                                    <label for="leaveType" class="form-label fw-semibold" style="font-size:0.875rem;">Type</label>
                                    <select
                                        id="leaveType"
                                        class="form-select"
                                        :class="{ 'is-invalid': formErrors.type }"
                                        x-model="leaveForm.type"
                                    >
                                        <option value="Leave">Leave</option>
                                        <option value="Sick">Sick</option>
                                    </select>
                                    <div class="invalid-feedback" x-text="formErrors.type"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="startDate" class="form-label fw-semibold" style="font-size:0.875rem;">Start Date</label>
                                    <input
                                        type="date"
                                        id="startDate"
                                        class="form-control"
                                        :class="{ 'is-invalid': formErrors.startDate }"
                                        x-model="leaveForm.startDate"
                                    >
                                    <div class="invalid-feedback" x-text="formErrors.startDate"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="endDate" class="form-label fw-semibold" style="font-size:0.875rem;">End Date</label>
                                    <input
                                        type="date"
                                        id="endDate"
                                        class="form-control"
                                        :class="{ 'is-invalid': formErrors.endDate }"
                                        x-model="leaveForm.endDate"
                                        :min="leaveForm.startDate"
                                    >
                                    <div class="invalid-feedback" x-text="formErrors.endDate"></div>
                                </div>

                                <div class="mb-0">
                                    <label for="reason" class="form-label fw-semibold" style="font-size:0.875rem;">Reason</label>
                                    <textarea
                                        id="reason"
                                        class="form-control"
                                        :class="{ 'is-invalid': formErrors.reason }"
                                        rows="3"
                                        x-model="leaveForm.reason"
                                        placeholder="Briefly describe your reason…"
                                    ></textarea>
                                    <div class="invalid-feedback" x-text="formErrors.reason"></div>
                                </div>

                            </div>
                        </div><!-- /modal-body -->

                        <!-- Modal footer -->
                        <div class="modal-footer-custom" x-show="!formSuccess">
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                @click="closeModal()"
                            >Cancel</button>
                            <button
                                type="button"
                                class="btn btn-primary"
                                @click="submitLeaveRequest()"
                            >Submit Request</button>
                        </div>

                    </div><!-- /modal-box -->
                </div><!-- /modal backdrop -->

            </div><!-- /x-data root -->

            <!-- ═══════════════════════════════════════════════
                 Alpine.js component — defined in attendance_log_page.js.
                 ═══════════════════════════════════════════════ -->
            <script src="/assets/js/export_csv_button.js"></script>
            <script src="/assets/js/attendance_log_page.js"></script>

        </main>
    </div>
</div><!-- /app-shell -->

<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>
