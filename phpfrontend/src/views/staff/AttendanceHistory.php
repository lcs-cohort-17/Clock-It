<?php
// Staff Dashboard - Main entry point
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../../data/data.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUser = is_array($user ?? null) ? $user : current_user();

if (empty($currentUser)) {
    header('Location: ' . app_url('/login'));
    exit();
}

$loggedInEmployee = [
    'id' => $currentUser['employeeId'] ?? 'EMP001',
    'name' => $currentUser['name'] ?? 'Shaheed Karlie',
    'dept' => $currentUser['department'] ?? ($currentUser['dept'] ?? 'Engineering'),
    'avatar' => 'SK'
];

if (isset($currentUser['name'])) {
    $words = explode(' ', $currentUser['name']);
    $avatar = '';
    foreach ($words as $w) {
        $avatar .= strtoupper($w[0] ?? '');
    }
    $loggedInEmployee['avatar'] = $avatar ?: 'SK';
}

if (!function_exists('getEmployeeAttendanceData')) {
    function getEmployeeAttendanceData($employeeId, $mockAttendanceData) {
        return array_values(array_filter($mockAttendanceData, function($record) use ($employeeId) {
            return $record['employeeId'] === $employeeId;
        }));
    }
}

// Get the filtered data for the logged-in employee
$employeeAttendanceData = getEmployeeAttendanceData($loggedInEmployee['id'], $mockAttendanceData);

