/**
 * attendance_log_utils.js
 *
 * Pure utility functions for the Attendance Log page.
 * Exposed on window.AttendanceUtils for Alpine.js consumption,
 * and via module.exports for Jest testability.
 *
 * Rule: every function here is side-effect-free and dependency-free.
 * Storage writes are injected (saveLeaveRequest takes a `storage` param)
 * so tests never need to mock window.localStorage.
 */
(function () {
  'use strict';

  // ─────────────────────────────────────────────
  // Leave / Sick request validation
  // ─────────────────────────────────────────────

  /**
   * Validates a leave/sick request form.
   *
   * @param {{ type: string, startDate: string, endDate: string, reason: string }} form
   * @param {boolean} [rejectPastDates=true]  Whether to flag past start dates as invalid.
   * @returns {Object} errors — keyed by field name, empty object means valid.
   */
  function validateLeaveForm(form, rejectPastDates) {
    if (rejectPastDates === undefined) rejectPastDates = true;
    var errors = {};

    if (!form.type) {
      errors.type = 'Please select a type.';
    }

    if (!form.startDate) {
      errors.startDate = 'Start date is required.';
    } else if (rejectPastDates) {
      var today = new Date().toISOString().split('T')[0];
      if (form.startDate < today) {
        errors.startDate = 'Start date cannot be in the past.';
      }
    }

    if (!form.endDate) {
      errors.endDate = 'End date is required.';
    } else if (form.startDate && form.endDate < form.startDate) {
      errors.endDate = 'End date must be on or after the start date.';
    }

    if (!form.reason || form.reason.trim() === '') {
      errors.reason = 'Reason is required.';
    }

    return errors;
  }

  // ─────────────────────────────────────────────
  // Audit trail CSV formatting
  // ─────────────────────────────────────────────

  /**
   * Formats audit trail events as RFC-4180-compliant CSV.
   *
   * @param {Array<{ timestamp: string, actor: string, action: string, details: string }>} events
   * @returns {string} CSV string with header row
   */
  function formatAuditCSV(events) {
    var headers = ['Timestamp', 'Actor', 'Action', 'Details'];

    function escapeCell(value) {
      var str = String(value == null ? '' : value);
      // Wrap in quotes; double any internal quotes per RFC 4180
      return '"' + str.replace(/"/g, '""') + '"';
    }

    var rows = [headers].concat(
      events.map(function (e) {
        return [e.timestamp, e.actor, e.action, e.details];
      })
    );

    return rows
      .map(function (row) {
        return row.map(escapeCell).join(',');
      })
      .join('\n');
  }

  // ─────────────────────────────────────────────
  // Audit trail sort
  // ─────────────────────────────────────────────

  /**
   * Sorts audit trail events by timestamp.
   * Returns a new array — does not mutate the original.
   *
   * @param {Array} events
   * @param {'asc'|'desc'} [direction='desc']
   * @returns {Array}
   */
  function sortAuditTrail(events, direction) {
    if (direction === undefined) direction = 'desc';
    return events.slice().sort(function (a, b) {
      var da = new Date(a.timestamp).getTime();
      var db = new Date(b.timestamp).getTime();
      return direction === 'desc' ? db - da : da - db;
    });
  }

  // ─────────────────────────────────────────────
  // Leave request persistence
  // ─────────────────────────────────────────────

  /**
   * Appends a leave request to localStorage.
   * The `storage` parameter is injectable so tests can pass a mock.
   *
   * @param {{ type: string, startDate: string, endDate: string, reason: string }} request
   * @param {Storage} storage  window.localStorage (or a test double)
   * @returns {Array} Full updated array of all saved requests
   */
  function saveLeaveRequest(request, storage) {
    var key = 'leaveRequests';
    var existing = [];
    try {
      existing = JSON.parse(storage.getItem(key) || '[]');
    } catch (_) {
      existing = [];
    }
    var entry = Object.assign({}, request, {
      id: Date.now(),
      submittedAt: new Date().toISOString()
    });
    var updated = existing.concat([entry]);
    storage.setItem(key, JSON.stringify(updated));
    return updated;
  }

  // ─────────────────────────────────────────────
  // Clock event filtering
  // ─────────────────────────────────────────────

  /**
   * Filters clock events against the active filter bar state.
   *
   * @param {Array}  events
   * @param {{ search?: string, staff?: string, status?: string }} filters
   * @returns {Array} filtered events (new array)
   */
  function filterClockEvents(events, filters) {
    var search = (filters.search || '').toLowerCase().trim();
    var staff  = filters.staff  || '';
    var status = filters.status || '';

    return events.filter(function (e) {
      var matchesSearch =
        !search ||
        (e.staff    || '').toLowerCase().includes(search) ||
        (e.location || '').toLowerCase().includes(search);

      var matchesStaff  = !staff  || e.staff === staff;
      var matchesStatus = !status || e.type  === status;

      return matchesSearch && matchesStaff && matchesStatus;
    });
  }

  // ─────────────────────────────────────────────
  // Export
  // ─────────────────────────────────────────────

  var AttendanceUtils = {
    validateLeaveForm:  validateLeaveForm,
    formatAuditCSV:     formatAuditCSV,
    sortAuditTrail:     sortAuditTrail,
    saveLeaveRequest:   saveLeaveRequest,
    filterClockEvents:  filterClockEvents
  };

  // Browser: make available to Alpine.js
  if (typeof window !== 'undefined') {
    window.AttendanceUtils = AttendanceUtils;
  }

  // // Node / Jest
  // if (typeof module !== 'undefined' && module.exports) {
  //   module.exports = AttendanceUtils;
  // }
})();
