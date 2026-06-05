<?php
$title = 'Leave Requests | Clock-It Admin';
$isAdminDashboard = true;

ob_start();
?>

<div class="app-shell" x-data="adminLeaveApp()">
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content container-fluid p-4 p-lg-5">

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="display-6 fw-bold mb-1">Leave Requests</h1>
                    <p class="text-muted mb-0">Review, approve, or reject company-wide staff leave requests.</p>
                </div>
                <div class="leave-stat-pills d-flex gap-2 flex-wrap">
                    <span class="leave-stat-pill">
                        <span class="leave-stat-dot leave-stat-dot--pending"></span>
                        <span class="fw-semibold" x-text="counts.pending"></span> Pending
                    </span>
                    <span class="leave-stat-pill">
                        <span class="leave-stat-dot leave-stat-dot--approved"></span>
                        <span class="fw-semibold" x-text="counts.approved"></span> Approved
                    </span>
                    <span class="leave-stat-pill">
                        <span class="leave-stat-dot leave-stat-dot--rejected"></span>
                        <span class="fw-semibold" x-text="counts.rejected"></span> Rejected
                    </span>
                </div>
            </div>

            <!-- Filters -->
            <div class="card leave-card border-0 shadow-sm mb-4 p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-5">
                        <div class="leave-search-wrap">
                            <i class="bi bi-search leave-search-icon"></i>
                            <input type="text"
                                   class="leave-search-input form-control"
                                   placeholder="Search by employee name or leave type…"
                                   x-model="filters.search"
                                   id="leaveSearchInput">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select leave-filter-select" x-model="filters.status" id="leaveStatusFilter">
                            <option value="All">All Requests</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button class="btn btn-outline-secondary" @click="resetFilters()" type="button" id="leaveClearFilters">
                            <i class="bi bi-x-circle me-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card leave-card border-0 shadow-sm overflow-hidden">

                <!-- Loading State -->
                <div x-show="isLoading" class="leave-loading-state">
                    <div class="leave-spinner"></div>
                    <p class="text-muted small mt-3 mb-0">Fetching leave roster…</p>
                </div>

                <!-- Table -->
                <div x-show="!isLoading" x-cloak class="table-responsive">
                    <table class="table table-hover align-middle mb-0 leave-table">
                        <thead class="leave-table-head">
                            <tr>
                                <th class="ps-4">Employee</th>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Duration</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in filteredRequests" :key="item.id">
                                <tr class="leave-table-row">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="leave-avatar" x-text="initials(item.employee_name)"></div>
                                            <span class="fw-semibold" x-text="item.employee_name"></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="leave-type-badge" :class="getLeaveTypeClass(item.type)" x-text="item.type"></span>
                                    </td>
                                    <td class="text-muted small" x-text="formatDate(item.start_date)"></td>
                                    <td class="text-muted small" x-text="formatDate(item.end_date)"></td>
                                    <td>
                                        <span class="leave-duration-badge" x-text="calcDays(item.start_date, item.end_date) + ' day(s)'"></span>
                                    </td>
                                    <td>
                                        <span class="leave-reason-text" :title="item.reason" x-text="item.reason"></span>
                                    </td>
                                    <td>
                                        <span class="leave-status-badge rounded-pill" :class="getStatusClass(item.status)" x-text="item.status"></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <button class="btn btn-sm leave-btn-approve"
                                                    x-show="item.status === 'Pending' || item.status === 'Rejected'"
                                                    @click="confirmAction(item, 'approved')"
                                                    type="button"
                                                    :id="'approve-' + item.id">
                                                <i class="bi bi-check-lg me-1"></i>Approve
                                            </button>
                                            <button class="btn btn-sm leave-btn-reject"
                                                    x-show="item.status === 'Pending' || item.status === 'Approved'"
                                                    @click="confirmAction(item, 'rejected')"
                                                    type="button"
                                                    :id="'reject-' + item.id">
                                                <i class="bi bi-x-lg me-1"></i>Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div x-show="filteredRequests.length === 0" class="leave-empty-state">
                        <i class="bi bi-folder-x leave-empty-icon"></i>
                        <h5 class="fw-bold mt-3 mb-1">No leave requests found</h5>
                        <p class="text-muted small mb-0">Try clearing filters or searching a different term.</p>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- Confirmation Modal — outside app-shell to avoid overflow:hidden clipping -->
