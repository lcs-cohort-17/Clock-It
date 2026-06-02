<section class="page-stack" x-data="historyView()">
    <section class="surface">
        <div class="surface-header">
            <div>
                <h2>Attendance History</h2>
                <p class="surface-subtitle">Personal list and calendar views backed by the same attendance state.</p>
            </div>
            <button class="btn btn-primary" type="button" x-on:click="exportCsv()"><?= ui_icon('download') ?> Export CSV</button>
        </div>

        <div class="row g-3">
            <div class="col-12 col-lg-4">
                <label class="form-label">Search</label>
                <input class="form-control" type="search" placeholder="Device, status, note..." x-model="search">
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label">Type</label>
                <select class="form-select" x-model="typeFilter">
                    <option value="">All types</option>
                    <option>Global Clock In</option>
                    <option>Global Clock Out</option>
                </select>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label">Date</label>
                <input class="form-control" type="date" x-model="dateFilter">
            </div>
            <div class="col-12 col-lg-2">
                <label class="form-label">View</label>
                <div class="segmented">
                    <button type="button" x-bind:class="{ active: view === 'list' }" x-on:click="view = 'list'">List</button>
                    <button type="button" x-bind:class="{ active: view === 'calendar' }" x-on:click="view = 'calendar'">Calendar</button>
                </div>
            </div>
        </div>
    </section>

    <section class="surface" x-show="view === 'list'">
        <div class="table-shell">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Device</th>
                        <th>QR Used</th>
                        <th>Status</th>
                        <th>Timestamp</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="record in filteredRecords" :key="record.id">
                        <tr>
                            <td x-text="record.type"></td>
                            <td x-text="record.device"></td>
                            <td><span class="badge-soft" x-text="record.qrUsed"></span></td>
                            <td><span :class="statusClass(record.status)" x-text="record.status"></span></td>
                            <td x-text="formatDateTime(record.timestamp)"></td>
                            <td>
                                <button class="btn btn-sm btn-outline-light" type="button" x-on:click="openDetails(record)"><?= ui_icon('eye') ?></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </section>

    <section class="surface" x-show="view === 'calendar'">
        <div class="list-stack">
            <template x-for="record in filteredRecords" :key="record.id">
                <button class="mini-row text-start" type="button" x-on:click="openDetails(record)">
                    <span>
                        <strong x-text="dateKey(record.timestamp)"></strong>
                        <span x-text="record.type"></span>
                    </span>
                    <span :class="statusClass(record.status)" x-text="record.status"></span>
                </button>
            </template>
        </div>
    </section>

    <div class="modal fade" id="historyDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" x-if="selected">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="selected.type"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="metadata-grid">
                        <div class="metadata-item"><span>Timestamp</span><strong x-text="formatDateTime(selected.timestamp)"></strong></div>
                        <div class="metadata-item"><span>Device</span><strong x-text="selected.device"></strong></div>
                        <div class="metadata-item"><span>QR</span><strong x-text="selected.qrUsed"></strong></div>
                        <div class="metadata-item"><span>Status</span><strong x-text="selected.status"></strong></div>
                        <div class="metadata-item"><span>Sync</span><strong x-text="selected.syncStatus"></strong></div>
                        <div class="metadata-item"><span>Updated</span><strong x-text="formatDateTime(selected.updatedAt)"></strong></div>
                    </div>
                    <p class="surface-subtitle mt-3" x-text="selected.notes"></p>
                </div>
            </div>
        </div>
    </div>
</section>
