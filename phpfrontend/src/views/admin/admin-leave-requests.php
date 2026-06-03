<?php
// src/views/admin/admin-leave-requests.php
// include __DIR__ . '/../partials/admin-header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave Requests | Clock-It Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="/assets/css/admin-leave-requests.css">
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="py-4" x-data="adminLeaveApp()">

<div class="container-fluid px-4" style="max-width: 1400px;">
    
    <div class="card admin-card shadow-none mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="text-navy fs-2 heading-mid-blue-icon"><i class="bi bi-person-check-fill"></i></div>
                <div>
                    <h4 class="mb-0 text-navy fw-bold heading-mid-blue">Employee Leave Management</h4>
                    <p class="text-muted small mb-0">Review, approve, or reject company-wide staff leave requests.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card admin-card shadow-none mb-4 p-3 bg-white">
        <div class="row g-3">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control bg-light border-start-0" 
                           placeholder="Search by employee name..." x-model="filters.search">
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select bg-light" x-model="filters.status">
                    <option value="All">All Requests</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button class="btn btn-outline-secondary" @click="resetFilters()">Clear Filters</button>
            </div>
        </div>
    </div>

    <div class="card admin-card shadow-none overflow-hidden bg-white">
        
        <div x-show="isLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading records...</span>
            </div>
            <p class="text-muted small mt-2 mb-0">Fetching updated leave rosters...</p>
        </div>

        <div x-show="!isLoading" class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-bottom">
                    <tr>
                        <th class="ps-4">Employee Name</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in filteredRequests" :key="item.id">
                        <tr>
                            <td class="ps-4 fw-semibold text-dark" x-text="item.employee_name"></td>
                            <td>
                                <span class="badge" :class="getLeaveTypeClass(item.type)" x-text="item.type"></span>
                            </td>
                            <td x-text="item.start_date"></td>
                            <td x-text="item.end_date"></td>
                            <td>
                                <div class="truncate-text text-muted" :title="item.reason" x-text="item.reason"></div>
                            </td>
                            <td>
                                <span class="badge rounded-pill fw-semibold" :class="getStatusClass(item.status)" x-text="item.status"></span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-2 justify-content-end" x-show="item.status === 'Pending'">
                                    <button class="btn btn-sm btn-success d-flex align-items-center gap-1" 
                                            @click="confirmAction(item, 'approved')">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1" 
                                            @click="confirmAction(item, 'rejected')">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div x-show="filteredRequests.length === 0" class="text-center py-5 text-muted border-top">
                <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                <h5 class="fw-bold text-dark">No leave requests found</h5>
                <p class="small mb-0">Try widening your active filter scopes or searching another term.</p>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-body p-4 text-center">
                    <div class="fs-1 mb-2" :class="activeAction === 'approved' ? 'text-success' : 'text-danger'">
                        <i :class="activeAction === 'approved' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'"></i>
                    </div>
                    <h5 class="fw-bold mb-1 text-dark" x-text="activeAction === 'approved' ? 'Confirm Approval' : 'Confirm Rejection'"></h5>
                    <p class="text-muted small mb-4">
                        Are you sure you want to change <span class="fw-bold text-dark" x-text="selectedItem?.employee_name"></span>'s request status to <span class="fw-bold" x-text="activeAction"></span>?
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn px-4 text-white" 
                                :class="activeAction === 'approved' ? 'btn-success' : 'btn-danger'"
                                @click="executeStatusUpdate()">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function adminLeaveApp() {
        return {
            requests: [],
            isLoading: true,
            filters: { search: '', status: 'All' },
            selectedItem: null,
            activeAction: '',
            bsModal: null,

            init() {
                this.bsModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                this.fetchLeaveRequests();
            },

            async fetchLeaveRequests() {
                this.isLoading = true;
                try {
                    setTimeout(() => {
                        this.requests = [
                            { id: 101, employee_name: 'Sarah Jenkins', type: 'Annual Leave', start_date: '2026-06-10', end_date: '2026-06-15', reason: 'Family vacation break', status: 'Pending' },
                            { id: 102, employee_name: 'John Doe', type: 'Sick Leave', start_date: '2026-06-05', end_date: '2026-06-06', reason: 'Dental surgery tracking procedure', status: 'Pending' },
                            { id: 103, employee_name: 'Alex Smith', type: 'Study Leave', start_date: '2026-06-20', end_date: '2026-06-23', reason: 'Frontend final examination preparation', status: 'Pending' },
                            { id: 104, employee_name: 'Michael Brown', type: 'Family Responsibility', start_date: '2026-05-12', end_date: '2025-05-15', reason: 'Attending relative funeral services', status: 'Pending' }
                        ];
                        this.isLoading = false;
                    }, 800);
                    
                } catch (error) {
                    console.error("Data load failed:", error);
                    this.isLoading = false;
                }
            },

            get filteredRequests() {
                return this.requests.filter(item => {
                    const searchTerm = this.filters.search.toLowerCase().trim();
                    const matchSearch =
                        item.employee_name.toLowerCase().includes(searchTerm) ||
                        item.type.toLowerCase().includes(searchTerm);
                    const matchStatus = this.filters.status === 'All' || item.status === this.filters.status;
                    return matchSearch && matchStatus;
                });
            },

            resetFilters() {
                this.filters.search = '';
                this.filters.status = 'All';
            },

            confirmAction(item, action) {
                this.selectedItem = item;
                this.activeAction = action;
                this.bsModal.show();
            },

            async executeStatusUpdate() {
                if (!this.selectedItem) return;
                
                this.bsModal.hide();
                this.isLoading = true;

                const updatedStatusLabel = this.activeAction.charAt(0).toUpperCase() + this.activeAction.slice(1);

                try {
                    setTimeout(() => {
                        const target = this.requests.find(r => r.id === this.selectedItem.id);
                        if (target) target.status = updatedStatusLabel;
                        this.isLoading = false;
                        this.selectedItem = null;
                    }, 400);

                } catch (error) {
                    console.error("Status modify transaction failure:", error);
                    this.isLoading = false;
                }
            },

            // Fixed: Returns clean, modular class names mapped to your external stylesheet definitions
            getLeaveTypeClass(type) {
                switch(type) {
                    case 'Annual Leave': return 'badge-annual';
                    case 'Sick Leave': return 'badge-sick';
                    case 'Study Leave': return 'badge-study';
                    case 'Funeral Leave': return 'badge-funeral';
                    default: return 'bg-secondary-subtle text-secondary';
                }
            },

            getStatusClass(status) {
                switch(status) {
                    case 'Pending': return 'bg-warning text-dark';
                    case 'Approved': return 'bg-success text-white';
                    case 'Rejected': return 'bg-danger text-white';
                    default: return 'bg-secondary text-white';
                }
            }
        }
    }
</script>
</body>
</html>