<div class="leave-confirm-overlay" x-data x-show="$store.leaveConfirm.show" x-cloak
     role="dialog" aria-modal="true" aria-labelledby="leaveConfirmTitle"
     @keydown.escape.window="$store.leaveConfirm.cancel()">
    <div class="leave-confirm-card">
        <div class="leave-confirm-icon" :class="$store.leaveConfirm.action === 'approved' ? 'leave-confirm-icon--approve' : 'leave-confirm-icon--reject'">
            <i :class="$store.leaveConfirm.action === 'approved' ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-circle-fill'"></i>
        </div>
        <h5 class="fw-bold mb-1" id="leaveConfirmTitle"
            x-text="$store.leaveConfirm.action === 'approved' ? 'Confirm Approval' : 'Confirm Rejection'"></h5>
        <p class="text-muted small mb-4">
            Are you sure you want to
            <strong x-text="$store.leaveConfirm.action === 'approved' ? 'approve' : 'reject'"></strong>
            the leave request from
            <strong x-text="$store.leaveConfirm.employeeName"></strong>?
        </p>
        <div class="d-flex gap-2 justify-content-center">
            <button type="button" class="btn btn-light px-4" @click="$store.leaveConfirm.cancel()" id="leaveCancelBtn">
                Cancel
            </button>
            <button type="button"
                    class="btn px-4 text-white"
                    :class="$store.leaveConfirm.action === 'approved' ? 'btn-success' : 'btn-danger'"
                    @click="$store.leaveConfirm.confirm()"
                    id="leaveConfirmBtn">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {

        /* ── Shared store for the confirm dialog ─────────────── */
        Alpine.store('leaveConfirm', {
            show: false,
            action: '',
            employeeName: '',
            _callback: null,

            open(action, employeeName, callback) {
                this.action = action;
                this.employeeName = employeeName;
                this._callback = callback;
                this.show = true;
            },
            cancel() {
                this.show = false;
                this._callback = null;
            },
            confirm() {
                const cb = this._callback;
                this.show = false;
                this._callback = null;
                if (cb) cb();
            }
        });

        /* ── Main component ──────────────────────────────────── */
        Alpine.data('adminLeaveApp', () => ({
            requests: [],
            isLoading: true,
            filters: { search: '', status: 'All' },

            init() {
                this.fetchLeaveRequests();
            },

            async fetchLeaveRequests() {
                this.isLoading = true;
                await new Promise(resolve => setTimeout(resolve, 300));
                
                let localRequests = [];
                try {
                    localRequests = JSON.parse(window.localStorage.getItem('leaveRequests') || '[]');
                } catch (_) {}
                
                if (localRequests.length === 0) {
                    const defaultMock = [
                        { id: 101, employee_name: 'Sarah Jenkins',   type: 'Annual Leave',          start_date: '2026-06-10', end_date: '2026-06-15', reason: 'Family vacation break',                     status: 'Pending'  },
                        { id: 102, employee_name: 'John Doe',         type: 'Sick Leave',             start_date: '2026-06-05', end_date: '2026-06-06', reason: 'Dental surgery procedure',                 status: 'Pending'  },
                        { id: 103, employee_name: 'Alex Smith',       type: 'Study Leave',            start_date: '2026-06-20', end_date: '2026-06-23', reason: 'Frontend final examination preparation',    status: 'Pending'  },
                        { id: 104, employee_name: 'Michael Brown',    type: 'Family Responsibility',  start_date: '2026-05-12', end_date: '2026-05-15', reason: 'Attending relative funeral services',       status: 'Pending'  },
                        { id: 105, employee_name: 'Lerato Dlamini',   type: 'Annual Leave',           start_date: '2026-07-01', end_date: '2026-07-05', reason: 'Annual holiday break',                     status: 'Approved' },
                        { id: 106, employee_name: 'Thandi Mokoena',   type: 'Sick Leave',             start_date: '2026-06-01', end_date: '2026-06-02', reason: 'Flu and fever recovery',                   status: 'Rejected' },
                    ];
                    const toSave = defaultMock.map(r => ({
                        id: r.id,
                        employee_name: r.employee_name,
                        type: r.type,
                        startDate: r.start_date,
                        endDate: r.end_date,
                        reason: r.reason,
                        status: r.status
                    }));
                    window.localStorage.setItem('leaveRequests', JSON.stringify(toSave));
                    localRequests = toSave;
                }
                
                this.requests = localRequests.map(r => ({
                    id: r.id,
                    employee_name: r.employee_name || 'Staff Member',
                    type: r.type,
                    start_date: r.startDate || r.start_date,
                    end_date: r.endDate || r.end_date,
                    reason: r.reason,
                    status: r.status
                }));
                this.isLoading = false;
            },

            get filteredRequests() {
                const term = this.filters.search.toLowerCase().trim();
                return this.requests.filter(item => {
                    const matchSearch = !term ||
                        item.employee_name.toLowerCase().includes(term) ||
                        item.type.toLowerCase().includes(term);
                    const matchStatus = this.filters.status === 'All' || item.status === this.filters.status;
                    return matchSearch && matchStatus;
                });
            },

            get counts() {
                return {
                    pending:  this.requests.filter(r => r.status === 'Pending').length,
                    approved: this.requests.filter(r => r.status === 'Approved').length,
                    rejected: this.requests.filter(r => r.status === 'Rejected').length,
                };
            },

            resetFilters() {
                this.filters.search = '';
                this.filters.status = 'All';
            },

            confirmAction(item, action) {
                Alpine.store('leaveConfirm').open(action, item.employee_name, () => {
                    this.executeStatusUpdate(item, action);
                });
            },

            async executeStatusUpdate(item, action) {
                this.isLoading = true;
                await new Promise(resolve => setTimeout(resolve, 400));
                const target = this.requests.find(r => r.id === item.id);
                if (target) {
                    target.status = action.charAt(0).toUpperCase() + action.slice(1);
                    
                    let localRequests = [];
                    try {
                        localRequests = JSON.parse(window.localStorage.getItem('leaveRequests') || '[]');
                    } catch (_) {}
                    
                    const localTarget = localRequests.find(r => r.id === item.id);
                    if (localTarget) {
                        localTarget.status = target.status;
                        window.localStorage.setItem('leaveRequests', JSON.stringify(localRequests));
                    }
                }
                this.isLoading = false;
            },

            initials(name) {
                return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
            },

            formatDate(dateStr) {
                const d = new Date(dateStr);
                return d.toLocaleDateString('en-ZA', { day: '2-digit', month: 'short', year: 'numeric' });
            },

            calcDays(start, end) {
                const ms = new Date(end) - new Date(start);
                return Math.max(1, Math.round(ms / 86400000) + 1);
            },

            getLeaveTypeClass(type) {
                const map = {
                    'Annual Leave':         'leave-type--annual',
                    'Sick Leave':           'leave-type--sick',
                    'Study Leave':          'leave-type--study',
                    'Family Responsibility':'leave-type--family',
                    'Funeral Leave':        'leave-type--family',
                };
                return map[type] ?? 'leave-type--default';
            },

            getStatusClass(status) {
                const map = {
                    'Pending':  'leave-status--pending',
                    'Approved': 'leave-status--approved',
                    'Rejected': 'leave-status--rejected',
                };
                return map[status] ?? 'leave-status--default';
            }
        }));
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
