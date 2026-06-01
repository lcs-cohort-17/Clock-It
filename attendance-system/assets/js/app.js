// Global attendance application component
function attendanceApp() {
    return {
        // Data
        allData: [],
        filteredData: [],
        paginatedData: [],
        
        // UI State
        viewMode: localStorage.getItem('attendanceViewMode') || 'list',
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
            avgHours: 0
        },
        
        // Initialize component
        async init() {
            await this.loadData();
            this.applyFilters();
            
            // Save view mode preference
            this.$watch('viewMode', (value) => {
                localStorage.setItem('attendanceViewMode', value);
            });
        },
        
        // Load data from API
        async loadData() {
            try {
                // Show loading state
                if (this.$el) this.$el.classList.add('opacity-50');
                
                const response = await fetch('api/attendance.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                this.allData = await response.json();
                this.filteredData = [...this.allData];
                this.updateCalendar();
                
                // Log successful load
                console.log(`Loaded ${this.allData.length} attendance records`);
            } catch (error) {
                console.error('Error loading attendance data:', error);
                
                // Show user-friendly error message
                this.showNotification('Failed to load attendance data. Please refresh the page.', 'error');
                
                // Fallback to empty data
                this.allData = [];
                this.filteredData = [];
            } finally {
                if (this.$el) this.$el.classList.remove('opacity-50');
            }
        },
        
        // Apply all filters
        applyFilters() {
            let filtered = [...this.allData];
            
            // Apply status filter
            if (this.statusFilter !== 'All') {
                filtered = filtered.filter(record => record.status === this.statusFilter);
            }
            
            // Apply time filter
            if (this.timeFilter !== 'all') {
                filtered = this.filterByTimePeriod(filtered);
            }
            
            // Apply search filter
            if (this.searchTerm.trim()) {
                filtered = this.filterBySearchTerm(filtered);
            }
            
            this.filteredData = filtered;
            this.applySorting();
            this.updateSummary();
            this.updatePagination();
            this.updateCalendar();
            
            // Reset to first page when filters change
            this.currentPage = 1;
        },
        
        // Filter by time period
        filterByTimePeriod(data) {
            const now = new Date();
            const currentMonth = now.getMonth();
            const currentYear = now.getFullYear();
            const currentWeekStart = this.getWeekStart(now);
            
            return data.filter(record => {
                const recordDate = new Date(record.date);
                if (this.timeFilter === 'month') {
                    return recordDate.getMonth() === currentMonth && 
                           recordDate.getFullYear() === currentYear;
                } else if (this.timeFilter === 'week') {
                    const recordWeekStart = this.getWeekStart(recordDate);
                    return recordWeekStart.getTime() === currentWeekStart.getTime();
                }
                return true;
            });
        },
        
        // Filter by search term
        filterBySearchTerm(data) {
            const term = this.searchTerm.toLowerCase();
            return data.filter(record => 
                record.employeeName.toLowerCase().includes(term) ||
                record.employeeId.toLowerCase().includes(term) ||
                record.department.toLowerCase().includes(term)
            );
        },
        
        // Apply sorting to filtered data
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
        },
        
        // Sort by field
        sortBy(field) {
            if (this.sortField === field) {
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortOrder = 'asc';
            }
            this.applySorting();
            this.updatePagination();
        },
        
        // Get sort icon
        getSortIcon(field) {
            if (this.sortField !== field) return '↕';
            return this.sortOrder === 'asc' ? '↑' : '↓';
        },
        
        // Update pagination
        updatePagination() {
            this.totalPages = Math.ceil(this.filteredData.length / this.itemsPerPage);
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            this.paginatedData = this.filteredData.slice(start, end);
        },
        
        // Update summary statistics
        updateSummary() {
            const total = this.filteredData.length;
            const present = this.filteredData.filter(r => r.status === 'Present').length;
            const late = this.filteredData.filter(r => r.status === 'Late').length;
            const absent = this.filteredData.filter(r => r.status === 'Absent').length;
            
            const recordsWithHours = this.filteredData.filter(r => r.workingHours > 0);
            const avgHours = recordsWithHours.length > 0 
                ? recordsWithHours.reduce((sum, r) => sum + r.workingHours, 0) / recordsWithHours.length
                : 0;
            
            this.summary = { 
                total, 
                present, 
                late, 
                absent, 
                avgHours: avgHours.toFixed(1) 
            };
        },
        
        // Calendar methods
        updateCalendar() {
            const year = this.currentCalendarDate.getFullYear();
            const month = this.currentCalendarDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startingDayOfWeek = firstDay.getDay();
            
            const days = [];
            const currentMonthRecords = this.filteredData.filter(record => {
                const recordDate = new Date(record.date);
                return recordDate.getMonth() === month && recordDate.getFullYear() === year;
            });
            
            // Add empty days for previous month
            for (let i = 0; i < startingDayOfWeek; i++) {
                days.push({ date: null, dayNumber: '', records: [] });
            }
            
            // Add current month days
            for (let d = 1; d <= lastDay.getDate(); d++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                const dayRecords = currentMonthRecords.filter(record => record.date === dateStr);
                days.push({
                    date: dateStr,
                    dayNumber: d,
                    records: dayRecords
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
            return this.currentCalendarDate.toLocaleString('default', { 
                month: 'long', 
                year: 'numeric' 
            });
        },
        
        // Helper methods
        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        },
        
        getWeekStart(date) {
            const d = new Date(date);
            const day = d.getDay();
            const diff = d.getDate() - day + (day === 0 ? -6 : 1);
            return new Date(d.setDate(diff));
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
        },
        
        // Export to CSV
        exportToCSV() {
            if (this.filteredData.length === 0) {
                this.showNotification('No data to export', 'warning');
                return;
            }
            
            const headers = ['Employee Name', 'Employee ID', 'Date', 'Check In', 'Check Out', 'Status', 'Working Hours', 'Department'];
            const csvRows = [headers];
            
            for (const record of this.filteredData) {
                csvRows.push([
                    record.employeeName,
                    record.employeeId,
                    record.date,
                    record.checkInTime || '—',
                    record.checkOutTime || '—',
                    record.status,
                    record.workingHours || 0,
                    record.department
                ]);
            }
            
            const csvContent = csvRows.map(row => row.join(',')).join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `attendance_export_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            this.showNotification('Export completed successfully!', 'success');
        },
        
        // Export only filtered history
        exportHistoryOnly() {
            console.log('Export button clicked'); // Debug log
            
            // Get filtered data based on current filters
            let dataToExport = [...this.filteredData];
            
            // Make sure we only export attendance records
            const validStatuses = ['Present', 'Absent', 'Late', 'Half Day', 'Holiday'];
            dataToExport = dataToExport.filter(record => validStatuses.includes(record.status));
            
            if (dataToExport.length === 0) {
                this.showNotification('No attendance history to export', 'warning');
                return;
            }
            
            // Create HTML content for export
            const exportDate = new Date().toLocaleString();
            const filterInfo = [];
            if (this.statusFilter !== 'All') filterInfo.push(`Status: ${this.statusFilter}`);
            if (this.timeFilter !== 'all') filterInfo.push(`Period: ${this.timeFilter === 'week' ? 'This Week' : 'This Month'}`);
            if (this.searchTerm) filterInfo.push(`Search: "${this.searchTerm}"`);
            
            let htmlContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Attendance History Report - ${exportDate}</title>
                    <style>
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        body {
                            font-family: 'Segoe UI', Arial, sans-serif;
                            margin: 40px;
                            color: #333;
                            background: white;
                        }
                        .report-container {
                            max-width: 1200px;
                            margin: 0 auto;
                        }
                        h1 {
                            color: #1e3a8a;
                            border-bottom: 3px solid #1e3a8a;
                            padding-bottom: 10px;
                            margin-bottom: 20px;
                        }
                        .header-info {
                            margin: 20px 0;
                            padding: 15px;
                            background: #f3f4f6;
                            border-radius: 8px;
                            line-height: 1.6;
                        }
                        .stats-summary {
                            margin: 20px 0;
                            padding: 15px;
                            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
                            border-radius: 10px;
                            color: white;
                        }
                        .stats-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                            gap: 15px;
                            margin-top: 15px;
                        }
                        .stat-item {
                            text-align: center;
                            padding: 10px;
                            background: rgba(255,255,255,0.2);
                            border-radius: 8px;
                        }
                        .stat-number {
                            font-size: 28px;
                            font-weight: bold;
                        }
                        .stat-label {
                            font-size: 12px;
                            margin-top: 5px;
                            opacity: 0.9;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin: 20px 0;
                            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                        }
                        th, td {
                            border: 1px solid #ddd;
                            padding: 12px;
                            text-align: left;
                        }
                        th {
                            background-color: #1e3a8a;
                            color: white;
                            font-weight: bold;
                            position: sticky;
                            top: 0;
                        }
                        tr:nth-child(even) {
                            background-color: #f9fafb;
                        }
                        tr:hover {
                            background-color: #f3f4f6;
                        }
                        .status-badge {
                            display: inline-block;
                            padding: 4px 12px;
                            border-radius: 20px;
                            font-size: 12px;
                            font-weight: bold;
                        }
                        .status-Present {
                            background: #10b981;
                            color: white;
                        }
                        .status-Absent {
                            background: #ef4444;
                            color: white;
                        }
                        .status-Late {
                            background: #f59e0b;
                            color: white;
                        }
                        .status-Half\\ Day {
                            background: #6366f1;
                            color: white;
                        }
                        .status-Holiday {
                            background: #8b5cf6;
                            color: white;
                        }
                        .footer {
                            margin-top: 40px;
                            text-align: center;
                            font-size: 12px;
                            color: #6b7280;
                            border-top: 1px solid #e5e7eb;
                            padding-top: 20px;
                        }
                        .print-button {
                            display: inline-block;
                            margin: 10px;
                            padding: 12px 24px;
                            background: #1e3a8a;
                            color: white;
                            border: none;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 14px;
                            font-weight: bold;
                        }
                        .print-button:hover {
                            background: #1e40af;
                        }
                        @media print {
                            body {
                                margin: 0;
                                padding: 20px;
                            }
                            .no-print {
                                display: none;
                            }
                            th {
                                background-color: #1e3a8a !important;
                                color: white !important;
                                print-color-adjust: exact;
                            }
                            .status-badge {
                                print-color-adjust: exact;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="report-container">
                        <h1>Attendance History Report</h1>
                        
                        <div class="header-info">
                            <strong>Generated:</strong> ${exportDate}<br>
                            <strong>Total Records:</strong> ${dataToExport.length}<br>
                            <strong>Filters Applied:</strong> ${filterInfo.length > 0 ? filterInfo.join(' | ') : 'None (All Records)'}
                        </div>
            `;
            
            // Calculate statistics
            const present = dataToExport.filter(r => r.status === 'Present').length;
            const absent = dataToExport.filter(r => r.status === 'Absent').length;
            const late = dataToExport.filter(r => r.status === 'Late').length;
            const halfDay = dataToExport.filter(r => r.status === 'Half Day').length;
            const holiday = dataToExport.filter(r => r.status === 'Holiday').length;
            
            htmlContent += `
                        <div class="stats-summary">
                            <h3 style="margin-bottom: 15px;">Summary Statistics</h3>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-number">${present}</div>
                                    <div class="stat-label">Present</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">${absent}</div>
                                    <div class="stat-label">Absent</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">${late}</div>
                                    <div class="stat-label">Late</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">${halfDay}</div>
                                    <div class="stat-label">Half Day</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">${holiday}</div>
                                    <div class="stat-label">Holiday</div>
                                </div>
                            </div>
                        </div>
                        
                        <h2>Detailed Attendance Records</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Employee Name</th>
                                    <th>Employee ID</th>
                                    <th>Department</th>
                                    <th>Date</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Working Hours</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            // Sort by date (most recent first)
            const sortedData = [...dataToExport].sort((a, b) => new Date(b.date) - new Date(a.date));
            
            sortedData.forEach(record => {
                const statusClass = `status-${record.status.replace(' ', '\\ ')}`;
                htmlContent += `
                    <tr>
                        <td><strong>${this.escapeHtml(record.employeeName)}</strong></td>
                        <td>${record.employeeId}</td>
                        <td>${record.department}</td>
                        <td>${record.date}</td>
                        <td>${record.checkInTime || '—'}</td>
                        <td>${record.checkOutTime || '—'}</td>
                        <td>${record.workingHours ? record.workingHours.toFixed(1) + 'h' : '—'}</td>
                        <td><span class="status-badge ${statusClass}">${record.status}</span></td>
                    </tr>
                `;
            });
            
            htmlContent += `
                            </tbody>
                        </table>
                        
                        <div class="footer">
                            <p>Generated by Attendance Management System</p>
                            <p>Report includes all attendance records (Present, Absent, Late, Half Day, Holiday)</p>
                            <p>${exportDate}</p>
                        </div>
                        
                        <div class="no-print" style="text-align: center; margin-top: 30px;">
                            <button onclick="window.print()" class="print-button">
                                Save as PDF / Print
                            </button>
                            <button onclick="copyToClipboard()" class="print-button" style="background: #6b7280;">
                                Copy to Google Docs
                            </button>
                        </div>
                    </div>
                    
                    <script>
                        function copyToClipboard() {
                            const htmlContent = document.body.innerHTML;
                            const blob = new Blob([htmlContent], {type: 'text/html'});
                            const item = new ClipboardItem({'text/html': blob});
                            navigator.clipboard.write([item]);
                            alert('Report copied! You can now paste it into Google Docs.');
                        }
                    <\/script>
                </body>
                </html>
            `;
            
            // Open in new window
            const exportWindow = window.open('', '_blank');
            if (exportWindow) {
                exportWindow.document.write(htmlContent);
                exportWindow.document.close();
                this.showNotification(`Exporting ${dataToExport.length} attendance records... Use Print to save as PDF`, 'success');
            } else {
                this.showNotification('Popup blocked. Please allow popups for this site.', 'error');
            }
        },
        
        // Print current view
        printView() {
            const printContent = this.viewMode === 'list' 
                ? this.getPrintableTableHTML()
                : this.getPrintableCalendarHTML();
            
            const printWindow = window.open('', '_blank');
            if (printWindow) {
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Attendance Report</title>
                            <script src="https://cdn.tailwindcss.com"><\/script>
                            <style>
                                body { padding: 20px; }
                                table { width: 100%; border-collapse: collapse; }
                                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                                th { background-color: #f2f2f2; }
                                @media print {
                                    .no-print { display: none; }
                                }
                            </style>
                        </head>
                        <body>
                            ${printContent}
                            <script>window.print();<\/script>
                        </body>
                    </html>
                `);
                printWindow.document.close();
            }
        },
        
        getPrintableTableHTML() {
            let html = '<h1 class="text-2xl font-bold mb-4">Attendance Report</h1>';
            html += `<p class="mb-4">Generated: ${new Date().toLocaleString()}</p>`;
            html += '<table class="min-w-full">';
            html += '<thead><tr>';
            html += '<th>Employee</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Hours</th>';
            html += '</tr></thead><tbody>';
            
            for (const record of this.filteredData) {
                html += '<tr>';
                html += `<td>${record.employeeName}</td>`;
                html += `<td>${record.date}</td>`;
                html += `<td>${record.checkInTime || '—'}</td>`;
                html += `<td>${record.checkOutTime || '—'}</td>`;
                html += `<td>${record.status}</td>`;
                html += `<td>${record.workingHours || 0}h</td>`;
                html += '</tr>';
            }
            
            html += '</tbody></table>';
            return html;
        },
        
        getPrintableCalendarHTML() {
            let html = '<h1 class="text-2xl font-bold mb-4">Attendance Calendar</h1>';
            html += `<p class="mb-4">${this.currentMonthName}</p>`;
            html += '<div class="grid grid-cols-7 gap-2">';
            
            // Day headers
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            for (const day of days) {
                html += `<div class="font-bold p-2">${day}</div>`;
            }
            
            // Calendar days
            for (const day of this.calendarDays) {
                if (!day.date) {
                    html += '<div class="p-2 bg-gray-100 min-h-[100px]"></div>';
                } else {
                    html += `<div class="p-2 border min-h-[100px]">
                                <div class="font-bold">${day.dayNumber}</div>`;
                    for (const record of day.records) {
                        html += `<div class="text-xs mt-1">${record.employeeName.split(' ')[0]}: ${record.status}</div>`;
                    }
                    html += '</div>';
                }
            }
            
            html += '</div>';
            return html;
        },
        
        // Helper function to escape HTML
        escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        // Get status color
        getStatusColor(status) {
            const colors = {
                'Present': '#10b981',
                'Absent': '#ef4444', 
                'Late': '#f59e0b',
                'Half Day': '#6366f1',
                'Holiday': '#8b5cf6'
            };
            return colors[status] || '#6b7280';
        },
        
        // Notification system
        showNotification(message, type = 'info') {
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };
            
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Animate out and remove
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
    }
}

// Dashboard component
function dashboardApp() {
    return {
        stats: {
            totalEmployees: 0,
            presentToday: 0,
            lateToday: 0,
            absentToday: 0
        },
        recentRecords: [],
        
        async init() {
            await this.loadData();
        },
        
        async loadData() {
            try {
                const response = await fetch('api/attendance.php?action=dashboard');
                const data = await response.json();
                this.stats = data.stats;
                this.recentRecords = data.recentRecords;
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        },
        
        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString();
        },
        
        getStatusClass(status) {
            const classes = {
                'Present': 'bg-green-100 text-green-800',
                'Absent': 'bg-red-100 text-red-800',
                'Late': 'bg-yellow-100 text-yellow-800',
                'Half Day': 'bg-blue-100 text-blue-800',
                'Holiday': 'bg-purple-100 text-purple-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        }
    }
}

// Export functionality for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { attendanceApp, dashboardApp };
}