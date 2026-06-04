<?php

use ClockIt\Data\AttendanceRepository;
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

// Pre-compute lists for PHP-rendered dropdown options
$repository = new AttendanceRepository();

$clockEvents = $repository->getClockEvents();
$auditTrail  = $repository->getAuditTrail();

$staffList = array_values(array_unique(array_column($clockEvents, 'staff')));
$typeList  = array_values(array_unique(array_column($clockEvents, 'type')));
sort($staffList);
sort($typeList);
?>

<?php ob_start(); ?>

<div class="app-shell">
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>
        <main class="content">

            <!-- ── PHP → JS data bridge ─────────────────────── -->
            <script>
                window.ATTENDANCE_DATA = {
                    clockEvents: <?= json_encode($clockEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                    auditTrail:  <?= json_encode($auditTrail,  JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
                };
            </script>

            <!-- ── Utility functions (pure, injectable, testable) ── -->
            <script src="<?= e(app_url('/assets/js/attendance_log_utils.js')) ?>"></script>

            <!-- ═══════════════════════════════════════════════════
                 Page root — single Alpine component
                 ═══════════════════════════════════════════════════ -->
            <div
                class="container-fluid py-4 px-4"
                x-data="attendancePage()"
                x-cloak
            >

                <!-- ── Page header ──────────────────────────── -->
                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                        <h1 class="page-title">Attendance Logs</h1>
                        <p class="page-subtitle">Full clock-event history with override and audit trail.</p>
                    </div>

                    <button class="export-btn" @click="activeTab === 'audit-trail' ? exportAuditCSV() : exportClockEventsCSV()" :aria-label="activeTab === 'audit-trail' ? 'Export audit trail as CSV' : 'Export attendance logs as CSV'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        <span x-text="activeTab === 'audit-trail' ? 'Export Audit Trail' : 'Export CSV'"></span>
                    </button>
                </div>

                <!-- ── Tab toggle ────────────────────────────── -->
                <div class="tab-group btn-group mb-3" role="tablist">
                    <button
                        class="tab-btn"
                        :class="{ active: activeTab === 'clock-events' }"
                        @click="activeTab = 'clock-events'"
                        role="tab"
                        :aria-selected="activeTab === 'clock-events'"
                    >Clock Events</button>

                    <button
                        class="tab-btn"
                        :class="{ active: activeTab === 'audit-trail' }"
                        @click="activeTab = 'audit-trail'"
                        role="tab"
                        :aria-selected="activeTab === 'audit-trail'"
                    >Audit Trail</button>
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
                <div class="attendance-loading py-4" x-show="loading" role="status">
                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <span class="ms-2">Loading attendance logs...</span>
                </div>
                    <!-- Filter bar -->
                    <div class="filter-bar mb-3">
                        <div class="row g-3 align-items-center">

                            <!-- Search -->
                            <div class="col-12 col-md-8">
                                <div class="search-wrapper">
                                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="11" cy="11" r="8"/>
                                        <path d="m21 21-4.35-4.35"/>
                                    </svg>
                                    <input
                                        type="text"
                                        class="search-input"
                                        placeholder="Search staff name"
                                        x-model.debounce.200ms="search"
                                        aria-label="Search staff name"
                                    >
                                </div>
                            </div>

                            <!-- All statuses dropdown -->
                            <div class="col-12 col-md-4">
                                <span class="visually-hidden">Status Filter</span>
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
                        <div class="table-responsive">
                        <table class="data-table" aria-label="Clock events">
                            <thead>
                                <tr>
                                    <th scope="col">Staff</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Timestamp</th>
                                    <th scope="col">Device</th>
                                    <th scope="col">Sync</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="filteredClockEvents.length === 0">
                                    <tr>
                                        <td colspan="5" class="no-records">No records.</td>
                                    </tr>
                                </template>

                                <template x-for="event in filteredClockEvents" :key="event.id">
                                    <tr>
                                        <td x-text="event.staff"></td>
                                        <td x-text="event.type"></td>
                                        <td x-text="event.timestamp"></td>
                                        <td x-text="event.device"></td>
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
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        </div>
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
                    <!-- Audit trail table -->
                    <div class="data-card">
                        <div class="table-responsive">
                        <table class="data-table table-striped" aria-label="Audit trail">
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
                                    <th scope="col">Admin Name</th>
                                    <th scope="col">Action</th>
                                    <th scope="col">Details</th>
                                    <th scope="col">Old Value</th>
                                    <th scope="col">New Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="sortedAuditTrail.length === 0">
                                    <tr>
                                        <td colspan="6" class="no-records">No records.</td>
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
                                        <td x-text="entry.oldValue"></td>
                                        <td x-text="entry.newValue"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        </div>
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
            <script src="<?= e(app_url('/assets/js/attendance_log_page.js')) ?>"></script>

        </main>
    </div>
</div><!-- /app-shell -->

<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>
