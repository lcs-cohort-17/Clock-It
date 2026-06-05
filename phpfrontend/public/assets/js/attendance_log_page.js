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
  "use strict";

  // ── Real DOM download (browser only) ────────────────────────
  function triggerDownload(csv, filename) {
    var blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
    var url = URL.createObjectURL(blob);
    var a = document.createElement("a");
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  function formatClockEventsCSV(events) {
    var headers = [
      "Staff Name",
      "Event Type",
      "Timestamp",
      "Device",
      "Location",
      "Sync Status",
    ];

    function escapeCell(value) {
      var str = String(value == null ? "" : value);
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
          event.sync,
        ];
      }),
    );

    return rows
      .map(function (row) {
        return row.map(escapeCell).join(",");
      })
      .join("\n");
  }

  // ── Component factory ────────────────────────────────────────
  function attendancePage(opts) {
    opts = opts || {};

    var _data =
      opts.data ||
      (typeof window !== "undefined"
        ? window.ATTENDANCE_DATA
        : { clockEvents: [], auditTrail: [] });
    var _storage =
      opts.storage ||
      (typeof window !== "undefined" ? window.localStorage : null);
    var _utils =
      opts.utils ||
      (typeof window !== "undefined" ? window.AttendanceUtils : null);
    var _exportUrl =
      opts.exportUrl ||
      _data.exportUrl ||
      (typeof window !== "undefined" ? "/api/admin/sheets/export" : "");

    return {

      // ── Tab state ──────────────────────────────────────────────
      activeTab: "clock-events",

      // ── Clock events data + filters ───────────────────────────
      clockEvents: _data.clockEvents.slice(),
      loading: false,
      exporting: false,
      exportError: "",
      exportSuccess: "",
      sheetUrl: "",
      search: "",
      staffFilter: "",
      statusFilter: "",
      staffDropdownOpen: false,
      statusDropdownOpen: false,

      // ── Audit trail data ──────────────────────────────────────
      auditTrail: _data.auditTrail.slice(),
      auditSortDir: "desc",

      // ── Leave/Sick modal state ────────────────────────────────
      showModal: false,
      leaveForm: { type: "Leave", startDate: "", endDate: "", reason: "" },
      formErrors: {},
      formSuccess: false,

      // ── Edit event modal state ────────────────────────────────
      showEditEventModal: false,
      editEventId: "",
      editEventForm: { event_type: "in", event_time: "", location: "" },
      editEventError: "",
      editEventLoading: false,

      // ── Computed: filtered clock events ───────────────────────
      get filteredClockEvents() {
        return _utils.filterClockEvents(this.clockEvents, {
          search: this.search,
          staff: this.staffFilter,
          status: this.statusFilter,
        });
      },

      // ── Computed: sorted audit trail ──────────────────────────
      get sortedAuditTrail() {
        return _utils.sortAuditTrail(this.auditTrail, this.auditSortDir);
      },

      // ── Audit sort toggle ─────────────────────────────────────
      toggleAuditSort: function () {
        this.auditSortDir = this.auditSortDir === "desc" ? "asc" : "desc";
      },

      // ── Export audit CSV ──────────────────────────────────────
      exportAuditCSV: function (_download) {
        var csv = _utils.formatAuditCSV(this.sortedAuditTrail);
        var date = new Date().toISOString().split("T")[0];
        var filename = "audit_trail_" + date + ".csv";
        (_download || triggerDownload)(csv, filename);
      },

      // ── Export clock events to Google Sheets ──────────────────
      exportClockEventsCSV: async function (_download) {
        if (_download) {
          var csv = formatClockEventsCSV(this.filteredClockEvents);
          var date = new Date().toISOString().split("T")[0];
          var filename = "attendance_logs_" + date + ".csv";
          _download(csv, filename);
          return;
        }

        this.exporting = true;
        this.exportError = "";
        this.exportSuccess = "";
        this.sheetUrl = "";

        try {
          if (this.filteredClockEvents.length === 0) {
            throw new Error("No attendance logs match the current filters.");
          }

          var response = await fetch(_exportUrl, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              event_ids: this.filteredClockEvents.map(function (event) {
                return event.id;
              }),
            }),
          });
          var responseText = await response.text();
          var payload;

          try {
            payload = responseText ? JSON.parse(responseText) : {};
          } catch (parseError) {
            payload = {
              success: false,
              message:
                "Backend returned a non-JSON response. Check the PHP server output for the export request.",
            };
          }

          if (!response.ok || !payload.success) {
            throw new Error(
              payload.message || "Failed to export attendance logs.",
            );
          }

          this.sheetUrl = payload.sheet_url || "";
          this.exportSuccess =
            payload.message || "Attendance logs exported to Google Sheets.";

          if (this.sheetUrl && typeof window !== "undefined") {
            window.open(this.sheetUrl, "_blank", "noopener");
          }
        } catch (error) {
          this.exportError =
            error.message || "Failed to export attendance logs.";
        } finally {
          this.exporting = false;
        }
      },

      // ── Open leave/sick modal ─────────────────────────────────
      openModal: function () {
        this.leaveForm = {
          type: "Leave",
          startDate: "",
          endDate: "",
          reason: "",
        };
        this.formErrors = {};
        this.formSuccess = false;
        this.showModal = true;
      },

      // ── Close leave/sick modal ────────────────────────────────
      closeModal: function () {
        this.showModal = false;
      },

      // ── Submit leave request ──────────────────────────────────
      submitLeaveRequest: function () {
        this.formErrors = _utils.validateLeaveForm(this.leaveForm);
        if (Object.keys(this.formErrors).length > 0) return;

        _utils.saveLeaveRequest(this.leaveForm, _storage);
        this.formSuccess = true;

        var self = this;
        setTimeout(function () {
          self.closeModal();
        }, 1500);
      },

      // ── Open edit attendance modal ────────────────────────────
      openEditEvent: function (event) {
        this.editEventId = event.id;

        var rawType = (event.event_type || event.type || "").toLowerCase();
        if (rawType === "clock in"  || rawType === "clock_in")  rawType = "in";
        if (rawType === "clock out" || rawType === "clock_out") rawType = "out";

        // datetime-local input requires "YYYY-MM-DDTHH:mm"
        var rawTime = (event.event_time || event.timestamp || "")
          .replace(" ", "T")
          .slice(0, 16);

        this.editEventForm = {
          event_type: rawType || "in",
          event_time: rawTime,
          location: event.location || "",
        };
        this.editEventError = "";
        this.showEditEventModal = true;
      },

      // ── Submit attendance edit (PATCH to backend) ─────────────
      submitEditEvent: async function () {
        if (!this.editEventForm.event_time) {
          this.editEventError = "Timestamp is required.";
          return;
        }

        this.editEventLoading = true;
        this.editEventError = "";

        try {
          var basePath = window.clockItBasePath || "";
          var response = await fetch(
            basePath + "/api/admin/attendance/" + encodeURIComponent(this.editEventId),
            {
              method: "PATCH",
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
              },
              body: JSON.stringify({
                event_type: this.editEventForm.event_type,
                event_time: this.editEventForm.event_time.replace("T", " "),
                location: this.editEventForm.location,
              }),
            },
          );

          var payload = await response.json();
          if (!payload.success) {
            throw new Error(payload.message || "Failed to update record.");
          }

          // findIndex does not accept a thisArg — use a closure variable instead
          var self = this;
          var idx = this.clockEvents.findIndex(function (e) {
            return e.id === self.editEventId;
          });
          if (idx !== -1 && payload.data) {
            this.clockEvents.splice(idx, 1, payload.data);
          }

          this.showEditEventModal = false;

        } catch (e) {
          this.editEventError = e.message || "Failed to update record.";
        } finally {
          this.editEventLoading = false;
        }
      },

    }; // ← end of returned object
  }   // ← end of attendancePage()

  // ── Exports ──────────────────────────────────────────────────

  // Browser: register as global so Alpine's x-data="attendancePage()" resolves it
  if (typeof window !== "undefined") {
    window.attendancePage = attendancePage;
  }

  // // Node / Jest
  // if (typeof module !== 'undefined' && module.exports) {
  //   module.exports = attendancePage;
  // }
})();