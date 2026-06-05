<main class="attendance-history" x-data="attendanceHistory()" x-init="init()" x-cloak>
    <section class="attendance-hero">
        <div class="attendance-container">
            <p>Staff Portal</p>
            <h1>My Attendance</h1>
            <span>View your personal attendance history and insights</span>
        </div>
    </section>

    <section class="attendance-container attendance-content">
        <div class="attendance-profile-card">
            <div class="attendance-avatar" x-text="employeeInitials"></div>
            <div>
                <h2 x-text="employeeName"></h2>
                <p>
                    <span x-text="currentEmployeeId || 'Employee'"></span>
                    <span aria-hidden="true">-</span>
                    <span x-text="employeeDepartment"></span>
                </p>
            </div>
        </div>

        <div class="history-metrics">
            <template x-for="metric in metrics" :key="metric.label">
                <div class="history-metric-card">
                    <span x-text="metric.label"></span>
                    <strong :class="metric.className" x-text="metric.value"></strong>
                    <small x-text="metric.detail"></small>
                </div>
            </template>
        </div>

        <div class="history-filters">
            <label class="history-search-field">
                <span>Search</span>
                <input type="search" x-model.debounce.200ms="searchTerm" @input="applyFilters()" placeholder="Search by date or status...">
            </label>
            <label>
                <span>Time Period</span>
                <select x-model="timeFilter" @change="applyFilters()">
                    <option value="all">All Time</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </label>
            <label>
                <span>Status</span>
                <select x-model="statusFilter" @change="applyFilters()">
                    <option value="All">All Statuses</option>
                    <option>Present</option>
                    <option>Absent</option>
                    <option>Late</option>
                    <option>Half Day</option>
                    <option>Holiday</option>
                </select>
            </label>
        </div>

        <div class="history-card">
            <div class="history-count" x-text="`Showing ${pageRows.length} of ${filteredData.length} records`"></div>
            <div class="table-responsive">
                <table class="history-table">
                    <thead>
                        <tr>
                            <template x-for="column in columns" :key="column.field">
                                <th>
                                    <button type="button" @click="sortBy(column.field)">
                                        <span x-text="column.label"></span>
                                        <span x-text="sortIcon(column.field)"></span>
                                    </button>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="pageRows.length === 0">
                            <tr><td colspan="5" class="history-empty">No matching attendance records.</td></tr>
                        </template>
                        <template x-for="record in pageRows" :key="record.id">
                            <tr>
                                <td x-text="formatDate(record.date)"></td>
                                <td x-text="record.checkInTime"></td>
                                <td x-text="record.checkOutTime"></td>
                                <td x-text="formatHours(record.workingHours)"></td>
                                <td><span class="history-status" :class="statusClass(record.status)" x-text="record.status"></span></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="history-pagination" x-show="totalPages > 1">
                <button type="button" @click="setPage(currentPage - 1)" :disabled="currentPage === 1">Previous</button>
                <span x-text="`Page ${currentPage} of ${totalPages}`"></span>
                <button type="button" @click="setPage(currentPage + 1)" :disabled="currentPage === totalPages">Next</button>
            </div>
        </div>
    </section>
</main>
