/**
 * attendance_log_page.js
 *
 * Alpine.js component factory for the Attendance Logs page.
 *
 * WHY A SEPARATE FILE:
 *   The component logic must be testable in Jest (Node), which means it cannot
 *   live as an inline <script> inside the PHP template. Extracting it here lets
 *   Jest import it via require() while Alpine picks it up via window.attendancePage.
 *
 * DEPENDENCY INJECTION:
 *   attendancePage(opts) accepts optional overrides for every external dependency.
 *   In the browser, opts is omitted and the function falls back to window globals.
 *   In tests, opts injects mock data, mock storage, and the real utils module —
 *   no DOM, no window, no Alpine required.
 *
 *   opts = {
 *     data:    { clockEvents: [], auditTrail: [] }  // replaces window.ATTENDANCE_DATA
 *     storage: StorageLike                          // replaces window.localStorage
 *     utils:   AttendanceUtils                      // replaces window.AttendanceUtils
 *   }
 *
 * EXPORT CSV — INJECTABLE DOWNLOAD:
 *   exportAuditCSV(fn?) accepts an optional download function.
 *   Alpine calls it with no args → real DOM download fires.
 *   Tests pass a jest.fn() → no Blob/URL/document needed.
 */
(function () {
  'use strict';

  // ── Real DOM download (browser only) ────────────────────────
  function triggerDownload(csv, filename) {
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href     = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  function formatClockEventsCSV(events) {
    var headers = ['Staff Name', 'Event Type', 'Timestamp', 'Device', 'Location', 'Sync Status'];

    function escapeCell(value) {
      var str = String(value == null ? '' : value);
      return '"' + str.replace(/"/g, '""') + '"';
    }

    var rows = [headers].concat(
      events.map(function (event) {
        return [
          event.staff,
          event.type,
          event.timestamp,
          event.device,
          event.location,
          event.sync
        ];
      })
    );

    return rows
      .map(function (row) {
        return row.map(escapeCell).join(',');
      })
      .join('\n');
  }

  // ── Component factory ────────────────────────────────────────
  function attendancePage(opts) {
    opts = opts || {};

    var _data = opts.data || (
      typeof window !== 'undefined' ? window.ATTENDANCE_DATA : { clockEvents: [], auditTrail: [] }
    );
    var _storage = opts.storage || (
      typeof window !== 'undefined' ? window.localStorage : null
    );
    var _utils = opts.utils || (
      typeof window !== 'undefined' ? window.AttendanceUtils : null
    );
    var _exportUrl = opts.exportUrl || _data.exportUrl || (
      typeof window !== 'undefined' ? '/api/admin/sheets/export' : ''
    );

    return {

      // ── Tab state (Ticket 2 — R2.1, AC2.1) ──
      activeTab: 'clock-events',

      // ── Clock events data + filters (Ticket 2 — R2.1) ──
      clockEvents:        _data.clockEvents.slice(),
      loading:            false,
      exporting:          false,
      exportError:        '',
      exportSuccess:      '',
      sheetUrl:           '',
      search:             '',
      staffFilter:        '',
      statusFilter:       '',
      staffDropdownOpen:  false,
      statusDropdownOpen: false,

      // ── Audit trail data (Ticket 2 — R2.2, R2.3, AC2.2) ──
      auditTrail:   _data.auditTrail.slice(),
      auditSortDir: 'desc',

      // ── Leave/Sick modal state (Ticket 1 — R1.1) ──
      showModal:   false,
      leaveForm:   { type: 'Leave', startDate: '', endDate: '', reason: '' },
      formErrors:  {},
      formSuccess: false,

      // ── Computed: filtered clock events ─────────────────────
      // Wires component filter state into AttendanceUtils.filterClockEvents.
      get filteredClockEvents() {
        return _utils.filterClockEvents(this.clockEvents, {
          search: this.search,
          staff:  this.staffFilter,
          status: this.statusFilter
        });
      },

      // ── Computed: sorted audit trail (Ticket 2 — R2.3) ──────
      // Default sort: newest first (desc). Flips on toggleAuditSort().
      get sortedAuditTrail() {
        return _utils.sortAuditTrail(this.auditTrail, this.auditSortDir);
      },

      // ── Audit sort toggle (Ticket 2 — AC2.4) ────────────────
      toggleAuditSort: function () {
        this.auditSortDir = this.auditSortDir === 'desc' ? 'asc' : 'desc';
      },

      // ── Export audit CSV (Ticket 2 — R2.4, R2.5, AC2.3) ────
      // _download is injectable: omit in Alpine, pass jest.fn() in tests.
      exportAuditCSV: function (_download) {
        var csv      = _utils.formatAuditCSV(this.sortedAuditTrail);
        var date     = new Date().toISOString().split('T')[0];
        var filename = 'audit_trail_' + date + '.csv';
        (_download || triggerDownload)(csv, filename);
      },

      exportClockEventsCSV: async function (_download) {
        if (_download) {
          var csv      = formatClockEventsCSV(this.filteredClockEvents);
          var date     = new Date().toISOString().split('T')[0];
          var filename = 'attendance_logs_' + date + '.csv';
          _download(csv, filename);
          return;
        }

        this.exporting = true;
        this.exportError = '';
        this.exportSuccess = '';
        this.sheetUrl = '';

        try {
          if (this.filteredClockEvents.length === 0) {
            throw new Error('No attendance logs match the current filters.');
          }

          var response = await fetch(_exportUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              event_ids: this.filteredClockEvents.map(function (event) { return event.id; })
            })
          });
          var responseText = await response.text();
          var payload;

          try {
            payload = responseText ? JSON.parse(responseText) : {};
          } catch (parseError) {
            payload = {
              success: false,
              message: 'Backend returned a non-JSON response. Check the PHP server output for the export request.'
            };
          }

          if (!response.ok || !payload.success) {
            throw new Error(payload.message || 'Failed to export attendance logs.');
          }

          this.sheetUrl = payload.sheet_url || '';
          this.exportSuccess = payload.message || 'Attendance logs exported to Google Sheets.';

          if (this.sheetUrl && typeof window !== 'undefined') {
            window.open(this.sheetUrl, '_blank', 'noopener');
          }
        } catch (error) {
          this.exportError = error.message || 'Failed to export attendance logs.';
        } finally {
          this.exporting = false;
        }
      },

      // ── Modal open (Ticket 1 — AC1.1) ───────────────────────
      openModal: function () {
        this.leaveForm   = { type: 'Leave', startDate: '', endDate: '', reason: '' };
        this.formErrors  = {};
        this.formSuccess = false;
        this.showModal   = true;
      },

      // ── Modal close (Ticket 1 — R1.5) ───────────────────────
      // Called by ✕ button, backdrop click, and Escape key binding.
      closeModal: function () {
        this.showModal = false;
      },

      // ── Submit leave request (Ticket 1 — R1.3, R1.4, AC1.3, AC1.4) ─
      // Flow: validate → bail with errors OR save + show success + schedule close.
      submitLeaveRequest: function () {
        this.formErrors = _utils.validateLeaveForm(this.leaveForm);
        if (Object.keys(this.formErrors).length > 0) return;

        _utils.saveLeaveRequest(this.leaveForm, _storage);
        this.formSuccess = true;

        var self = this;
        setTimeout(function () { self.closeModal(); }, 1500);
      }

    };
  }

  // ── Exports ─────────────────────────────────────────────────

  // Browser: register as global so Alpine's x-data="attendancePage()" resolves it
  if (typeof window !== 'undefined') {
    window.attendancePage = attendancePage;
  }

  // // Node / Jest
  // if (typeof module !== 'undefined' && module.exports) {
  //   module.exports = attendancePage;
  // }
})();