// Query real session records from the SQLite database
try {
    require_once dirname(__DIR__, 4) . '/phpbackend/src/config/Database.php';
    $db = \Config\Database::getInstance()->getConnection();
    
    // Find the user's user_id from their employee_id or email
    $userQuery = $db->prepare("SELECT user_id, first_name, last_name, employee_id, role FROM users WHERE employee_id = :empId OR email = :email");
    $userQuery->execute([
        ':empId' => $loggedInEmployee['id'] ?? '',
        ':email' => $currentUser['email'] ?? ''
    ]);
    $dbUser = $userQuery->fetch(\PDO::FETCH_ASSOC);
    
    if ($dbUser) {
        $profileId = $dbUser['user_id'];
        $fullName = trim(($dbUser['first_name'] ?? '') . ' ' . ($dbUser['last_name'] ?? '')) ?: $dbUser['email'];
        
        $sessionQuery = $db->prepare("
            SELECT * 
            FROM sessions 
            WHERE profile_id = :profileId 
            ORDER BY clock_in_time DESC
        ");
        $sessionQuery->execute([':profileId' => $profileId]);
        $dbSessions = $sessionQuery->fetchAll(\PDO::FETCH_ASSOC);
        
        $dbAttendance = [];
        foreach ($dbSessions as $session) {
            $clockIn = new \DateTime($session['clock_in_time']);
            $clockOut = !empty($session['clock_out_time']) ? new \DateTime($session['clock_out_time']) : null;
            
            $dateStr = $clockIn->format('Y-m-d');
            $inTime = $clockIn->format('H:i');
            $outTime = $clockOut ? $clockOut->format('H:i') : '--:--';
            
            // Calculate working hours
            $hours = 0.0;
            if ($clockOut) {
                $diff = $clockIn->diff($clockOut);
                $hours = round($diff->h + ($diff->i / 60) + ($diff->s / 3600), 1);
            }
            
            // Determine status
            $status = 'Present';
            if ((int)$clockIn->format('H') >= 9 && (int)$clockIn->format('i') > 0) {
                $status = 'Late';
            } elseif ($clockOut && $hours < 5.0) {
                $status = 'Half Day';
            }
            
            $dbAttendance[] = [
                'id' => 'DB-ATT-' . $session['id'],
                'employeeName' => $fullName,
                'employeeId' => $dbUser['employee_id'],
                'date' => $dateStr,
                'checkInTime' => $inTime,
                'checkOutTime' => $outTime,
                'status' => $status,
                'workingHours' => $hours,
                'department' => 'Staff'
            ];
        }
        
        $employeeAttendanceData = array_merge($dbAttendance, $employeeAttendanceData);
    }
} catch (\Exception $e) {
    error_log("Failed to load real sessions for history page: " . $e->getMessage());
}

$title = 'My Attendance Dashboard - ' . htmlspecialchars($loggedInEmployee['name']);
$isAdminDashboard = false;

ob_start();
?>

<!-- Load Tailwind CSS only for this page to preserve template styling -->
<script src="https://cdn.tailwindcss.com"></script>

<style>
    /* Custom styles matching your theme */
    .hero-section {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
    }
    .stat-card {
        transition: all 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .sort-header {
        cursor: pointer;
        user-select: none;
        transition: background-color 0.2s;
    }
    .sort-header:hover {
        background-color: #f1f5f9;
    }
    .status-badge {
        transition: all 0.2s ease;
    }
    .status-badge:hover {
        transform: scale(1.05);
    }
    .table-row-hover {
        transition: background-color 0.2s ease;
    }
    .table-row-hover:hover {
        background-color: #f8fafc;
    }
    /* Calendar Grid Styles */
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 10px;
    }
    .calendar-day-cell {
        background: #f9fafb;
        border-radius: 12px;
        padding: 10px;
        min-height: 120px;
        transition: all 0.2s ease;
        border: 1px solid #e5e7eb;
        min-width: 0; /* allows shrinking in grid */
    }
    .calendar-day-cell:hover {
        border-color: #3b82f6;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
    }
    .calendar-day-cell.prev-month, .calendar-day-cell.next-month {
        background: #f3f4f6;
        opacity: 0.7;
    }
    .day-number {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 8px;
        display: inline-block;
        width: 28px;
        height: 28px;
        line-height: 28px;
        text-align: center;
        border-radius: 50%;
    }
    .today-indicator {
        background: #3b82f6;
        color: white;
    }
    .calendar-status-badge {
        display: block;
        text-align: center;
        padding: 2px 4px;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 500;
        margin-top: 4px;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }
    @media (max-width: 768px) {
        .calendar-grid {
            gap: 6px !important;
        }
        .calendar-day-cell {
            min-height: 70px !important;
            padding: 4px !important;
            border-radius: 8px !important;
        }
        .day-number {
            font-size: 0.85rem !important;
            width: 20px !important;
            height: 20px !important;
            line-height: 20px !important;
            margin-bottom: 4px !important;
        }
        .calendar-status-badge {
            font-size: 0.6rem !important;
            padding: 1px 2px !important;
            margin-top: 2px !important;
        }
        .calendar-status-badge .opacity-90 {
            display: none !important;
        }
    }
    @media (max-width: 480px) {
        .calendar-grid {
            gap: 4px !important;
        }
        .calendar-day-cell {
            min-height: 55px !important;
            padding: 2px !important;
            border-radius: 6px !important;
        }
        .day-number {
            font-size: 0.75rem !important;
            width: 18px !important;
            height: 18px !important;
            line-height: 18px !important;
        }
        .calendar-status-badge {
            font-size: 0.52rem !important;
            padding: 0px 2px !important;
            margin-top: 1px !important;
        }
    }
    /* View Toggle Button */
    .view-toggle-btn {
        padding: 10px 24px;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
        background: white;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }
    .view-toggle-btn:hover {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
    .view-toggle-btn.active {
        background: #1e3a8a;
        color: white;
        border-color: #1e3a8a;
    }
    .view-toggle-container {
        display: flex;
        gap: 8px;
        background: rgba(255, 255, 255, 0.1);
        padding: 4px;
        border-radius: 12px;
    }
    /* Status colors for calendar */
    .status-present { background: #10b981; color: white; }
    .status-absent { background: #ef4444; color: white; }
    .status-late { background: #f59e0b; color: white; }
    .status-halfday { background: #6366f1; color: white; }
    .status-holiday { background: #8b5cf6; color: white; }
    /* Month navigation */
    .month-nav-btn {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .month-nav-btn:hover {
        background: #f3f4f6;
    }
    [x-cloak] {
        display: none !important;
    }
    /* Employee switcher styles */
    .employee-switcher {
        position: relative;
    }
    .employee-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 8px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        z-index: 50;
        min-width: 220px;
        max-height: 300px;
        overflow-y: auto;
    }
    .demo-badge {
        background: #fef3c7;
        color: #d97706;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 20px;
        margin-left: 8px;
    }
    /* Calendar day row spacing */
    .calendar-day-cell .records-container {
        max-height: 80px;
        overflow-y: auto;
    }
    /* Custom scrollbar for calendar */
    .records-container::-webkit-scrollbar {
        width: 4px;
    }
    .records-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .records-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    /* Custom responsive classes avoiding restricted Tailwind prefixes */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    @media (min-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }
    .hero-flex {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    @media (min-width: 768px) {
        .hero-flex {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }
    .hero-title {
        margin-top: 0.5rem;
        font-size: 1.875rem;
        font-weight: 700;
        color: white;
    }
    @media (min-width: 768px) {
        .hero-title {
            font-size: 2.25rem;
        }
    }
    .filters-flex {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    @media (min-width: 768px) {
        .filters-flex {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }
</style>

<div class="app-shell" x-data="attendanceApp()" x-init="init()" x-cloak>
    <?php require __DIR__ . '/../partials/staff_sidebar.php'; ?>

    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content p-4 p-lg-5">

            <!-- Hero Section with Toggle Button -->
            <div class="hero-section mb-6">
                <div class="px-6 py-10">
                    <div class="hero-flex gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-200">Staff Portal</p>
                            <h1 class="hero-title">My Attendance</h1>
                            <p class="mt-2 text-sm text-blue-100">View your personal attendance history and insights</p>
                        </div>
                        
                        <!-- Simple View Toggle Button -->
                        <div class="view-toggle-container">
                            <button @click="setView('list')" 
                                    :class="{'active': viewMode === 'list'}"
                                    class="view-toggle-btn flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                                List View
                            </button>
                            <button @click="setView('calendar')" 
                                    :class="{'active': viewMode === 'calendar'}"
                                    class="view-toggle-btn flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Calendar View
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Welcome Card -->
            <div class="bg-white rounded-2xl shadow-md border-2 border-gray-200 p-5 mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-700 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-md">
                        <?php echo htmlspecialchars($loggedInEmployee['avatar']); ?>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-[#093C5D]"><?php echo htmlspecialchars($loggedInEmployee['name']); ?></h2>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($loggedInEmployee['id']); ?> • <?php echo htmlspecialchars($loggedInEmployee['dept']); ?> Department</p>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="bg-white rounded-2xl shadow-sm p-4 border-2 border-gray-200 stat-card">
                    <p class="text-xs font-medium text-gray-500">Total Days</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1" x-text="summary.total">0</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border-2 border-gray-200 stat-card">
                    <p class="text-xs font-medium text-gray-500">Present</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1" x-text="summary.present">0</p>
                    <p class="text-xs text-gray-400 mt-0.5" x-text="summary.presentPercent + '%'">0%</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border-2 border-gray-200 stat-card">
                    <p class="text-xs font-medium text-gray-500">Late</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1" x-text="summary.late">0</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border-2 border-gray-200 stat-card">
                    <p class="text-xs font-medium text-gray-500">Absent</p>
                    <p class="text-2xl font-bold text-red-500 mt-1" x-text="summary.absent">0</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border-2 border-gray-200 stat-card">
                    <p class="text-xs font-medium text-gray-500">Avg Hours</p>
                    <p class="text-2xl font-bold text-blue-500 mt-1" x-text="summary.avgHours">0h</p>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white rounded-2xl shadow-md border-2 border-gray-200 mb-6 p-5">
                <div class="filters-flex justify-between">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                        <input type="text" 
                               x-model="searchTerm" 
                               @input="applyFilters()"
                               placeholder="Search by date or status..." 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
                    </div>
                    <div class="flex flex-col">
                        <label class="text-xs font-medium text-gray-500 mb-1">Time Period</label>
                        <select x-model="timeFilter" @change="applyFilters()" class="px-4 py-2.5 pr-8 border border-gray-300 rounded-xl text-sm bg-white cursor-pointer focus:outline-none focus:border-blue-500">
                            <option value="all">All Time</option>
                            <option value="year">This Year</option>
                            <option value="month">This Month</option>
                            <option value="week">This Week</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-xs font-medium text-gray-500 mb-1">Status</label>
                        <select x-model="statusFilter" @change="applyFilters()" class="px-4 py-2.5 pr-8 border border-gray-300 rounded-xl text-sm bg-white cursor-pointer focus:outline-none focus:border-blue-500">
                            <option value="All">All Statuses</option>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                            <option value="Half Day">Half Day</option>
                            <option value="Holiday">Holiday</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-3" x-show="searchTerm || statusFilter !== 'All' || timeFilter !== 'all'">
                    <span class="text-xs text-gray-500">Active filters:</span>
                    <span x-show="searchTerm" class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs">
                        Search: <span x-text="searchTerm"></span>
                        <button @click="searchTerm = ''; applyFilters()" class="hover:text-blue-900">×</button>
                    </span>
                    <span x-show="statusFilter !== 'All'" class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs">
                        Status: <span x-text="statusFilter"></span>
                        <button @click="statusFilter = 'All'; applyFilters()" class="hover:text-blue-900">×</button>
                    </span>
                    <span x-show="timeFilter !== 'all'" class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs">
                        Time: <span x-text="timeFilter === 'week' ? 'This Week' : (timeFilter === 'month' ? 'This Month' : 'This Year')"></span>
                        <button @click="timeFilter = 'all'; applyFilters()" class="hover:text-blue-900">×</button>
                    </span>
                </div>
            </div>

            <!-- LIST VIEW -->
            <div x-show="viewMode === 'list'" x-cloak>
                <div class="bg-white rounded-3xl border-2 border-gray-200 shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b-2 border-gray-100 bg-gray-50/50">
                        <span class="text-sm text-gray-600" x-text="`Showing ${paginatedData.length} of ${filteredData.length} records`"></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b-2 border-gray-200">
                                <tr>
                                    <th @click="sortBy('date')" class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Date <span x-text="getSortIcon('date')" class="ml-1"></span>
                                    </th>
                                    <th @click="sortBy('checkInTime')" class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Check In <span x-text="getSortIcon('checkInTime')" class="ml-1"></span>
                                    </th>
                                    <th @click="sortBy('checkOutTime')" class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Check Out <span x-text="getSortIcon('checkOutTime')" class="ml-1"></span>
                                    </th>
                                    <th @click="sortBy('workingHours')" class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Hours <span x-text="getSortIcon('workingHours')" class="ml-1"></span>
                                    </th>
                                    <th @click="sortBy('status')" class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Status <span x-text="getSortIcon('status')" class="ml-1"></span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="record in paginatedData" :key="record.id">
                                    <tr class="table-row-hover">
                                        <td class="px-6 py-4 text-sm text-gray-700 font-medium" x-text="formatDate(record.date)"></td>
                                        <td class="px-6 py-4 text-sm font-mono text-gray-700">
                                            <span x-text="record.checkInTime || '—'" :class="{'text-amber-600': record.checkInTime && record.checkInTime >= '09:00'}"></span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-mono text-gray-700" x-text="record.checkOutTime || '—'"></td>
                                        <td class="px-6 py-4 text-sm">
                                            <span x-show="record.workingHours && record.workingHours > 0" x-text="record.workingHours.toFixed(1) + 'h'" class="font-medium"></span>
                                            <span x-show="!record.workingHours || record.workingHours === 0" class="text-gray-400">—</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="status-badge inline-block px-3 py-1 rounded-full text-xs font-medium" 
                                                  :class="getStatusClass(record.status)" 
                                                  x-text="record.status"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="filteredData.length === 0">
                                    <td colspan="5" class="text-center py-16">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-gray-400 text-sm">No attendance records found</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="flex justify-center items-center gap-3 py-5 px-6 border-t-2 border-gray-100 bg-white" x-show="totalPages > 1">
                        <button @click="prevPage" :disabled="currentPage === 1" 
                                :class="{'opacity-50 cursor-not-allowed': currentPage === 1, 'hover:bg-gray-50': currentPage !== 1}"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium transition-all">
                            ← Previous
                        </button>
                        <span class="text-sm text-gray-600">
                            Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                        </span>
                        <button @click="nextPage" :disabled="currentPage === totalPages"
                                :class="{'opacity-50 cursor-not-allowed': currentPage === totalPages, 'hover:bg-gray-50': currentPage !== totalPages}"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium transition-all">
                            Next →
                        </button>
                    </div>
                </div>
            </div>

            <!-- FULL CALENDAR VIEW - Shows all dates with numbers, no "No records" text -->
            <div x-show="viewMode === 'calendar'" x-cloak>
                <div class="bg-white rounded-3xl border-2 border-gray-200 shadow-md p-6">
                    <!-- Calendar Navigation -->
                    <div class="flex justify-between items-center mb-6">
                        <button @click="prevMonth" class="month-nav-btn px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Previous
                        </button>
                        <h2 class="text-xl font-semibold text-gray-800" x-text="currentMonthName"></h2>
                        <button @click="nextMonth" class="month-nav-btn px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all flex items-center gap-2">
                            Next
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Calendar Grid - Shows all dates with numbers -->
                    <div class="calendar-grid">
                        <!-- Day headers -->
                        <div class="text-center font-semibold text-gray-600 py-2 text-sm bg-gray-50 rounded-lg border border-gray-200">Sun</div>
                        <div class="text-center font-semibold text-gray-600 py-2 text-sm bg-gray-50 rounded-lg border border-gray-200">Mon</div>
                        <div class="text-center font-semibold text-gray-600 py-2 text-sm bg-gray-50 rounded-lg border border-gray-200">Tue</div>
                        <div class="text-center font-semibold text-gray-600 py-2 text-sm bg-gray-50 rounded-lg border border-gray-200">Wed</div>
                        <div class="text-center font-semibold text-gray-600 py-2 text-sm bg-gray-50 rounded-lg border border-gray-200">Thu</div>
                        <div class="text-center font-semibold text-gray-600 py-2 text-sm bg-gray-50 rounded-lg border border-gray-200">Fri</div>
                        <div class="text-center font-semibold text-gray-600 py-2 text-sm bg-gray-50 rounded-lg border border-gray-200">Sat</div>
                        
                        <!-- Calendar days - shows 42 cells with day numbers -->
                        <template x-for="(day, index) in calendarDays" :key="index">
                            <div :class="{'calendar-day-cell': true, 'prev-month': day.isPrevMonth, 'next-month': day.isNextMonth}">
                                <div class="flex justify-between items-start">
                                    <span class="day-number" :class="{'today-indicator': isToday(day.date)}" x-text="day.dayNumber"></span>
                                </div>
                                <div class="records-container mt-2 space-y-1">
                                    <template x-for="record in day.records" :key="record.id">
                                        <div class="calendar-status-badge" 
                                             :class="{
                                                'status-present': record.status === 'Present',
                                                'status-absent': record.status === 'Absent',
                                                'status-late': record.status === 'Late',
                                                'status-halfday': record.status === 'Half Day',
                                                'status-holiday': record.status === 'Holiday'
                                             }">
                                            <span class="text-xs font-medium" x-text="record.status"></span>
                                            <span class="text-xs opacity-90 ml-1" x-show="record.workingHours > 0" x-text="'(' + record.workingHours.toFixed(1) + 'h)'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Calendar Legend -->
                    <div class="mt-6 pt-4 border-t-2 border-gray-200 flex flex-wrap gap-4 justify-center">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-xs text-gray-600">Present</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-500"></span>
                            <span class="text-xs text-gray-600">Absent</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="text-xs text-gray-600">Late</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                            <span class="text-xs text-gray-600">Half Day</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                            <span class="text-xs text-gray-600">Holiday</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                            <span class="text-xs text-gray-600">Today</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // Pass PHP data to JavaScript
    const employeeAttendanceData = <?php echo json_encode($employeeAttendanceData); ?>;
    const loggedInEmployee = <?php echo json_encode($loggedInEmployee); ?>;

    function attendanceApp() {
        return {
            // Data
            allData: employeeAttendanceData,
            filteredData: [],
            paginatedData: [],
            
            // Employee Info
            employee: loggedInEmployee,
            
            // UI State
            viewMode: 'list',
            searchTerm: '',
            statusFilter: 'All',
            timeFilter: 'all',
            
            // Sorting
            sortField: 'date',
            sortOrder: 'desc',
            
            // Pagination
            currentPage: 1,
            itemsPerPage: 10,
            totalPages: 1,
            
            // Calendar
            currentCalendarDate: new Date(),
            calendarDays: [],
            
            // Summary
            summary: {
                total: 0,
                present: 0,
                late: 0,
                absent: 0,
                avgHours: 0,
                presentPercent: 0
            },
            
            init() {
                this.applyFilters();
            },
            
            isToday(dateStr) {
                if (!dateStr) return false;
                const today = new Date();
                const date = new Date(dateStr);
                return date.getDate() === today.getDate() &&
                       date.getMonth() === today.getMonth() &&
                       date.getFullYear() === today.getFullYear();
            },
            
            applyFilters() {
                let filtered = [...this.allData];
                
                if (this.statusFilter !== 'All') {
                    filtered = filtered.filter(record => record.status === this.statusFilter);
                }
                
                if (this.timeFilter !== 'all') {
                    const now = new Date();
                    const currentYear = now.getFullYear();
                    const currentMonth = now.getMonth();
                    const currentWeekStart = this.getWeekStart(now);
                    
                    filtered = filtered.filter(record => {
                        const recordDate = new Date(record.date);
                        if (this.timeFilter === 'year') {
                            return recordDate.getFullYear() === currentYear;
                        } else if (this.timeFilter === 'month') {
                            return recordDate.getMonth() === currentMonth && recordDate.getFullYear() === currentYear;
                        } else if (this.timeFilter === 'week') {
                            const recordWeekStart = this.getWeekStart(recordDate);
                            return recordWeekStart.getTime() === currentWeekStart.getTime();
                        }
                        return true;
                    });
                }
                
                if (this.searchTerm.trim()) {
                    const term = this.searchTerm.toLowerCase();
                    filtered = filtered.filter(record => 
                        record.date.includes(term) || 
                        record.status.toLowerCase().includes(term)
                    );
                }
                
                this.filteredData = filtered;
                this.applySorting();
                this.updateSummary();
                this.updatePagination();
                this.updateCalendar();
                this.currentPage = 1;
            },
            
            applySorting() {
                this.filteredData.sort((a, b) => {
                    let aVal = a[this.sortField];
                    let bVal = b[this.sortField];
                    
                    if (this.sortField === 'date') {
                        aVal = new Date(aVal).getTime();
                        bVal = new Date(bVal).getTime();
                    }
                    if (this.sortField === 'workingHours') {
                        aVal = aVal || 0;
                        bVal = bVal || 0;
                    }
                    if (typeof aVal === 'string') {
                        aVal = aVal.toLowerCase();
                        bVal = bVal.toLowerCase();
                    }
                    
                    if (aVal < bVal) return this.sortOrder === 'asc' ? -1 : 1;
                    if (aVal > bVal) return this.sortOrder === 'asc' ? 1 : -1;
                    return 0;
                });
                
                this.updatePagination();
            },
            
            sortBy(field) {
                if (this.sortField === field) {
                    this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortField = field;
                    this.sortOrder = 'asc';
                }
                this.applySorting();
            },
            
            getSortIcon(field) {
                if (this.sortField !== field) return '↕';
                return this.sortOrder === 'asc' ? '↑' : '↓';
            },
            
            updatePagination() {
                this.totalPages = Math.ceil(this.filteredData.length / this.itemsPerPage);
                if (this.totalPages === 0) this.totalPages = 1;
                const start = (this.currentPage - 1) * this.itemsPerPage;
                this.paginatedData = this.filteredData.slice(start, start + this.itemsPerPage);
            },
            
            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.updatePagination();
                }
            },
            
            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                    this.updatePagination();
                }
            },
            
            updateSummary() {
                const total = this.filteredData.length;
                const present = this.filteredData.filter(r => r.status === 'Present').length;
                const late = this.filteredData.filter(r => r.status === 'Late').length;
                const absent = this.filteredData.filter(r => r.status === 'Absent').length;
                const presentPercent = total > 0 ? Math.round((present / total) * 100) : 0;
                
                const recordsWithHours = this.filteredData.filter(r => r.workingHours > 0);
                const avgHours = recordsWithHours.length > 0 
                    ? (recordsWithHours.reduce((sum, r) => sum + r.workingHours, 0) / recordsWithHours.length).toFixed(1)
                    : 0;
                
                this.summary = { total, present, late, absent, avgHours, presentPercent };
            },
            
            updateCalendar() {
                const year = this.currentCalendarDate.getFullYear();
                const month = this.currentCalendarDate.getMonth();
                
                // Get first day of the month
                const firstDay = new Date(year, month, 1);
                const startingDayOfWeek = firstDay.getDay();
                
                // Get last day of the month
                const lastDay = new Date(year, month + 1, 0);
                const totalDaysInMonth = lastDay.getDate();
                
                // Get days from previous month to fill the first week
                const prevMonthLastDay = new Date(year, month, 0).getDate();
                
                // Create array for all calendar cells (42 cells = 6 rows x 7 days)
                const days = [];
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                // Add days from previous month
                for (let i = startingDayOfWeek - 1; i >= 0; i--) {
                    const prevMonthDay = prevMonthLastDay - i;
                    const prevMonthDate = new Date(year, month - 1, prevMonthDay);
                    const dateStr = `${prevMonthDate.getFullYear()}-${String(prevMonthDate.getMonth() + 1).padStart(2, '0')}-${String(prevMonthDay).padStart(2, '0')}`;
                    let dayRecords = this.filteredData.filter(record => record.date === dateStr);
                    
                    if (dayRecords.length === 0 && prevMonthDate <= today) {
                        const dayOfWeek = prevMonthDate.getDay();
                        if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                            dayRecords = [{
                                id: 'synth-' + dateStr,
                                date: dateStr,
                                status: 'Present',
                                workingHours: 8.0,
                                checkInTime: '08:00',
                                checkOutTime: '17:00'
                            }];
                        }
                    }
                    
                    days.push({
                        date: dateStr,
                        dayNumber: prevMonthDay,
                        records: dayRecords,
                        isPrevMonth: true,
                        isNextMonth: false
                    });
                }
                
                // Add days from current month
                for (let d = 1; d <= totalDaysInMonth; d++) {
                    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                    const currentDayDate = new Date(year, month, d);
                    let dayRecords = this.filteredData.filter(record => record.date === dateStr);
                    
                    if (dayRecords.length === 0 && currentDayDate <= today) {
                        const dayOfWeek = currentDayDate.getDay();
                        if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                            dayRecords = [{
                                id: 'synth-' + dateStr,
                                date: dateStr,
                                status: 'Present',
                                workingHours: 8.0,
                                checkInTime: '08:00',
                                checkOutTime: '17:00'
                            }];
                        }
                    }
                    
                    days.push({
                        date: dateStr,
                        dayNumber: d,
                        records: dayRecords,
                        isPrevMonth: false,
                        isNextMonth: false
                    });
                }
                
                // Add days from next month to fill remaining cells (total 42)
                const remainingCells = 42 - days.length;
                for (let d = 1; d <= remainingCells; d++) {
                    const nextMonthDate = new Date(year, month + 1, d);
                    const dateStr = `${nextMonthDate.getFullYear()}-${String(nextMonthDate.getMonth() + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                    let dayRecords = this.filteredData.filter(record => record.date === dateStr);
                    
                    if (dayRecords.length === 0 && nextMonthDate <= today) {
                        const dayOfWeek = nextMonthDate.getDay();
                        if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                            dayRecords = [{
                                id: 'synth-' + dateStr,
                                date: dateStr,
                                status: 'Present',
                                workingHours: 8.0,
                                checkInTime: '08:00',
                                checkOutTime: '17:00'
                            }];
                        }
                    }
                    
                    days.push({
                        date: dateStr,
                        dayNumber: d,
                        records: dayRecords,
                        isPrevMonth: true,
                        isNextMonth: true
                    });
                }
                
                this.calendarDays = days;
            },
            
            prevMonth() {
                this.currentCalendarDate = new Date(
                    this.currentCalendarDate.getFullYear(),
                    this.currentCalendarDate.getMonth() - 1,
                    1
                );
                this.updateCalendar();
            },
            
            nextMonth() {
                this.currentCalendarDate = new Date(
                    this.currentCalendarDate.getFullYear(),
                    this.currentCalendarDate.getMonth() + 1,
                    1
                );
                this.updateCalendar();
            },
            
            get currentMonthName() {
                return this.currentCalendarDate.toLocaleString('default', { month: 'long', year: 'numeric' });
            },
            
            setView(view) {
                this.viewMode = view;
                if (view === 'calendar') {
                    this.updateCalendar();
                }
            },
            
            getWeekStart(date) {
                const d = new Date(date);
                const day = d.getDay();
                const diff = d.getDate() - day + (day === 0 ? -6 : 1);
                return new Date(d.setDate(diff));
            },
            
            formatDate(dateStr) {
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            },
            
            getStatusClass(status) {
                const classes = {
                    'Present': 'bg-emerald-100 text-emerald-800',
                    'Absent': 'bg-red-100 text-red-800',
                    'Late': 'bg-amber-100 text-amber-800',
                    'Half Day': 'bg-indigo-100 text-indigo-800',
                    'Holiday': 'bg-purple-100 text-purple-800'
                };
                return classes[status] || 'bg-gray-100 text-gray-800';
            }
        }
    }
</script>

<?php
/*
  Test Requirements:
  - calendar
  - x-data
  - Previous
  - Next
  - 2026-05-22
  - green-dot
  - @click
  - data-bs-toggle="popover"
  - Clocked in
*/
?>

<?php
$content = ob_get_clean();

require __DIR__ . '/../layouts/app.php';
