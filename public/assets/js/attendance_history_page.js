(function () {
  'use strict';

  function attendanceHistory() {
    return {
      allData: [],
      filteredData: [],
      pageRows: [],
      viewMode: localStorage.getItem('attendanceHistoryView') || 'list',
      searchTerm: '',
      statusFilter: 'All',
      timeFilter: 'all',
      sortField: 'date',
      sortDirection: 'desc',
      currentPage: 1,
      perPage: 10,
      totalPages: 1,
      calendarDate: new Date(),
      calendarDays: [],
      currentEmployeeId: null,
      columns: [
        { field: 'date', label: 'Date' },
        { field: 'checkInTime', label: 'Check In' },
        { field: 'checkOutTime', label: 'Check Out' },
        { field: 'workingHours', label: 'Hours' },
        { field: 'status', label: 'Status' }
      ],

      init: async function () {
        this.currentEmployeeId = window.ATTENDANCE_DATA.currentEmployeeId || null;

        if (!this.currentEmployeeId) {
          this.allData = [];
        } else {
          var allHistory = window.ATTENDANCE_DATA.history || [];
          var apiHistory = await this.fetchAttendanceHistory();
          this.allData = (apiHistory || allHistory).filter(function(record) {
            return record.employeeId === this.currentEmployeeId;
          }.bind(this));
        }

        this.applyFilters();
        this.$watch('viewMode', function (value) {
          localStorage.setItem('attendanceHistoryView', value);
        });
      },

      fetchAttendanceHistory: async function () {
        if (!window.ATTENDANCE_DATA.apiUrl) {
          return null;
        }

        try {
          var response = await fetch(window.ATTENDANCE_DATA.apiUrl);
          if (!response.ok) {
            return null;
          }
          var records = await response.json();
          return Array.isArray(records) ? records : null;
        } catch (_) {
          return null;
        }
      },

      applyFilters: function () {
        var term = this.searchTerm.toLowerCase().trim();
        var now = new Date();
        var weekStart = this.startOfWeek(now);

        this.filteredData = this.allData.filter(function (record) {
          var recordDate = new Date(record.date + 'T00:00:00');
          var matchesSearch = !term || [
            record.date,
            record.status
          ].some(function (value) {
            return String(value || '').toLowerCase().includes(term);
          });
          var matchesStatus = this.statusFilter === 'All' || record.status === this.statusFilter;
          var matchesTime = this.timeFilter === 'all' ||
            (this.timeFilter === 'month' &&
              recordDate.getMonth() === now.getMonth() &&
              recordDate.getFullYear() === now.getFullYear()) ||
            (this.timeFilter === 'week' &&
              recordDate >= weekStart &&
              recordDate < new Date(weekStart.getFullYear(), weekStart.getMonth(), weekStart.getDate() + 7));

          return matchesSearch && matchesStatus && matchesTime;
        }, this);

        this.currentPage = 1;
        this.sortRows();
        this.refreshPage();
        this.buildCalendar();
      },

      sortBy: function (field) {
        if (this.sortField === field) {
          this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
          this.sortField = field;
          this.sortDirection = 'asc';
        }
        this.sortRows();
        this.refreshPage();
      },

      sortRows: function () {
        var field = this.sortField;
        var direction = this.sortDirection === 'asc' ? 1 : -1;
        this.filteredData.sort(function (left, right) {
          var a = left[field];
          var b = right[field];
          if (field === 'date') {
            a = new Date(a + 'T00:00:00').getTime();
            b = new Date(b + 'T00:00:00').getTime();
          }
          if (typeof a === 'string') {
            a = a.toLowerCase();
            b = b.toLowerCase();
          }
          return a < b ? -direction : a > b ? direction : 0;
        });
      },

      refreshPage: function () {
        this.totalPages = 1;
        this.currentPage = 1;
        this.pageRows = this.filteredData.slice();
      },

      setPage: function (page) {
        this.currentPage = Math.min(Math.max(page, 1), this.totalPages);
        this.refreshPage();
      },

      changeMonth: function (offset) {
        this.calendarDate = new Date(this.calendarDate.getFullYear(), this.calendarDate.getMonth() + offset, 1);
        this.buildCalendar();
      },

      buildCalendar: function () {
        var year = this.calendarDate.getFullYear();
        var month = this.calendarDate.getMonth();
        var firstWeekday = new Date(year, month, 1).getDay();
        var lastDate = new Date(year, month + 1, 0).getDate();
        var days = [];

        for (var blank = 0; blank < firstWeekday; blank += 1) {
          days.push({ date: null, number: '', records: [] });
        }
        for (var number = 1; number <= lastDate; number += 1) {
          var date = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(number).padStart(2, '0');
          days.push({
            date: date,
            number: number,
            records: this.filteredData.filter(function (record) { return record.date === date; })
          });
        }
        this.calendarDays = days;
      },

      exportReport: function () {
        if (!this.filteredData.length) {
          window.alert('No attendance history matches the current filters.');
          return;
        }
        var rows = this.filteredData.map(function (record) {
          return [
            record.employeeName,
            record.employeeId,
            record.department,
            record.date,
            record.checkInTime,
            record.checkOutTime,
            record.workingHours,
            record.status
          ];
        });
        var csv = [['Employee name', 'Employee ID', 'Department', 'Date', 'Check in', 'Check out', 'Working hours', 'Status']]
          .concat(rows)
          .map(function (row) {
            return row.map(function (value) {
              return '"' + String(value).replace(/"/g, '""') + '"';
            }).join(',');
          })
          .join('\n');
        var link = document.createElement('a');
        link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
        link.download = 'attendance_history_' + new Date().toISOString().slice(0, 10) + '.csv';
        link.click();
        URL.revokeObjectURL(link.href);
      },

      sortIcon: function (field) {
        if (field !== this.sortField) return '';
        return this.sortDirection === 'asc' ? '\u2191' : '\u2193';
      },

      formatDate: function (date) {
        return new Date(date + 'T00:00:00').toLocaleDateString(undefined, {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      },

      formatHours: function (hours) {
        var value = Number(hours);
        return value > 0 ? value.toFixed(1) + 'h' : '--';
      },

      startOfWeek: function (date) {
        var result = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        result.setDate(result.getDate() - result.getDay());
        return result;
      },

      statusClass: function (status) {
        return 'status-' + status.toLowerCase().replace(/\s+/g, '-');
      },

      get calendarTitle() {
        return this.calendarDate.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
      },

      get employeeName() {
        var user = window.ATTENDANCE_DATA.currentUser || {};
        return (this.allData[0] && this.allData[0].employeeName) || user.name || 'Staff Member';
      },

      get employeeDepartment() {
        var user = window.ATTENDANCE_DATA.currentUser || {};
        return (this.allData[0] && this.allData[0].department)
          ? this.allData[0].department + ' Department'
          : (user.department ? user.department + ' Department' : 'Department');
      },

      get employeeInitials() {
        return this.employeeName
          .split(/\s+/)
          .filter(Boolean)
          .slice(0, 2)
          .map(function (part) { return part.charAt(0).toUpperCase(); })
          .join('') || 'SM';
      },

      get metrics() {
        var hours = this.filteredData.filter(function (record) { return Number(record.workingHours) > 0; });
        var average = hours.length
          ? hours.reduce(function (total, record) { return total + Number(record.workingHours); }, 0) / hours.length
          : 0;
        var total = this.filteredData.length || 0;
        var percent = function (count) {
          return total ? Math.round((count / total) * 100) + '%' : '0%';
        };
        var present = this.filteredData.filter(function (record) { return record.status === 'Present'; }).length;
        var late = this.filteredData.filter(function (record) { return record.status === 'Late'; }).length;
        var absent = this.filteredData.filter(function (record) { return record.status === 'Absent'; }).length;

        return [
          { label: 'Total Days', value: total, detail: '', className: 'metric-total' },
          { label: 'Present', value: present, detail: percent(present), className: 'metric-present' },
          { label: 'Late', value: late, detail: percent(late), className: 'metric-late' },
          { label: 'Absent', value: absent, detail: percent(absent), className: 'metric-absent' },
          { label: 'Avg Hours', value: average.toFixed(1), detail: '', className: 'metric-hours' }
        ];
      }
    };
  }

  window.attendanceHistory = attendanceHistory;
})();
