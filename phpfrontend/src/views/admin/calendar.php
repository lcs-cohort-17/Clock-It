<section class="page-stack" x-data="calendarView('admin')">
    <section class="calendar-shell surface">
        <div class="calendar-toolbar">
            <div>
                <h2 x-text="monthLabel"></h2>
                <p class="surface-subtitle">Attendance, leave, holiday, and schedule indicators.</p>
            </div>
            <div class="calendar-controls">
                <select class="form-select" x-model.number="monthIndex">
                    <template x-for="(month, index) in months" :key="month">
                        <option :value="index" x-text="month"></option>
                    </template>
                </select>
                <select class="form-select" x-model.number="year">
                    <template x-for="yearOption in years" :key="yearOption">
                        <option :value="yearOption" x-text="yearOption"></option>
                    </template>
                </select>
            </div>
        </div>

        <div class="calendar-grid mb-2">
            <template x-for="weekday in weekdays" :key="weekday">
                <div class="calendar-weekday" x-text="weekday"></div>
            </template>
        </div>
        <div class="calendar-grid">
            <template x-for="day in days" :key="day.key">
                <button class="calendar-day" type="button" :class="{ 'is-empty': day.empty, 'is-today': day.today, 'is-selected': day.selected }" x-on:click="selectDay(day)">
                    <span class="calendar-number" x-text="day.day"></span>
                    <span class="calendar-markers">
                        <template x-for="marker in day.markers" :key="marker.type + marker.label">
                            <span :class="markerClass(marker.type)" x-text="marker.label"></span>
                        </template>
                    </span>
                </button>
            </template>
        </div>
    </section>

    <div class="split-grid">
        <section class="surface">
            <div class="surface-header">
                <h2 x-text="formatDate(selectedDate)"></h2>
                <span class="badge-soft violet" x-text="`${selectedRecords.length} attendance records`"></span>
            </div>
            <div class="table-shell">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Event</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="record in selectedRecords" :key="record.id">
                            <tr>
                                <td x-text="record.employeeName"></td>
                                <td x-text="record.type"></td>
                                <td><span :class="statusClass(record.status)" x-text="record.status"></span></td>
                                <td x-text="formatTime(record.timestamp)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="surface">
            <div class="surface-header">
                <h2>Day Markers</h2>
            </div>
            <div class="list-stack">
                <template x-for="marker in selectedMarkers" :key="marker.type + marker.label">
                    <div class="mini-row">
                        <strong x-text="marker.label"></strong>
                        <span :class="markerClass(marker.type)" x-text="marker.type"></span>
                    </div>
                </template>
                <template x-if="!selectedMarkers.length">
                    <div class="feedback-panel">No markers for this date.</div>
                </template>
            </div>
        </aside>
    </div>
</section>
