<?php
// Mock calendar events data matching ticket requirements
$calendarEvents = [
    '2026-05-12' => 'Clocked in at 08:45 AM, out at 04:45 PM',
    '2026-05-18' => 'Clocked in at 09:00 AM, out at 05:00 PM',
    '2026-05-22' => 'Clocked in at 09:15 AM, out at 05:30 PM',
    '2026-05-25' => 'Clocked in at 09:00 AM, out at 05:00 PM',
];
?>

<div class="card rounded-2xl border-light p-4 bg-white shadow-sm" 
     x-data="initCalendar(<?= htmlspecialchars(json_encode($calendarEvents)) ?>)"
     x-init="generateCalendar(); initPopovers();">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="h5 fw-bold text-dark mb-0" x-text="monthNames[currentMonth] + ' ' + currentYear"></h3>
            <p class="text-muted small mb-0">Dates with green dots indicate recorded attendance.</p>
        </div>
        <div class="btn-group rounded-xl overflow-hidden shadow-sm border border-light-subtle">
            <button class="btn btn-white bg-white border-0 text-dark" @click="prevMonth()">&larr;</button>
            <button class="btn btn-white bg-white border-x border-light-subtle text-muted small fw-medium" @click="goToToday()">Today</button>
            <button class="btn btn-white bg-white border-0 text-dark" @click="nextMonth()">&rarr;</button>
        </div>
    </div>

    <div class="calendar-grid">
        <div class="row row-cols-7 g-1 text-center mb-2 fw-semibold text-secondary small text-uppercase" style="letter-spacing: 0.05em;">
            <div class="col">Sun</div>
            <div class="col">Mon</div>
            <div class="col">Tue</div>
            <div class="col">Wed</div>
            <div class="col">Thu</div>
            <div class="col">Fri</div>
            <div class="col">Sat</div>
        </div>

        <div class="row row-cols-7 g-2 text-center" id="calendarDays">
            <template x-for="day in days" :key="day.id">
                <div class="col">
                    <div :class="{
                            'bg-light text-muted opacity-50': !day.isCurrentMonth,
                            'bg-white text-dark border border-light-subtle': day.isCurrentMonth,
                            'border border-primary fw-bold text-primary': day.isToday
                         }"
                         class="position-relative d-flex flex-column align-items-center justify-content-center rounded-xl p-2 h-100"
                         style="min-height: 65px; cursor: day.hasEvent ? 'pointer' : 'default';"
                         :data-bs-toggle="day.hasEvent ? 'popover' : null"
                         :data-bs-content="day.eventInfo"
                         data-bs-trigger="click focus"
                         data-bs-placement="top"
                         title="Attendance Info"
                    >
                        <span class="small" x-text="day.dateNumber"></span>
                        
                        <template x-if="day.hasEvent">
                            <span class="position-absolute bottom-0 start-50 translate-middle-x mb-2 bg-success rounded-circle" 
                                  style="width: 6px; height: 6px;">
                            </span>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>


