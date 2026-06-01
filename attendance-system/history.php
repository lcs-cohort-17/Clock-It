<?php
// No PHP includes needed for standalone testing
// The data is loaded via AJAX from api/attendance.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History - Attendance Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .calendar-day {
            transition: all 0.2s ease;
            min-height: 120px;
            cursor: pointer;
        }
        .calendar-day:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .sort-header {
            cursor: pointer;
            user-select: none;
            transition: background-color 0.2s;
        }
        .sort-header:hover {
            background-color: #f1f5f9;
        }
        .hero-section {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            position: relative;
            overflow: hidden;
        }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0,0,0,.1);
            border-radius: 50%;
            border-top-color: #3b82f6;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
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
                    <span class="text-sm text-gray-600">Welcome, Admin User</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-200">Attendance Management</p>
                    <h1 class="mt-2 text-4xl font-bold text-white md:text-5xl">Attendance History</h1>
                    <p class="mt-2 text-base text-blue-100">View and manage employee attendance records</p>
                </div>
                
                <div class="flex flex-col items-end gap-3">
                    <button id="exportBtn" class="px-6 py-3 rounded-xl font-medium transition-all duration-200 bg-white text-blue-600 hover:bg-blue-50 shadow-md border-2 border-blue-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Export History
                    </button>
                    
                    <div class="flex rounded-lg shadow-md border-2 border-blue-200 bg-white p-1">
                        <button id="listViewBtn" class="px-5 py-2 text-sm font-medium rounded-md transition-all duration-200 bg-blue-600 text-white shadow-sm">List View</button>
                        <button id="calendarViewBtn" class="px-5 py-2 text-sm font-medium rounded-md transition-all duration-200 text-gray-700 hover:bg-gray-50">Calendar View</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Filters Bar -->
        <div class="bg-white rounded-2xl shadow-md border-2 border-gray-200 mb-6 p-5">
            <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                    <input type="text" id="searchInput" placeholder="Search by name, ID, or department..." class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
                </div>
                <div class="flex flex-col">
                    <label class="text-xs font-medium text-gray-500 mb-1">Time Period</label>
                    <select id="timeFilter" class="px-4 py-2.5 pr-8 border border-gray-300 rounded-xl text-sm bg-white cursor-pointer focus:outline-none focus:border-blue-500">
                        <option value="all">All Time</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label class="text-xs font-medium text-gray-500 mb-1">Status</label>
                    <select id="statusFilter" class="px-4 py-2.5 pr-8 border border-gray-300 rounded-xl text-sm bg-white cursor-pointer focus:outline-none focus:border-blue-500">
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
        <div class="bg-white rounded-3xl border-2 border-gray-200 shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-[#093C5D] mb-4">Metrics Overview</h2>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div class="bg-slate-50 rounded-2xl px-4 py-3 text-center border-2 border-gray-100">
                    <span class="block text-xs font-medium text-slate-500 mb-1">Total Records</span>
                    <span class="block text-2xl font-bold text-slate-800" id="totalRecords">0</span>
                </div>
                <div class="bg-slate-50 rounded-2xl px-4 py-3 text-center border-2 border-gray-100">
                    <span class="block text-xs font-medium text-slate-500 mb-1">Present</span>
                    <span class="block text-2xl font-bold text-emerald-600" id="presentCount">0</span>
                </div>
                <div class="bg-slate-50 rounded-2xl px-4 py-3 text-center border-2 border-gray-100">
                    <span class="block text-xs font-medium text-slate-500 mb-1">Late</span>
                    <span class="block text-2xl font-bold text-amber-600" id="lateCount">0</span>
                </div>
                <div class="bg-slate-50 rounded-2xl px-4 py-3 text-center border-2 border-gray-100">
                    <span class="block text-xs font-medium text-slate-500 mb-1">Absent</span>
                    <span class="block text-2xl font-bold text-red-500" id="absentCount">0</span>
                </div>
                <div class="bg-slate-50 rounded-2xl px-4 py-3 text-center border-2 border-gray-100">
                    <span class="block text-xs font-medium text-slate-500 mb-1">Avg Hours</span>
                    <span class="block text-2xl font-bold text-blue-500" id="avgHours">0h</span>
                </div>
            </div>
        </div>

        <!-- LIST VIEW -->
        <div id="listView" class="bg-white rounded-3xl border-2 border-gray-200 shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b-2 border-gray-100 bg-gray-50/50">
                <span class="text-sm text-gray-600" id="recordCount">Loading...</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600" data-sort="employeeName">Employee ↕</th>
                            <th class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600" data-sort="date">Date ↕</th>
                            <th class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600" data-sort="checkInTime">Check In ↕</th>
                            <th class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600" data-sort="checkOutTime">Check Out ↕</th>
                            <th class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600" data-sort="workingHours">Hours ↕</th>
                            <th class="sort-header px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600" data-sort="status">Status ↕</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="tableBody">
                        <tr><td colspan="6" class="text-center py-16"><div class="loading"></div> Loading data...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-center items-center gap-3 py-5 px-6 border-t-2 border-gray-100 bg-white" id="pagination"></div>
        </div>

        <!-- CALENDAR VIEW -->
        <div id="calendarView" class="bg-white rounded-3xl border-2 border-gray-200 shadow-md p-6" style="display: none;">
            <div class="flex justify-between items-center mb-6">
                <button id="prevMonth" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">← Previous</button>
                <h2 class="text-xl font-semibold text-gray-800" id="currentMonth"></h2>
                <button id="nextMonth" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">Next →</button>
            </div>
            <div class="grid grid-cols-7 gap-3" id="calendarGrid"></div>
            <div class="mt-6 pt-4 border-t-2 border-gray-200 flex flex-wrap gap-4 justify-center">
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500"></span><span class="text-xs text-gray-600">Present</span></div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-500"></span><span class="text-xs text-gray-600">Absent</span></div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-500"></span><span class="text-xs text-gray-600">Late</span></div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-indigo-500"></span><span class="text-xs text-gray-600">Half Day</span></div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-purple-500"></span><span class="text-xs text-gray-600">Holiday</span></div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let allData = [];
        let filteredData = [];
        let currentPage = 1;
        let itemsPerPage = 10;
        let currentView = 'list';
        let currentCalendarDate = new Date();
        let sortField = 'date';
        let sortOrder = 'desc';
        
        // DOM elements
        const searchInput = document.getElementById('searchInput');
        const timeFilter = document.getElementById('timeFilter');
        const statusFilter = document.getElementById('statusFilter');
        const listView = document.getElementById('listView');
        const calendarView = document.getElementById('calendarView');
        const listViewBtn = document.getElementById('listViewBtn');
        const calendarViewBtn = document.getElementById('calendarViewBtn');
        const exportBtn = document.getElementById('exportBtn');
        const tableBody = document.getElementById('tableBody');
        const paginationDiv = document.getElementById('pagination');
        const recordCountSpan = document.getElementById('recordCount');
        
        // Load data
        async function loadData() {
            try {
                const response = await fetch('api/attendance.php');
                if (!response.ok) throw new Error('HTTP error ' + response.status);
                allData = await response.json();
                filteredData = [...allData];
                applyFilters();
            } catch (error) {
                console.error('Error:', error);
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-16 text-red-500">Error loading data. Make sure api/attendance.php exists.</td></tr>';
            }
        }
        
        // Apply filters
        function applyFilters() {
            let filtered = [...allData];
            
            // Status filter
            const status = statusFilter.value;
            if (status !== 'All') {
                filtered = filtered.filter(record => record.status === status);
            }
            
            // Time filter
            const time = timeFilter.value;
            if (time !== 'all') {
                const now = new Date();
                filtered = filtered.filter(record => {
                    const recordDate = new Date(record.date);
                    if (time === 'month') {
                        return recordDate.getMonth() === now.getMonth() && recordDate.getFullYear() === now.getFullYear();
                    } else if (time === 'week') {
                        const weekStart = getWeekStart(now);
                        const recordWeekStart = getWeekStart(recordDate);
                        return recordWeekStart.getTime() === weekStart.getTime();
                    }
                    return true;
                });
            }
            
            // Search filter
            const search = searchInput.value.toLowerCase();
            if (search) {
                filtered = filtered.filter(record => 
                    record.employeeName.toLowerCase().includes(search) ||
                    record.employeeId.toLowerCase().includes(search) ||
                    record.department.toLowerCase().includes(search)
                );
            }
            
            filteredData = filtered;
            updateStats();
            applySorting();
            updatePagination();
            renderTable();
            renderCalendar();
        }
        
        // Apply sorting
        function applySorting() {
            filteredData.sort((a, b) => {
                let aVal = a[sortField];
                let bVal = b[sortField];
                
                if (sortField === 'date') {
                    aVal = new Date(aVal).getTime();
                    bVal = new Date(bVal).getTime();
                }
                if (sortField === 'workingHours') {
                    aVal = aVal || 0;
                    bVal = bVal || 0;
                }
                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }
                
                if (aVal < bVal) return sortOrder === 'asc' ? -1 : 1;
                if (aVal > bVal) return sortOrder === 'asc' ? 1 : -1;
                return 0;
            });
        }
        
        // Get week start
        function getWeekStart(date) {
            const d = new Date(date);
            const day = d.getDay();
            const diff = d.getDate() - day + (day === 0 ? -6 : 1);
            return new Date(d.setDate(diff));
        }
        
        // Update statistics
        function updateStats() {
            const total = filteredData.length;
            const present = filteredData.filter(r => r.status === 'Present').length;
            const late = filteredData.filter(r => r.status === 'Late').length;
            const absent = filteredData.filter(r => r.status === 'Absent').length;
            
            const recordsWithHours = filteredData.filter(r => r.workingHours > 0);
            const avgHours = recordsWithHours.length > 0 
                ? (recordsWithHours.reduce((sum, r) => sum + r.workingHours, 0) / recordsWithHours.length).toFixed(1)
                : 0;
            
            document.getElementById('totalRecords').textContent = total;
            document.getElementById('presentCount').textContent = present;
            document.getElementById('lateCount').textContent = late;
            document.getElementById('absentCount').textContent = absent;
            document.getElementById('avgHours').textContent = avgHours + 'h';
        }
        
        // Update pagination
        function updatePagination() {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            recordCountSpan.textContent = `Showing ${Math.min(filteredData.length, currentPage * itemsPerPage)} of ${filteredData.length} records`;
            
            if (totalPages <= 1) {
                paginationDiv.innerHTML = '';
                return;
            }
            
            paginationDiv.innerHTML = `
                <button onclick="changePage(${currentPage - 1})" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium transition-all hover:bg-gray-50 ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}" ${currentPage === 1 ? 'disabled' : ''}>← Previous</button>
                <span class="text-sm text-gray-600">Page ${currentPage} of ${totalPages}</span>
                <button onclick="changePage(${currentPage + 1})" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium transition-all hover:bg-gray-50 ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''}" ${currentPage === totalPages ? 'disabled' : ''}>Next →</button>
            `;
        }
        
        // Change page
        function changePage(page) {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderTable();
            updatePagination();
        }
        
        // Render table
        function renderTable() {
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const pageData = filteredData.slice(start, end);
            
            if (pageData.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-16 text-gray-400 text-sm">No records found</td></tr>';
                return;
            }
            
            let html = '';
            pageData.forEach(record => {
                const statusClass = getStatusClass(record.status);
                html += `
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">${escapeHtml(record.employeeName)}</div>
                            <div class="text-xs text-gray-500 mt-0.5">${record.employeeId} • ${record.department}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">${formatDate(record.date)}</td>
                        <td class="px-6 py-4 text-sm font-mono text-gray-700">${record.checkInTime || '—'}</td>
                        <td class="px-6 py-4 text-sm font-mono text-gray-700">${record.checkOutTime || '—'}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">${record.workingHours ? record.workingHours.toFixed(1) + 'h' : '—'}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium ${statusClass}">${record.status}</span>
                        </td>
                    </tr>
                `;
            });
            tableBody.innerHTML = html;
        }
        
        // Get status class
        function getStatusClass(status) {
            const classes = {
                'Present': 'bg-emerald-100 text-emerald-800',
                'Absent': 'bg-red-100 text-red-800',
                'Late': 'bg-amber-100 text-amber-800',
                'Half Day': 'bg-indigo-100 text-indigo-800',
                'Holiday': 'bg-purple-100 text-purple-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        }
        
        // Format date
        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }
        
        // Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Render calendar
        function renderCalendar() {
            const year = currentCalendarDate.getFullYear();
            const month = currentCalendarDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startingDayOfWeek = firstDay.getDay();
            
            const days = [];
            const currentMonthRecords = filteredData.filter(record => {
                const recordDate = new Date(record.date);
                return recordDate.getMonth() === month && recordDate.getFullYear() === year;
            });
            
            for (let i = 0; i < startingDayOfWeek; i++) {
                days.push({ date: null, dayNumber: '', records: [] });
            }
            
            for (let d = 1; d <= lastDay.getDate(); d++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                const dayRecords = currentMonthRecords.filter(record => record.date === dateStr);
                days.push({ date: dateStr, dayNumber: d, records: dayRecords });
            }
            
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
            
            let calendarHtml = '';
            const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            weekDays.forEach(day => {
                calendarHtml += `<div class="text-center font-semibold text-gray-600 py-2 text-sm bg-gray-50 rounded-lg border border-gray-200">${day}</div>`;
            });
            
            days.forEach(day => {
                if (!day.date) {
                    calendarHtml += `<div class="calendar-day bg-gray-50 rounded-xl border-2 border-gray-200 p-2 min-h-[100px]"></div>`;
                } else {
                    let dayHtml = `<div class="calendar-day bg-gray-50 rounded-xl border-2 border-gray-200 p-2 min-h-[100px]">
                        <div class="font-medium text-sm text-gray-700 mb-2">${day.dayNumber}</div>
                        <div class="space-y-1">`;
                    day.records.forEach(record => {
                        const statusClass = getStatusClass(record.status);
                        dayHtml += `<div class="text-xs px-2 py-1 rounded-lg ${statusClass}">${record.employeeName.split(' ')[0]}: ${record.status}</div>`;
                    });
                    dayHtml += `</div></div>`;
                    calendarHtml += dayHtml;
                }
            });
            
            document.getElementById('calendarGrid').innerHTML = calendarHtml;
        }
        
        // Calendar navigation
        function prevMonth() {
            currentCalendarDate = new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth() - 1, 1);
            renderCalendar();
        }
        
        function nextMonth() {
            currentCalendarDate = new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth() + 1, 1);
            renderCalendar();
        }
        
        // Export function
        function exportHistoryOnly() {
            const dataToExport = filteredData.filter(record => ['Present', 'Absent', 'Late', 'Half Day', 'Holiday'].includes(record.status));
            
            if (dataToExport.length === 0) {
                alert('No attendance history to export');
                return;
            }
            
            const exportDate = new Date().toLocaleString();
            const filterInfo = [];
            if (statusFilter.value !== 'All') filterInfo.push(`Status: ${statusFilter.value}`);
            if (timeFilter.value !== 'all') filterInfo.push(`Period: ${timeFilter.value === 'week' ? 'This Week' : 'This Month'}`);
            if (searchInput.value) filterInfo.push(`Search: "${searchInput.value}"`);
            
            const present = dataToExport.filter(r => r.status === 'Present').length;
            const absent = dataToExport.filter(r => r.status === 'Absent').length;
            const late = dataToExport.filter(r => r.status === 'Late').length;
            const halfDay = dataToExport.filter(r => r.status === 'Half Day').length;
            const holiday = dataToExport.filter(r => r.status === 'Holiday').length;
            
            let htmlContent = `<!DOCTYPE html>
            <html>
            <head><meta charset="UTF-8"><title>Attendance Report - ${exportDate}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                h1 { color: #1e3a8a; border-bottom: 3px solid #1e3a8a; padding-bottom: 10px; }
                .stats { background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; padding: 20px; border-radius: 10px; margin: 20px 0; }
                .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-top: 15px; }
                .stat-item { text-align: center; padding: 10px; background: rgba(255,255,255,0.2); border-radius: 8px; }
                .stat-number { font-size: 28px; font-weight: bold; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                th { background: #1e3a8a; color: white; }
                .status-Present { background: #10b981; color: white; padding: 4px 12px; border-radius: 20px; display: inline-block; }
                .status-Absent { background: #ef4444; color: white; padding: 4px 12px; border-radius: 20px; display: inline-block; }
                .status-Late { background: #f59e0b; color: white; padding: 4px 12px; border-radius: 20px; display: inline-block; }
                .status-Half\\ Day { background: #6366f1; color: white; padding: 4px 12px; border-radius: 20px; display: inline-block; }
                .status-Holiday { background: #8b5cf6; color: white; padding: 4px 12px; border-radius: 20px; display: inline-block; }
                @media print { .no-print { display: none; } }
                .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
            </style>
            </head>
            <body>
                <h1>Attendance History Report</h1>
                <p><strong>Generated:</strong> ${exportDate}</p>
                <p><strong>Total Records:</strong> ${dataToExport.length}</p>
                <p><strong>Filters Applied:</strong> ${filterInfo.length > 0 ? filterInfo.join(' | ') : 'None'}</p>
                
                <div class="stats">
                    <h3>Summary Statistics</h3>
                    <div class="stats-grid">
                        <div class="stat-item"><div class="stat-number">${present}</div><div>Present</div></div>
                        <div class="stat-item"><div class="stat-number">${absent}</div><div>Absent</div></div>
                        <div class="stat-item"><div class="stat-number">${late}</div><div>Late</div></div>
                        <div class="stat-item"><div class="stat-number">${halfDay}</div><div>Half Day</div></div>
                        <div class="stat-item"><div class="stat-number">${holiday}</div><div>Holiday</div></div>
                    </div>
                </div>
                
                <h2>Detailed Attendance Records</h2>
                <table>
                    <thead><tr><th>Employee</th><th>ID</th><th>Department</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Hours</th><th>Status</th></tr></thead>
                    <tbody>`;
            
            dataToExport.forEach(record => {
                htmlContent += `<tr>
                    <td>${escapeHtml(record.employeeName)}</td>
                    <td>${record.employeeId}</td>
                    <td>${record.department}</td>
                    <td>${record.date}</td>
                    <td>${record.checkInTime || '—'}</td>
                    <td>${record.checkOutTime || '—'}</td>
                    <td>${record.workingHours ? record.workingHours.toFixed(1) + 'h' : '—'}</td>
                    <td><span class="status-${record.status.replace(' ', '\\ ')}">${record.status}</span></td>
                </tr>`;
            });
            
            htmlContent += `</tbody></table>
                <div class="footer">Generated by Attendance Management System</div>
                <div class="no-print" style="text-align:center; margin-top:30px;">
                    <button onclick="window.print()" style="padding:10px 20px; background:#1e3a8a; color:white; border:none; border-radius:5px; cursor:pointer;">Save as PDF / Print</button>
                </div>
            </body></html>`;
            
            const exportWindow = window.open('', '_blank');
            if (exportWindow) {
                exportWindow.document.write(htmlContent);
                exportWindow.document.close();
            } else {
                alert('Popup blocked. Please allow popups for this site.');
            }
        }
        
        // Set view
        function setView(view) {
            currentView = view;
            if (view === 'list') {
                listView.style.display = 'block';
                calendarView.style.display = 'none';
                listViewBtn.classList.add('bg-blue-600', 'text-white', 'shadow-sm');
                listViewBtn.classList.remove('text-gray-700', 'hover:bg-gray-50');
                calendarViewBtn.classList.remove('bg-blue-600', 'text-white', 'shadow-sm');
                calendarViewBtn.classList.add('text-gray-700', 'hover:bg-gray-50');
                renderTable();
                updatePagination();
            } else {
                listView.style.display = 'none';
                calendarView.style.display = 'block';
                calendarViewBtn.classList.add('bg-blue-600', 'text-white', 'shadow-sm');
                calendarViewBtn.classList.remove('text-gray-700', 'hover:bg-gray-50');
                listViewBtn.classList.remove('bg-blue-600', 'text-white', 'shadow-sm');
                listViewBtn.classList.add('text-gray-700', 'hover:bg-gray-50');
                renderCalendar();
            }
        }
        
        // Sorting
        function initSorting() {
            document.querySelectorAll('.sort-header').forEach(header => {
                header.addEventListener('click', () => {
                    const field = header.getAttribute('data-sort');
                    if (sortField === field) {
                        sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
                    } else {
                        sortField = field;
                        sortOrder = 'asc';
                    }
                    applySorting();
                    renderTable();
                    updatePagination();
                    
                    // Update header arrows
                    document.querySelectorAll('.sort-header').forEach(h => {
                        const f = h.getAttribute('data-sort');
                        if (f === sortField) {
                            h.innerHTML = h.innerHTML.replace(/[↕↑↓]/, sortOrder === 'asc' ? '↑' : '↓');
                        } else {
                            h.innerHTML = h.innerHTML.replace(/[↑↓]/, '↕');
                        }
                    });
                });
            });
        }
        
        // Event listeners
        searchInput.addEventListener('input', () => { currentPage = 1; applyFilters(); });
        timeFilter.addEventListener('change', () => { currentPage = 1; applyFilters(); });
        statusFilter.addEventListener('change', () => { currentPage = 1; applyFilters(); });
        listViewBtn.addEventListener('click', () => setView('list'));
        calendarViewBtn.addEventListener('click', () => setView('calendar'));
        exportBtn.addEventListener('click', exportHistoryOnly);
        document.getElementById('prevMonth').addEventListener('click', prevMonth);
        document.getElementById('nextMonth').addEventListener('click', nextMonth);
        
        // Initialize
        loadData();
        initSorting();
    </script>
</body>
</html>