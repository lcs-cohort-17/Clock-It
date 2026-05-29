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
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>
</head>
<body x-data="{ 
    currentDate: new Date(),
    
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
            days.push(null);
        }
        for (let i = 1; i <= this.daysInMonth; i++) {
            days.push(i);
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
    
    previousMonth() {
        this.currentDate = new Date(this.year, this.month - 1);
    },
    
    nextMonth() {
        this.currentDate = new Date(this.year, this.month + 1);
    },
    
    goToToday() {
        this.currentDate = new Date();
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

                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Calendar -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <button class="btn btn-sm btn-outline-secondary" @click="previousMonth()" type="button">
                                            ←
                                        </button>
                                        <h5 class="mb-0" x-text="monthName + ' ' + year"></h5>
                                        <button class="btn btn-sm btn-outline-secondary" @click="nextMonth()" type="button">
                                            →
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Day headers -->
                                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem; margin-bottom: 1rem;">
                                        <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day">
                                            <div style="text-align: center; font-weight: bold; padding: 0.5rem; background-color: #f0f0f0; border-radius: 0.25rem;" x-text="day"></div>
                                        </template>
                                    </div>

                                    <!-- Calendar days -->
                                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem;">
                                        <template x-for="day in calendarDays" :key="day">
                                            <div 
                                                x-show="day"
                                                :class="getDayStatusColor(getDayStatus(day))"
                                                style="aspect-ratio: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 0.5rem; cursor: pointer; color: white; font-weight: bold; transition: transform 0.2s;"
                                                @mouseenter="$el.style.transform = 'scale(1.1)'"
                                                @mouseleave="$el.style.transform = 'scale(1)'"
                                            >
                                                <div x-text="day" style="font-size: 1.25rem;"></div>
                                                <small x-text="getDayStatusText(getDayStatus(day))" style="font-size: 0.65rem; opacity: 0.9;"></small>
                                            </div>
                                            <div x-show="!day" style="aspect-ratio: 1;"></div>
                                        </template>
                                    </div>

                                    <div class="d-flex justify-content-center gap-2 mt-4">
                                        <button class="btn btn-primary" @click="goToToday()" type="button">
                                            📅 Today
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
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
