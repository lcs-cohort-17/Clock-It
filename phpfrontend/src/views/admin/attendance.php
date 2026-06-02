<section class="page-stack" x-data="attendanceManager()">
    <section class="surface">
        <div class="surface-header">
            <div>
                <h2>Attendance Records</h2>
                <p class="surface-subtitle">Search, filter, export, and review audit-ready events.</p>
            </div>
            <button class="btn btn-primary" type="button" x-on:click="exportCsv()">
                <?= ui_icon('download') ?>
                Export CSV
            </button>
        </div>

        <div class="row g-3">
            <div class="col-12 col-xl-4">
                <label class="form-label" for="attendance-search">Search</label>
                <input id="attendance-search" class="form-control" type="search" placeholder="Employee, department, note..." x-model="search">
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="employee-filter">Employee</label>
                <select id="employee-filter" class="form-select" x-model="employeeFilter">
                    <option value="">All employees</option>
                    <template x-for="person in staffOptions" :key="person.employeeId">
                        <option :value="person.employeeId" x-text="person.name"></option>
                    </template>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="status-filter">Status</label>
                <select id="status-filter" class="form-select" x-model="statusFilter">
                    <option value="">All statuses</option>
                    <option value="Verified">Verified</option>
                    <option value="Pending Review">Pending Review</option>
                    <option value="Clock Out Pending">Clock Out Pending</option>
                    <option value="Pending">Pending sync</option>
                    <option value="Flagged">Flagged</option>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="device-filter">Device</label>
                <select id="device-filter" class="form-select" x-model="deviceFilter">
                    <option value="">All devices</option>
                    <option>Desktop</option>
                    <option>Mobile</option>
                    <option>Tablet</option>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="date-filter">Date</label>
                <input id="date-filter" class="form-control" type="date" x-model="dateFilter">
            </div>
        </div>
    </section>

    <section class="surface">
        <div class="surface-header">
            <h2>Filtered Log</h2>
            <span class="badge-soft violet" x-text="`${filteredRecords.length} records`"></span>
        </div>

        <div class="table-shell">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Event</th>
                        <th>Device</th>
                        <th>QR Used</th>
                        <th>Status</th>
                        <th>Sync</th>
                        <th>Timestamp</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="record in filteredRecords" :key="record.id">
                        <tr>
                            <td>
                                <strong x-text="record.employeeName"></strong>
                                <div class="table-muted" x-text="`${record.employeeId} / ${record.department}`"></div>
                            </td>
                            <td x-text="record.type"></td>
                            <td x-text="record.device"></td>
                            <td><span class="badge-soft" x-text="record.qrUsed"></span></td>
                            <td><span :class="statusClass(record.status)" x-text="record.status"></span></td>
                            <td><span :class="statusClass(record.syncStatus)" x-text="record.syncStatus"></span></td>
                            <td x-text="formatDateTime(record.timestamp)"></td>
                            <td>
                                <button class="btn btn-sm btn-outline-light" type="button" x-on:click="openReview(record)">
                                    <?= ui_icon('eye') ?>
                                    Review
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </section>

    <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" x-if="selected">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" x-text="selected.employeeName"></h5>
                        <div class="table-muted" x-text="selected.id"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="metadata-grid mb-3">
                        <div class="metadata-item">
                            <span>Type</span>
                            <strong x-text="selected.type"></strong>
                        </div>
                        <div class="metadata-item">
                            <span>Timestamp</span>
                            <strong x-text="formatDateTime(selected.timestamp)"></strong>
                        </div>
                        <div class="metadata-item">
                            <span>Device</span>
                            <strong x-text="selected.device"></strong>
                        </div>
                        <div class="metadata-item">
                            <span>Status</span>
                            <strong x-text="selected.status"></strong>
                        </div>
                    </div>

                    <label class="form-label" for="review-note">Review note</label>
                    <textarea id="review-note" class="form-control" rows="3" x-model="reviewNote" placeholder="Add review context"></textarea>

                    <h6 class="section-title mt-4">Audit Trail</h6>
                    <div class="list-stack mt-2">
                        <template x-for="entry in selected.auditTrail || []" :key="entry.at + entry.event">
                            <div class="mini-row">
                                <div>
                                    <strong x-text="entry.actor"></strong>
                                    <span x-text="entry.event"></span>
                                </div>
                                <small x-text="formatDateTime(entry.at)"></small>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-light" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" type="button" x-on:click="saveReview('Flagged')">
                        <?= ui_icon('x') ?>
                        Flag
                    </button>
                    <button class="btn btn-primary" type="button" x-on:click="saveReview('Verified')">
                        <?= ui_icon('check') ?>
                        Verify
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
