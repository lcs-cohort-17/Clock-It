<?php
// Mock attendance data matching ticket requirements
$attendanceEvents = [
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

<style>
    .row-cols-7 > * {
        flex: 0 0 auto;
        width: 14.28571429%;
    }
    .btn-white:hover {
        background-color: #f8f9fa !important;
    }
</style>

<script>
function initCalendar(events) {
    return {
        eventsList: events,
        days: [],
        currentMonth: 4, // May (0-indexed)
        currentYear: 2026,
        monthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
        
        generateCalendar() {
            this.days = [];
            
            // Get first day of the selected month configuration
            const firstDayIndex = new Date(this.currentYear, this.currentMonth, 1).getDay();
            // Get total days in the selected month
            const totalDays = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
            // Get total days of previous month for padding
            const prevTotalDays = new Date(this.currentYear, this.currentMonth, 0).getDate();
            
            const today = new Date();
            const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

            // 1. Previous Month Padding Days
            for (let i = firstDayIndex - 1; i >= 0; i--) {
                const dayNum = prevTotalDays - i;
                const mockMonth = this.currentMonth === 0 ? 11 : this.currentMonth - 1;
                const mockYear = this.currentMonth === 0 ? this.currentYear - 1 : this.currentYear;
                const dateString = `${mockYear}-${String(mockMonth + 1).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
                
                this.days.push({
                    id: 'prev-' + dayNum,
                    dateNumber: dayNum,
                    isCurrentMonth: false,
                    isToday: dateString === todayStr,
                    hasEvent: !!this.eventsList[dateString],
                    eventInfo: this.eventsList[dateString] || ''
                });
            }

            // 2. Current Month Target Days
            for (let i = 1; i <= totalDays; i++) {
                const dateString = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                
                this.days.push({
                    id: 'curr-' + i,
                    dateNumber: i,
                    isCurrentMonth: true,
                    isToday: dateString === todayStr,
                    hasEvent: !!this.eventsList[dateString],
                    eventInfo: this.eventsList[dateString] || ''
                });
            }

            // 3. Next Month Padding Days to complete standard grid layout
            const remainingGridCells = 42 - this.days.length; // 6 rows * 7 days
            for (let i = 1; i <= remainingGridCells; i++) {
                const mockMonth = this.currentMonth === 11 ? 0 : this.currentMonth + 1;
                const mockYear = this.currentMonth === 11 ? this.currentYear + 1 : this.currentYear;
                const dateString = `${mockYear}-${String(mockMonth + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;

                this.days.push({
                    id: 'next-' + i,
                    dateNumber: i,
                    isCurrentMonth: false,
                    isToday: dateString === todayStr,
                    hasEvent: !!this.eventsList[dateString],
                    eventInfo: this.eventsList[dateString] || ''
                });
            }
        },
        
        prevMonth() {
            if (this.currentMonth === 0) {
                this.currentMonth = 11;
                this.currentYear--;
            } else {
                this.currentMonth--;
            }
            this.updateCalendarView();
        },
        
        nextMonth() {
            if (this.currentMonth === 11) {
                this.currentMonth = 0;
                this.currentYear++;
            } else {
                this.currentMonth++;
            }
            this.updateCalendarView();
        },

        goToToday() {
            const today = new Date();
            this.currentMonth = today.getMonth();
            this.currentYear = today.getFullYear();
            this.updateCalendarView();
        },

        updateCalendarView() {
            this.destroyPopovers();
            this.generateCalendar();
            // Allow Alpine to render DOM update before re-attaching triggers
            this.$nextTick(() => { this.initPopovers(); });
        },

        initPopovers() {
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            popoverTriggerList.forEach(popoverTriggerEl => {
                new bootstrap.Popover(popoverTriggerEl);
            });
        },

        destroyPopovers() {
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            popoverTriggerList.forEach(popoverTriggerEl => {
                const instance = bootstrap.Popover.getInstance(popoverTriggerEl);
                if (instance) instance.dispose();
            });
        }
    }
}
</script>