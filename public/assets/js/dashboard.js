document.addEventListener('alpine:init', () => {
  window.attendanceDashboard = function attendanceDashboard() {
    const basePath = window.clockItBasePath || '';

    return {
      onsiteStaff: [],
      recentActivity: [],
      initialLoading: true,
      refreshing: false,
      initialized: false,
      error: null,
      pollTimer: null,
      initials(name) {
        return String(name || '')
          .trim()
          .split(/\s+/)
          .filter(Boolean)
          .map((part) => part[0])
          .join('')
          .slice(0, 2)
          .toUpperCase();
      },
      normalizePayload(payload) {
        return Array.isArray(payload) ? payload : payload?.data || [];
      },
      async fetchOnsiteStaff() {
        try {
          this.error = null;
          const response = await fetch(`${basePath}/api/onsite.php`, { headers: { Accept: 'application/json' } });

          if (!response.ok) {
            throw new Error('Unable to fetch onsite staff.');
          }

          this.onsiteStaff = this.normalizePayload(await response.json());
        } catch (error) {
          this.error = 'Unable to load onsite staff right now.';
          this.onsiteStaff = [];
        }
      },
      async fetchRecentActivity() {
        try {
          this.error = null;
          const response = await fetch(`${basePath}/api/activity.php`, { headers: { Accept: 'application/json' } });

          if (!response.ok) {
            throw new Error('Unable to fetch recent activity.');
          }

          this.recentActivity = this.normalizePayload(await response.json()).slice(0, 10);
        } catch (error) {
          this.error = 'Unable to load recent activity right now.';
          this.recentActivity = [];
        }
      },
      async refresh() {
        if (this.refreshing) return;

        this.refreshing = true;
        try {
          await Promise.all([this.fetchOnsiteStaff(), this.fetchRecentActivity()]);
        } finally {
          this.initialLoading = false;
          this.refreshing = false;
        }
      },
      exportOnsiteCSV() {
        if (!this.onsiteStaff.length) {
          window.alert('No onsite staff to export.');
          return;
        }

        const rows = [['Name', 'Role', 'Signed in']].concat(
          this.onsiteStaff.map((staff) => [
            staff.name,
            staff.role,
            staff.signed_in_at || staff.signedInAt || '',
          ]),
        );
        const csv = rows
          .map((row) => row.map((value) => `"${String(value).replace(/"/g, '""')}"`).join(','))
          .join('\n');
        const link = document.createElement('a');
        link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
        link.download = `attendance_export_${new Date().toISOString().slice(0, 10)}.csv`;
        link.click();
        URL.revokeObjectURL(link.href);
      },
      connectSheets() {
        window.alert('Google Sheets connection is coming soon.');
      },
      init() {
        if (this.initialized) return;

        this.initialized = true;
        this.refresh();
        this.pollTimer = setInterval(() => this.refresh(), 60000);
      },
    };
  };
});

function dashboard() {
  return {
    showCalendar: false,
    showLeave: false,
    currentMonth: new Date().getMonth(),
    currentYear: new Date().getFullYear(),
    selectedDate: null,
    calendarDays: [],

    init() {
      this.generateCalendar();
    },

    generateCalendar() {
      const firstDay = new Date(this.currentYear, this.currentMonth, 1);
      const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
      const daysInMonth = lastDay.getDate();
      const startingDayOfWeek = firstDay.getDay();

      this.calendarDays = [];

      for (let i = 0; i < startingDayOfWeek; i += 1) {
        this.calendarDays.push(null);
      }

      for (let day = 1; day <= daysInMonth; day += 1) {
        this.calendarDays.push(day);
      }
    },

    previousMonth() {
      if (this.currentMonth === 0) {
        this.currentMonth = 11;
        this.currentYear -= 1;
      } else {
        this.currentMonth -= 1;
      }
      this.generateCalendar();
    },

    nextMonth() {
      if (this.currentMonth === 11) {
        this.currentMonth = 0;
        this.currentYear += 1;
      } else {
        this.currentMonth += 1;
      }
      this.generateCalendar();
    },

    selectDate(day) {
      if (day) {
        this.selectedDate = new Date(this.currentYear, this.currentMonth, day);
      }
    },

    isToday(day) {
      const today = new Date();
      return day
        && day === today.getDate()
        && this.currentMonth === today.getMonth()
        && this.currentYear === today.getFullYear();
    },

    isSelected(day) {
      return this.selectedDate
        && day === this.selectedDate.getDate()
        && this.currentMonth === this.selectedDate.getMonth()
        && this.currentYear === this.selectedDate.getFullYear();
    },

    getMonthYear() {
      const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
      ];
      return `${months[this.currentMonth]} ${this.currentYear}`;
    },
  };
}



