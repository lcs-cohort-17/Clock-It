<?php
/**
 * Calendar Page
 * Monthly attendance calendar view
 */
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . route_url('/login'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Calendar - Clock-It</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>
</head>
<body x-data="{ 
    currentDate: new Date(),
    selectedDate: new Date(),
    today: new Date(),
    
    get month() {
        return this.currentDate.getMonth();
    },
    
    get year() {
        return this.currentDate.getFullYear();
    },
    
    get monthName() {
        const names = ['January', 'February', 'March', 'April', 'May', 'June',
                       'July', 'August', 'September', 'October', 'November', 'December'];
        return names[this.month];
    },
    
    get firstDay() {
        return new Date(this.year, this.month, 1).getDay();
    },
    
    get daysInMonth() {
        return new Date(this.year, this.month + 1, 0).getDate();
    },
    
    get calendarDays() {
        const days = [];
        for (let i = 0; i < this.firstDay; i++) {
            days.push({ key: `${this.year}-${this.month}-blank-${i}`, day: null });
        }
        for (let i = 1; i <= this.daysInMonth; i++) {
            days.push({ key: `${this.year}-${this.month}-${i}`, day: i });
        }
        const remainingCells = (7 - (days.length % 7)) % 7;
        for (let i = 0; i < remainingCells; i++) {
            days.push({ key: `${this.year}-${this.month}-after-${i}`, day: null });
        }
        return days;
    },
    
    getDayStatus(day) {
        if (!day) return null;
        // Mock data - in real app, fetch from database
        const presentDays = [1, 2, 3, 6, 7, 8, 9, 10, 13, 14, 15, 16, 17, 20, 21, 22, 23, 24, 27, 28, 29];
        const absentDays = [5, 12, 19, 26];
        const leaveDays = [11, 18, 25];
        
        if (presentDays.includes(day)) return 'present';
        if (absentDays.includes(day)) return 'absent';
        if (leaveDays.includes(day)) return 'leave';
        return 'pending';
    },
    
    getDayStatusColor(status) {
        const colors = {
            present: 'bg-success',
            absent: 'bg-danger',
            leave: 'bg-warning',
            pending: 'bg-light'
        };
        return colors[status] || 'bg-light';
    },
    
    getDayStatusText(status) {
        const text = {
            present: 'Present',
            absent: 'Absent',
            leave: 'On Leave',
            pending: 'Pending'
        };
        return text[status] || 'Unknown';
    },

    isToday(day) {
        return day
            && day === this.today.getDate()
            && this.month === this.today.getMonth()
            && this.year === this.today.getFullYear();
    },

    isSelected(day) {
        return day
            && day === this.selectedDate.getDate()
            && this.month === this.selectedDate.getMonth()
            && this.year === this.selectedDate.getFullYear();
    },

    selectDay(day) {
        if (!day) return;
        this.selectedDate = new Date(this.year, this.month, day);
    },
    
    previousMonth() {
        const nextDate = new Date(this.year, this.month - 1, 1);
        this.currentDate = nextDate;
        this.selectedDate = nextDate;
    },
    
    nextMonth() {
        const nextDate = new Date(this.year, this.month + 1, 1);
        this.currentDate = nextDate;
        this.selectedDate = nextDate;
    },
    
    goToToday() {
        this.today = new Date();
        this.currentDate = new Date(this.today.getFullYear(), this.today.getMonth(), this.today.getDate());
        this.selectedDate = new Date(this.today.getFullYear(), this.today.getMonth(), this.today.getDate());
    }
}" @init="window.themeManager.initTheme()">
    
    <div style="display: flex; min-height: 100vh;">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div style="flex: 1; display: flex; flex-direction: column;">
            <!-- Header -->
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <!-- Page Content -->
            <main class="dashboard-section" style="padding: 2rem;">
                <div class="container-fluid">
                    <h2 class="mb-4">Attendance Calendar</h2>

                    <div class="row justify-content-center">
                        <div class="col-xl-8 col-lg-8">
                            <!-- Calendar -->
                            <div class="card border-0 shadow-sm attendance-calendar-card">
                                <div class="card-header bg-light attendance-calendar-header">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <button class="btn btn-sm btn-outline-secondary calendar-nav-btn" @click="previousMonth()" type="button">
                                            <i class="bi bi-chevron-left" aria-hidden="true"></i>
                                            <span class="visually-hidden">Previous month</span>
                                        </button>
                                        <div class="text-center">
                                            <h5 class="mb-0" x-text="monthName + ' ' + year"></h5>
                                            <small class="text-muted">Monthly attendance view</small>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary calendar-nav-btn" @click="nextMonth()" type="button">
                                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                            <span class="visually-hidden">Next month</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body calendar-card-body">
                                    <!-- Day headers -->
                                    <div class="calendar-weekdays">
                                        <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day">
                                            <div x-text="day"></div>
                                        </template>
                                    </div>

                                    <!-- Calendar days -->
                                    <div class="attendance-calendar-grid">
                                        <template x-for="item in calendarDays" :key="item.key">
                                            <button
                                                class="calendar-day"
                                                :class="[item.day ? `calendar-day-${getDayStatus(item.day)}` : 'calendar-day-placeholder', isToday(item.day) ? 'is-today' : '', isSelected(item.day) ? 'is-selected' : '']"
                                                :disabled="!item.day"
                                                type="button"
                                                @click="selectDay(item.day)"
                                                :aria-label="item.day ? `${monthName} ${item.day}, ${year}: ${getDayStatusText(getDayStatus(item.day))}` : 'Empty calendar cell'"
                                            >
                                                <span class="calendar-day-number" x-text="item.day"></span>
                                                <span class="calendar-day-status">
                                                    <span class="calendar-status-dot" aria-hidden="true"></span>
                                                    <small x-text="getDayStatusText(getDayStatus(item.day))"></small>
                                                </span>
                                            </button>
                                        </template>
                                    </div>

                                    <div class="d-flex justify-content-center gap-2 mt-4">
                                        <button class="btn btn-primary" @click="goToToday()" type="button">
                                            <i class="bi bi-calendar-event me-1" aria-hidden="true"></i>Today
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4">
                            <!-- Legend -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Legend</h6>
                                </div>
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex gap-2">
                                        <div style="width: 30px; height: 30px; background-color: #198754; border-radius: 0.25rem;"></div>
                                        <div>
                                            <strong>Present</strong>
                                            <small class="d-block text-muted">Clocked in today</small>
                                        </div>
                                    </div>
                                    <div class="list-group-item d-flex gap-2">
                                        <div style="width: 30px; height: 30px; background-color: #dc3545; border-radius: 0.25rem;"></div>
                                        <div>
                                            <strong>Absent</strong>
                                            <small class="d-block text-muted">No clock-in recorded</small>
                                        </div>
                                    </div>
                                    <div class="list-group-item d-flex gap-2">
                                        <div style="width: 30px; height: 30px; background-color: #ffc107; border-radius: 0.25rem;"></div>
                                        <div>
                                            <strong>On Leave</strong>
                                            <small class="d-block text-muted">Approved leave day</small>
                                        </div>
                                    </div>
                                    <div class="list-group-item d-flex gap-2">
                                        <div style="width: 30px; height: 30px; background-color: #f0f0f0; border: 1px solid #ccc; border-radius: 0.25rem;"></div>
                                        <div>
                                            <strong>Pending</strong>
                                            <small class="d-block text-muted">Data not recorded yet</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Month Summary -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Monthly Summary</h6>
                                </div>
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between">
                                        <span>Days Present</span>
                                        <strong class="text-success">20</strong>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between">
                                        <span>Days Absent</span>
                                        <strong class="text-danger">2</strong>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between">
                                        <span>Days on Leave</span>
                                        <strong class="text-warning">3</strong>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between">
                                        <span>Total Hours</span>
                                        <strong style="color: var(--primary-navy);">160</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
