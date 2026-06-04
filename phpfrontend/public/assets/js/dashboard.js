document.addEventListener('alpine:init', () => {
  window.attendanceDashboard = function attendanceDashboard() {
    const basePath = window.clockItBasePath || '';

    return {
      onsiteStaff: Array.isArray(window.DASHBOARD_DATA?.onsiteStaff) ? window.DASHBOARD_DATA.onsiteStaff : [],
      recentActivity: Array.isArray(window.DASHBOARD_DATA?.recentActivity) ? window.DASHBOARD_DATA.recentActivity : [],
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
          if (!this.onsiteStaff.length) {
            this.onsiteStaff = [];
          }
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
          if (!this.recentActivity.length) {
            this.recentActivity = [];
          }
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
    leaveRequestType: 'Annual Leave',
    leaveRequestStartDate: '',
    leaveRequestEndDate: '',
    leaveRequestReason: '',
    leaveRequestErrors: {},
    leaveRequestSuccess: false,
    currentMonth: new Date().getMonth(),
    currentYear: new Date().getFullYear(),
    selectedDate: null,
    calendarDays: [],
    attendanceHistoryMap: {},
    leaveEvents: {},

    init() {
      this.loadAttendanceHistory();
      this.loadLeaveEvents();
      this.generateCalendar();
      window.addEventListener(window.scanQrConfig?.storage?.eventsUpdatedEventName || 'attendance-events-updated', () => {
        this.loadAttendanceHistory();
        this.generateCalendar();
      });
    },

    loadAttendanceHistory() {
      var history = [];

      if (typeof window !== 'undefined' && window.ATTENDANCE_DATA && Array.isArray(window.ATTENDANCE_DATA.history)) {
        history = window.ATTENDANCE_DATA.history;
      }

      this.attendanceHistoryMap = history.reduce(function (map, entry) {
        if (entry && entry.date && entry.status) {
          map[entry.date] = entry.status;
        }
        return map;
      }, {});

      try {
        var scanEvents = JSON.parse(window.localStorage.getItem(window.scanQrConfig?.storage?.attendanceEventsKey || 'attendanceEvents') || '[]');
        scanEvents.forEach(function (event) {
          if (event && event.date) {
            this.attendanceHistoryMap[event.date] = 'Present';
          }
        }, this);
      } catch (_) {
      }
    },

    parseDateKey(dateKey) {
      var parts = String(dateKey || '').split('-').map(Number);
      if (parts.length !== 3 || parts.some(function (part) { return Number.isNaN(part); })) {
        return null;
      }

      return new Date(parts[0], parts[1] - 1, parts[2]);
    },

    formatDateKey(date) {
      var month = String(date.getMonth() + 1).padStart(2, '0');
      var day = String(date.getDate()).padStart(2, '0');

      return `${date.getFullYear()}-${month}-${day}`;
    },

    loadLeaveEvents() {
      var stored = [];
      try {
        stored = JSON.parse(window.localStorage.getItem('leaveRequests') || '[]');
      } catch (_) {
        stored = [];
      }

      var map = {};
      var self = this;
      stored.forEach(function (request) {
        if (!request.startDate || !request.endDate) {
          return;
        }

        var startDate = self.parseDateKey(request.startDate);
        var endDate = self.parseDateKey(request.endDate);
        if (!startDate || !endDate) {
          return;
        }

        var status = request.status || 'Pending';
        var current = new Date(startDate);

        while (current <= endDate) {
          var dateKey = self.formatDateKey(current);
          map[dateKey] = {
            type: 'leave',
            status: status,
            label: `${status} ${request.type || 'Leave'}`,
            description: request.reason || '',
          };
          current = new Date(current);
          current.setDate(current.getDate() + 1);
        }
      });

      this.leaveEvents = map;
    },

    getEventForDay(day) {
      if (!day) {
        return null;
      }

      var date = new Date(this.currentYear, this.currentMonth, day);
      var dateKey = this.formatDateKey(date);

      if (this.leaveEvents[dateKey]) {
        return this.leaveEvents[dateKey];
      }

      if (this.attendanceHistoryMap[dateKey]) {
        return {
          type: 'attendance',
          status: this.attendanceHistoryMap[dateKey],
          label: this.attendanceHistoryMap[dateKey],
        };
      }

      var today = new Date();
      if (date > today) {
        return null;
      }

      var dayOfWeek = date.getDay();
      if (dayOfWeek === 0 || dayOfWeek === 6) {
        return { type: 'attendance', status: 'Absent', label: 'Absent' };
      }

      return { type: 'attendance', status: 'Present', label: 'Present' };
    },

    generateCalendar() {
      var firstDay = new Date(this.currentYear, this.currentMonth, 1);
      var lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
      var daysInMonth = lastDay.getDate();
      var startingDayOfWeek = firstDay.getDay();

      this.calendarDays = [];

      for (var i = 0; i < startingDayOfWeek; i += 1) {
        this.calendarDays.push(null);
      }

      for (var day = 1; day <= daysInMonth; day += 1) {
        var date = new Date(this.currentYear, this.currentMonth, day);
        var event = this.getEventForDay(day);

        this.calendarDays.push({
          day: day,
          dateKey: this.formatDateKey(date),
          isToday: date.toDateString() === new Date().toDateString(),
          event: event,
        });
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

    openCalendar() {
      this.selectedDate = null;
      this.showCalendar = true;
    },

    isToday(day) {
      const today = new Date();
      return (
        day &&
        day === today.getDate() &&
        this.currentMonth === today.getMonth() &&
        this.currentYear === today.getFullYear()
      );
    },

    isSelected(day) {
      return (
        this.selectedDate &&
        day === this.selectedDate.getDate() &&
        this.currentMonth === this.selectedDate.getMonth() &&
        this.currentYear === this.selectedDate.getFullYear()
      );
    },

    getMonthYear() {
      const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
      ];
      return `${months[this.currentMonth]} ${this.currentYear}`;
    },

    resetLeaveForm() {
      this.leaveRequestType = 'Annual Leave';
      this.leaveRequestStartDate = '';
      this.leaveRequestEndDate = '';
      this.leaveRequestReason = '';
      this.leaveRequestErrors = {};
      this.leaveRequestSuccess = false;
    },

    openLeaveRequest() {
      this.resetLeaveForm();
      this.showLeave = true;
    },

    validateLeaveRequest() {
      var errors = {};

      if (!this.leaveRequestType) {
        errors.type = 'Please select a leave type.';
      }
      if (!this.leaveRequestStartDate) {
        errors.startDate = 'Start date is required.';
      }
      if (!this.leaveRequestEndDate) {
        errors.endDate = 'End date is required.';
      } else if (this.leaveRequestStartDate && this.leaveRequestEndDate < this.leaveRequestStartDate) {
        errors.endDate = 'End date must be on or after the start date.';
      }
      if (!this.leaveRequestReason || this.leaveRequestReason.trim() === '') {
        errors.reason = 'Reason is required.';
      }

      return errors;
    },

    saveLeaveRequest() {
      var request = {
        id: Date.now(),
        type: this.leaveRequestType,
        startDate: this.leaveRequestStartDate,
        endDate: this.leaveRequestEndDate,
        reason: this.leaveRequestReason,
        status: 'Pending',
        submittedAt: new Date().toISOString(),
      };

      var existing = [];
      try {
        existing = JSON.parse(window.localStorage.getItem('leaveRequests') || '[]');
      } catch (_) {
        existing = [];
      }

      existing.push(request);
      window.localStorage.setItem('leaveRequests', JSON.stringify(existing));
      this.loadLeaveEvents();
      return request;
    },

    submitLeaveRequest() {
      this.leaveRequestErrors = this.validateLeaveRequest();
      if (Object.keys(this.leaveRequestErrors).length > 0) {
        return;
      }

      this.saveLeaveRequest();
      this.leaveRequestSuccess = true;
      this.generateCalendar();

      var self = this;
      setTimeout(function () {
        self.showLeave = false;
        self.resetLeaveForm();
      }, 1400);
    },
  };
}

document.addEventListener('DOMContentLoaded', function () {
  if (window.Alpine) {
    return;
  }

  var staffModals = Array.prototype.slice.call(document.querySelectorAll('[data-staff-modal]'));

  function closeStaffModals() {
    staffModals.forEach(function (modal) {
      modal.style.display = 'none';
      modal.setAttribute('x-cloak', '');
    });
  }

  function openStaffModal(name) {
    var modal = document.querySelector('[data-staff-modal="' + name + '"]');
    if (!modal) {
      return;
    }

    modal.removeAttribute('x-cloak');
    modal.style.display = 'flex';
  }

  closeStaffModals();

  document.querySelectorAll('[data-open-staff-modal]').forEach(function (button) {
    button.addEventListener('click', function () {
      openStaffModal(button.getAttribute('data-open-staff-modal'));
    });
  });

  staffModals.forEach(function (modal) {
    modal.addEventListener('click', function (event) {
      if (
        event.target === modal ||
        event.target.closest('.btn-close') ||
        event.target.closest('.modal-back-button')
      ) {
        closeStaffModals();
      }
    });
  });

  var leaveForm = document.querySelector('[data-staff-modal="leave"] form');
  leaveForm?.addEventListener('submit', function (event) {
    event.preventDefault();
    var success = leaveForm.querySelector('.alert-success');
    if (success) {
      var successWrap = success.closest('[x-show]');
      if (successWrap) {
        successWrap.removeAttribute('x-cloak');
        successWrap.style.display = 'block';
      }
    }
    window.setTimeout(closeStaffModals, 1400);
  });
});



