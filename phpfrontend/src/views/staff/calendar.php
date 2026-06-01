<?php
/**
 * Calendar Page
 * Monthly attendance calendar view
 */
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . route_url('/login.php'));
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>

    <style>
        /* Design System Token Mapping */
        :root {
            --deep-navy: #093C5D;
            --mid-blue: #3B7597;
            --olive-green: #9CB07A;
            --light-gray: #F5F5F5;
            
            /* Extended Soft Semantics for Status Grid */
            --soft-absent: #E26E6E;
            --soft-leave: #E9B867;
        }

        body {
            background-color: var(--light-gray);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--deep-navy);
        }

        /* Workspace Content Offsets for the Fixed Sidebar */
        .main-workspace {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-left: 280px; /* Aligns exactly with sidebar component layout */
            min-height: 100vh;
        }

        /* Elegant Modern Card Restyling */
        .custom-card {
            background-color: #FFFFFF;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(9, 60, 93, 0.04);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .custom-card-header {
            background-color: #FFFFFF;
            border-bottom: 1px solid rgba(9, 60, 93, 0.06);
            padding: 1.25rem 1.5rem;
        }

        .custom-card-body {
            padding: 1.5rem;
        }

        /* Nav Directional Arrows */
        .btn-nav {
            background-color: var(--light-gray);
            border: none;
            color: var(--deep-navy);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .btn-nav:hover {
            background-color: var(--mid-blue);
            color: #FFFFFF;
        }

        /* Custom Action Controls */
        .btn-brand-primary {
            background-color: var(--mid-blue);
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background-color 0.2s ease;
        }

        .btn-brand-primary:hover {
            background-color: var(--deep-navy);
            color: #FFFFFF;
        }

        /* Calendar Grid Layout Tokens */
        .weekday-header {
            text-align: center;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.75rem 0;
            color: var(--mid-blue);
            background-color: var(--light-gray);
            border-radius: 8px;
        }

        /* Interactive Dynamic Status Grid Cells */
        .calendar-cell {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s;
            position: relative;
        }

        /* Status Pipeline Color Configurations */
        .cell-present {
            background-color: var(--olive-green);
            color: #FFFFFF;
        }
        .cell-absent {
            background-color: var(--soft-absent);
            color: #FFFFFF;
        }
        .cell-leave {
            background-color: var(--soft-leave);
            color: #FFFFFF;
        }
        .cell-pending {
            background-color: #FFFFFF;
            border: 2px dashed #E0E0E0;
            color: var(--deep-navy);
        }
        .cell-pending opacity-label {
            color: rgba(9, 60, 93, 0.6);
        }

        /* Unified Custom List Layouts */
        .custom-list-item {
            padding: 1rem 1.25rem;
            background-color: #FFFFFF;
            border: none;
            border-bottom: 1px solid rgba(9, 60, 93, 0.05);
            display: flex;
            align-items: center;
        }

        .custom-list-item:last-child {
            border-bottom: none;
        }

        .legend-indicator {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            flex-shrink: 0;
        }

        [x-cloak] { display: none !important; }
    </style>
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
        const presentDays = [1, 2, 3, 6, 7, 8, 9, 10, 13, 14, 15, 16, 17, 20, 21, 22, 23, 24, 27, 28, 29];
        const absentDays = [5, 12, 19, 26];
        const leaveDays = [11, 18, 25];
        
        if (presentDays.includes(day)) return 'present';
        if (absentDays.includes(day)) return 'absent';
        if (leaveDays.includes(day)) return 'leave';
        return 'pending';
    },
    
    getDayStatusClass(status) {
        const classes = {
            present: 'cell-present',
            absent: 'cell-absent',
            leave: 'cell-leave',
            pending: 'cell-pending'
        };
        return classes[status] || 'cell-pending';
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
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-workspace">
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <main class="p-4 p-md-5">
                <div class="container-fluid p-0">
                    
                    <div class="mb-4">
                        <h2 class="fw-bold tracking-tight" style="color: var(--deep-navy);">Attendance Calendar</h2>
                        <p class="text-muted small">Track and review your monthly operational duty cycles and logs.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-xl-8">
                            <div class="custom-card">
                                <div class="custom-card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 fw-bold" style="color: var(--deep-navy);" x-text="monthName + ' ' + year"></h5>
                                        <div class="d-flex gap-2">
                                            <button class="btn-nav" @click="previousMonth()" type="button" aria-label="Previous Month">
                                                <i class="bi bi-chevron-left"></i>
                                            </button>
                                            <button class="btn-nav" @click="nextMonth()" type="button" aria-label="Next Month">
                                                <i class="bi bi-chevron-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="custom-card-body">
                                    
                                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.75rem; margin-bottom: 1rem;">
                                        <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day">
                                            <div class="weekday-header" x-text="day"></div>
                                        </template>
                                    </div>

                                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.75rem;">
                                        <template x-for="day in calendarDays" :key="day">
                                            <div>
                                                <div 
                                                    x-show="day"
                                                    :class="getDayStatusClass(getDayStatus(day)) + ' calendar-cell'"
                                                    @mouseenter="$el.style.transform = 'translateY(-3px)'"
                                                    @mouseleave="$el.style.transform = 'translateY(0px)'"
                                                >
                                                    <div x-text="day" style="font-size: 1.3rem;"></div>
                                                    <small :style="getDayStatus(day) === 'pending' ? 'color: rgba(9,60,93,0.5);' : 'opacity: 0.85;'" style="font-size: 0.65rem; font-weight: 600;" x-text="getDayStatusText(getDayStatus(day))"></small>
                                                </div>
                                                <div x-show="!day" style="aspect-ratio: 1;"></div>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="d-flex justify-content-center mt-4">
                                        <button class="btn-brand-primary d-flex align-items-center gap-2" @click="goToToday()" type="button">
                                            <i class="bi bi-calendar2-check"></i> Return to Today
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="custom-card">
                                <div class="custom-card-header">
                                    <h6 class="mb-0 fw-bold" style="color: var(--deep-navy);">Status Directory Legend</h6>
                                </div>
                                <div class="list-group list-group-flush">
                                    <div class="custom-list-item gap-3">
                                        <div class="legend-indicator" style="background-color: var(--olive-green);"></div>
                                        <div>
                                            <strong class="d-block small" style="color: var(--deep-navy);">Present</strong>
                                            <span class="text-muted d-block" style="font-size: 0.75rem;">Verified clock-in session logged</span>
                                        </div>
                                    </div>
                                    <div class="custom-list-item gap-3">
                                        <div class="legend-indicator" style="background-color: var(--soft-absent);"></div>
                                        <div>
                                            <strong class="d-block small" style="color: var(--deep-navy);">Absent</strong>
                                            <span class="text-muted d-block" style="font-size: 0.75rem;">No attendance processing detected</span>
                                        </div>
                                    </div>
                                    <div class="custom-list-item gap-3">
                                        <div class="legend-indicator" style="background-color: var(--soft-leave);"></div>
                                        <div>
                                            <strong class="d-block small" style="color: var(--deep-navy);">On Leave</strong>
                                            <span class="text-muted d-block" style="font-size: 0.75rem;">Approved structural company break</span>
                                        </div>
                                    </div>
                                    <div class="custom-list-item gap-3">
                                        <div class="legend-indicator" style="background-color: #FFFFFF; border: 2px dashed #E0E0E0;"></div>
                                        <div>
                                            <strong class="d-block small" style="color: var(--deep-navy);">Pending</strong>
                                            <span class="text-muted d-block" style="font-size: 0.75rem;">No historical database logs generated yet</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="custom-card">
                                <div class="custom-card-header">
                                    <h6 class="mb-0 fw-bold" style="color: var(--deep-navy);">Monthly Overview Summary</h6>
                                </div>
                                <div class="list-group list-group-flush">
                                    <div class="custom-list-item justify-content-between">
                                        <span class="small fw-semibold text-muted">Days Present</span>
                                        <strong style="color: var(--olive-green); font-size: 1.1rem;">20</strong>
                                    </div>
                                    <div class="custom-list-item justify-content-between">
                                        <span class="small fw-semibold text-muted">Days Absent</span>
                                        <strong style="color: var(--soft-absent); font-size: 1.1rem;">2</strong>
                                    </div>
                                    <div class="custom-list-item justify-content-between">
                                        <span class="small fw-semibold text-muted">Days on Leave</span>
                                        <strong style="color: var(--soft-leave); font-size: 1.1rem;">3</strong>
                                    </div>
                                    <div class="custom-list-item justify-content-between style-summary-footer" style="background-color: rgba(9, 60, 93, 0.02);">
                                        <span class="small fw-bold" style="color: var(--deep-navy);">Total Shift Hours Logged</span>
                                        <strong style="color: var(--deep-navy); font-size: 1.1rem;">160 hrs</strong>
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