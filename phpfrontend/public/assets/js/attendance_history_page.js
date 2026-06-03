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
      columns: [
        { field: 'employeeName', label: 'Employee' },
        { field: 'department', label: 'Department' },
        { field: 'date', label: 'Date' },
        { field: 'checkInTime', label: 'Check in' },
        { field: 'checkOutTime', label: 'Check out' },
        { field: 'workingHours', label: 'Hours' },
        { field: 'status', label: 'Status' }
      ],

      init: function () {
        this.allData = (window.ATTENDANCE_DATA.history || []).slice();
        this.applyFilters();
        this.$watch('viewMode', function (value) {
          localStorage.setItem('attendanceHistoryView', value);
        });
      },

      applyFilters: function () {
        var term = this.searchTerm.toLowerCase().trim();
        var now = new Date();
        var weekStart = this.startOfWeek(now);

        this.filteredData = this.allData.filter(function (record) {
          var recordDate = new Date(record.date + 'T00:00:00');
          var matchesSearch = !term || [
            record.employeeName,
            record.employeeId,
            record.department
          ].some(function (value) {
            return value.toLowerCase().includes(term);
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
        this.totalPages = Math.max(1, Math.ceil(this.filteredData.length / this.perPage));
        this.currentPage = Math.min(this.currentPage, this.totalPages);
        var start = (this.currentPage - 1) * this.perPage;
        this.pageRows = this.filteredData.slice(start, start + this.perPage);
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

      get metrics() {
        var hours = this.filteredData.filter(function (record) { return Number(record.workingHours) > 0; });
        var average = hours.length
          ? hours.reduce(function (total, record) { return total + Number(record.workingHours); }, 0) / hours.length
          : 0;
        return [
          { label: 'Total records', value: this.filteredData.length },
          { label: 'Present', value: this.filteredData.filter(function (record) { return record.status === 'Present'; }).length },
          { label: 'Late', value: this.filteredData.filter(function (record) { return record.status === 'Late'; }).length },
          { label: 'Absent', value: this.filteredData.filter(function (record) { return record.status === 'Absent'; }).length },
          { label: 'Avg hours', value: average.toFixed(1) + 'h' }
        ];
      }
    };
  }

  window.attendanceHistory = attendanceHistory;
})();
