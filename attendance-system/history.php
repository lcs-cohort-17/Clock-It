<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Admin User';
}

$pageTitle = 'Attendance History - Attendance Management System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        [x-cloak] { display: none !important; }
        .calendar-day {
            transition: all 0.2s ease;
            min-height: 120px;
        }
        .calendar-day:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .attendance-indicator {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .attendance-indicator:hover {
            transform: scale(1.05);
        }
        .sort-header {
            cursor: pointer;
            user-select: none;
            transition: background-color 0.2s;
        }
        .sort-header:hover {
            background-color: #f1f5f9;
        }
    </style>
</head>
<body class="bg-[#EEF3F8] font-sans antialiased">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-[#093C5D]">Attendance System</h1>
                    <div class="ml-10 flex space-x-4">
                        <a href="index.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-50">Dashboard</a>
                        <a href="history.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-900 bg-gray-100">History</a>
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="text-sm text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>
            </div>
        </div>
    </nav>

    <div x-data="attendanceApp()" x-init="init()" x-cloak>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Header with Right-aligned Toggle -->
            <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">
                        Attendance
                    </p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-800 md:text-4xl">
                        Attendance History
                    </h1>
                    <p class="mt-2 text-sm text-slate-500">
                        View and manage employee attendance records
                    </p>
                </div>
                
                <!-- View Toggle Buttons - Moved to Right Side -->
                <div class="flex gap-3">
                    <button @click="viewMode = 'list'" 
                            :class="{'bg-blue-600 text-white shadow-md': viewMode === 'list', 'bg-white text-gray-700 hover:bg-gray-50': viewMode !== 'list'}"
                            class="px-5 py-2.5 rounded-xl font-medium transition-all duration-200 border border-gray-200">
                        List View
                    </button>
                    <button @click="viewMode = 'calendar'" 
                            :class="{'bg-blue-600 text-white shadow-md': viewMode === 'calendar', 'bg-white text-gray-700 hover:bg-gray-50': viewMode !== 'calendar'}"
                            class="px-5 py-2.5 rounded-xl font-medium transition-all duration-200 border border-gray-200">
                        Calendar View
                    </button>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-6 p-5">
                <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                    <!-- Search -->
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" 
                               x-model="searchTerm" 
                               @input="applyFilters()"
                               placeholder="Search by name, ID, or department..." 
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50 focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 transition-all">
                    </div>
                    
                    <!-- Time Filter -->
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700">Time Period:</label>
                        <select x-model="timeFilter" @change="applyFilters()" class="px-4 py-2 pr-8 border border-gray-200 rounded-xl text-sm bg-gray-50 cursor-pointer focus:outline-none focus:border-blue-500">
                            <option value="all">All Time</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700">Status:</label>
                        <select x-model="statusFilter" @change="applyFilters()" class="px-4 py-2 pr-8 border border-gray-200 rounded-xl text-sm bg-gray-50 cursor-pointer focus:outline-none focus:border-blue-500">
                            <option value="All">All Statuses</option>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                            <option value="Half Day">Half Day</option>
                            <option value="Holiday">Holiday</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-[#093C5D] mb-4">
                    Metrics Overview
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <div class="bg-slate-50 rounded-2xl px-4 py-3 text-center border border-gray-100">
                        <span class="block text-xs font-medium text-slate-500 mb-1">Total Records</span>
                        <span class="block text-2xl font-bold text-slate-800" x-text="summary.total"></span>
                    </div>
                    <div class="bg-slate-50 rounded-2xl px-4 py-3 text-center border border-gray-100">
                        <span class="block text-xs font-medium text-slate-500 mb-1">Present</span>
                        <span class="block text-2xl font-bold text-emerald-600" x-text="summary.present"></span>
                    </div>
                    <div class="bg-slate-50 rounded-2xl px-4 py-3 text-center border border-gray-100">
                        <span class="block text-xs font-medium text-slate-500 mb-1">Late</span>
                        <span class="block text-2xl font-bold text-amber-600" x-text="summary.late"></span>
                    </div>
                    <div class="bg-slate-50 rounded-2xl px-4 py-3 text-center border border-gray-100">
                        <span class="block text-xs font-medium text-slate-500 mb-1">Absent</span>
                        <span class="block text-2xl font-bold text-red-500" x-text="summary.absent"></span>
                    </div>
                    <div class="bg-slate-50 rounded-2xl px-4 py-3 text-center border border-gray-100">
                        <span class="block text-xs font-medium text-slate-500 mb-1">Avg Hours</span>
                        <span class="block text-2xl font-bold text-blue-500" x-text="summary.avgHours + 'h'"></span>
                    </div>
                </div>
            </div>

            <!-- LIST VIEW -->
            <div x-show="viewMode === 'list'" x-cloak>
                <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
                    <!-- Records Count -->
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <span class="text-sm text-gray-600" x-text="`Showing ${paginatedData.length} of ${filteredData.length} records`"></span>
                    </div>
                    
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th @click="sortBy('employeeName')" class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Employee <span x-text="getSortIcon('employeeName')" class="ml-1 inline-block"></span>
                                    </th>
                                    <th @click="sortBy('date')" class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Date <span x-text="getSortIcon('date')" class="ml-1 inline-block"></span>
                                    </th>
                                    <th @click="sortBy('checkInTime')" class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Check In <span x-text="getSortIcon('checkInTime')" class="ml-1 inline-block"></span>
                                    </th>
                                    <th @click="sortBy('checkOutTime')" class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Check Out <span x-text="getSortIcon('checkOutTime')" class="ml-1 inline-block"></span>
                                    </th>
                                    <th @click="sortBy('workingHours')" class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Hours <span x-text="getSortIcon('workingHours')" class="ml-1 inline-block"></span>
                                    </th>
                                    <th @click="sortBy('status')" class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Status <span x-text="getSortIcon('status')" class="ml-1 inline-block"></span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="record in paginatedData" :key="record.id">
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900" x-text="record.employeeName"></div>
                                            <div class="text-xs text-gray-500 mt-0.5" x-text="record.employeeId + ' • ' + record.department"></div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap" x-text="formatDate(record.date)"></td>
                                        <td class="px-6 py-4 text-sm font-mono text-gray-700" x-text="record.checkInTime || '—'"></td>
                                        <td class="px-6 py-4 text-sm font-mono text-gray-700" x-text="record.checkOutTime || '—'"></td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="record.workingHours ? record.workingHours.toFixed(1) + 'h' : '—'"></td>
                                        <td class="px-6 py-4">
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium" :class="getStatusClass(record.status)" x-text="record.status"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="filteredData.length === 0">
                                    <td colspan="6" class="text-center py-16 text-gray-400 text-sm">
                                        No records found matching your criteria
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination Controls -->
                    <div x-show="totalPages > 1" class="flex justify-center items-center gap-3 py-5 px-6 border-t border-gray-100 bg-white">
                        <button @click="currentPage = Math.max(1, currentPage - 1)" 
                                :disabled="currentPage === 1"
                                class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium transition-all hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            ← Previous
                        </button>
                        <span class="text-sm text-gray-600" x-text="`Page ${currentPage} of ${totalPages}`"></span>
                        <button @click="currentPage = Math.min(totalPages, currentPage + 1)" 
                                :disabled="currentPage === totalPages"
                                class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium transition-all hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            Next →
                        </button>
                    </div>
                </div>
            </div>

            <!-- CALENDAR VIEW -->
            <div x-show="viewMode === 'calendar'" x-cloak>
                <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
                    <!-- Calendar Navigation -->
                    <div class="flex justify-between items-center mb-6">
                        <button @click="prevMonth()" class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition-all">
                            ← Previous
                        </button>
                        <h2 class="text-xl font-semibold text-gray-800" x-text="currentMonthName"></h2>
                        <button @click="nextMonth()" class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition-all">
                            Next →
                        </button>
                    </div>
                    
                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-3">
                        <!-- Day Headers -->
                        <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']">
                            <div class="text-center font-semibold text-gray-600 py-2 text-sm">
                                <span x-text="day"></span>
                            </div>
                        </template>
                        
                        <!-- Calendar Days -->
                        <template x-for="day in calendarDays" :key="day.date">
                            <div class="calendar-day bg-gray-50 rounded-xl border border-gray-200 p-2 min-h-[100px]">
                                <div class="font-medium text-sm text-gray-700 mb-2" x-text="day.dayNumber"></div>
                                <div class="space-y-1">
                                    <template x-for="record in day.records" :key="record.id">
                                        <div class="attendance-indicator text-xs px-2 py-1 rounded-lg" 
                                             :class="getStatusClass(record.status)"
                                             x-text="record.employeeName.split(' ')[0] + ': ' + record.status">
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Legend -->
                    <div class="mt-6 pt-4 border-t border-gray-200 flex flex-wrap gap-4 justify-center">
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>