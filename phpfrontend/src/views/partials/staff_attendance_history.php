<section class="attendance-history" x-data="attendanceHistory()" x-init="init()" x-cloak>
    <div class="history-toolbar">
        <div>
            <h1>Attendance history</h1>
            <p>Review daily records, switch to calendar view, and export filtered history.</p>
        </div>
        <div class="history-toolbar-actions">
            <button type="button" class="export-btn" @click="exportReport()">
                <i class="bi bi-download" aria-hidden="true"></i>
                Export history
            </button>
            <div class="history-view-toggle" aria-label="History view">
                <button type="button" :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'">List view</button>
                <button type="button" :class="{ active: viewMode === 'calendar' }" @click="viewMode = 'calendar'">Calendar view</button>
            </div>
        </div>
    </div>

    <div class="history-filters">
        <label>
            <span>Search</span>
            <input type="search" x-model.debounce.200ms="searchTerm" @input="applyFilters()" placeholder="Search name, ID, or department">
        </label>
        <label>
            <span>Time period</span>
            <select x-model="timeFilter" @change="applyFilters()">
                <option value="all">All time</option>
                <option value="week">This week</option>
                <option value="month">This month</option>
            </select>
        </label>
        <label>
            <span>Status</span>
            <select x-model="statusFilter" @change="applyFilters()">
                <option value="All">All statuses</option>
                <option>Present</option>
                <option>Absent</option>
                <option>Late</option>
                <option>Half Day</option>
                <option>Holiday</option>
            </select>
        </label>
    </div>

    <div class="history-metrics">
        <template x-for="metric in metrics" :key="metric.label">
            <div>
                <span x-text="metric.label"></span>
                <strong x-text="metric.value"></strong>
            </div>
        </template>
    </div>

    <div class="history-card" x-show="viewMode === 'list'">
        <div class="history-count" x-text="`${filteredData.length} attendance record${filteredData.length === 1 ? '' : 's'}`"></div>
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
                        <tr><td colspan="7" class="history-empty">No matching attendance records.</td></tr>
                    </template>
                    <template x-for="record in pageRows" :key="record.id">
                        <tr>
                            <td><strong x-text="record.employeeName"></strong><small x-text="record.employeeId"></small></td>
                            <td x-text="record.department"></td>
                            <td x-text="formatDate(record.date)"></td>
                            <td x-text="record.checkInTime"></td>
                            <td x-text="record.checkOutTime"></td>
                            <td x-text="`${Number(record.workingHours).toFixed(1)}h`"></td>
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

    <div class="history-card history-calendar" x-show="viewMode === 'calendar'">
        <div class="history-calendar-header">
            <button type="button" @click="changeMonth(-1)" aria-label="Previous month"><i class="bi bi-chevron-left"></i></button>
            <h2 x-text="calendarTitle"></h2>
            <button type="button" @click="changeMonth(1)" aria-label="Next month"><i class="bi bi-chevron-right"></i></button>
        </div>
        <div class="history-calendar-grid history-weekdays">
            <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day"><strong x-text="day"></strong></template>
        </div>
        <div class="history-calendar-grid">
            <template x-for="(day, index) in calendarDays" :key="index">
                <div class="history-calendar-day" :class="{ blank: !day.date }">
                    <b x-text="day.number"></b>
                    <template x-for="record in day.records.slice(0, 3)" :key="record.id">
                        <small :class="statusClass(record.status)" x-text="`${record.employeeName.split(' ')[0]}: ${record.status}`"></small>
                    </template>
                    <em x-show="day.records.length > 3" x-text="`+${day.records.length - 3} more`"></em>
                </div>
            </template>
        </div>
    </div>
</section>
