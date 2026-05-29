// =============================================================================
// export_csv_button.js
// =============================================================================
// JS equivalent of ExportCSVButton.tsx. Fully self-contained — owns its own
// state, event listeners, and hover behaviour.
//
// PUBLIC API  (window.ExportCSVButton):
//
//   .setRows(rows)
//     Called by attendance_log.js whenever the filtered clock events change.
//     Mirrors the rows={filteredClockEvents} prop from the React version —
//     the button always exports whatever the parent's current filter produces.
//
// The parent (attendance_log.js) is the only caller of setRows().
// This file does not import or depend on attendance_log.js.
// =============================================================================

(function () {
  'use strict';

  // ── Design tokens ────────────────────────────────────────────────────────────
  // Mirror of the C object in AttendanceLog — kept here so this component is
  // self-contained and doesn't create a cross-file dependency (same rationale
  // as the comment in ExportCSVButton.tsx).
  const C = {
    cardBg:      '#ffffff',
    borderInput: '#c8d3de',
    green:       '#78a835',
    textPrimary: '#0d1f35',
  };

  // ── Component state ───────────────────────────────────────────────────────────
  // rows  : the currently filtered ClockEvent array; set by the parent via setRows()
  // loading: true while export is in progress
  let rows    = [];
  let loading = false;

  // ── DOM refs (resolved once on init) ─────────────────────────────────────────
  let btn     = null;
  let labelEl = null;
  let errorEl = null;

  // ════════════════════════════════════════════════════════════════════════════
  // EXPORT LOGIC
  // Preserves all behaviour from ExportCSVButton.tsx:
  //   • loading state + label swap
  //   • disabled + opacity 0.45 while exporting
  //   • await Promise.resolve() yield so loading state renders before CSV work
  //   • BOM prefix for Excel / Google Sheets UTF-8 compatibility
  //   • empty-dataset alert (headers-only export)
  //   • error message on failure (role="alert")
  //   • hover colours reset correctly after export
  // ════════════════════════════════════════════════════════════════════════════

  async function exportToCSV() {
    if (loading) return;

    try {
      loading = true;
      setLoadingUI(true);

      // Yield to the browser so the loading state renders before synchronous
      // CSV work begins — mirrors the await Promise.resolve() in ExportCSVButton.tsx.
      await Promise.resolve();

      // ── Headers ─────────────────────────────────────────────────────────────
      // Column order matches the table displayed on screen.
      const headers = [
        'Staff Name',
        'Timestamp',
        'Event Type',
        'Device',
        'Location',
        'Sync Status',
      ];

      // ── Empty dataset ────────────────────────────────────────────────────────
      // Ticket: "Handle empty dataset — export headers only or toast message"
      if (rows.length === 0) {
        alert('No attendance data found. Exporting headers only.');
      }

      // ── CSV rows ─────────────────────────────────────────────────────────────
      // Each cell quoted so values containing commas (e.g. "Cape Town, WC")
      // don't break column structure in Google Sheets.
      const csvRows = rows.map(item => [
        item.staff     || '',
        item.timestamp || '',
        item.type      || '',
        item.device    || '',
        item.location  || '',
        item.sync      || '',
      ]);

      // ── Assemble CSV string ──────────────────────────────────────────────────
      const csvContent = [
        headers.join(','),
        ...csvRows.map(row =>
          row.map(field => `"${String(field).replace(/"/g, '""')}"`).join(',')
        ),
      ].join('\n');

      // ── BOM prefix ───────────────────────────────────────────────────────────
      // \uFEFF tells Excel and Google Sheets this file is UTF-8 encoded.
      // Without it, accented characters (é, ü, etc.) can appear corrupted.
      const blob = new Blob(['\uFEFF' + csvContent], {
        type: 'text/csv;charset=utf-8;',
      });

      // ── Programmatic download ────────────────────────────────────────────────
      // Mirrors the Object URL pattern from ExportCSVButton.tsx.
      const url  = await Promise.resolve(window.URL.createObjectURL(blob));
      const link = document.createElement('a');
      const date = new Date().toISOString().split('T')[0];

      link.href     = url;
      link.download = `attendance_logs_${date}.csv`;

      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      window.URL.revokeObjectURL(url);

    } catch (err) {
      console.error(err);
      // Ticket: "Error message shown if export fails"
      if (errorEl) {
        errorEl.textContent   = 'Failed to export CSV file.';
        errorEl.style.display = 'block';
      }
    } finally {
      loading = false;
      setLoadingUI(false);
    }
  }

  // ── UI state helpers ──────────────────────────────────────────────────────────

  function setLoadingUI(isLoading) {
    if (!btn) return;
    btn.disabled      = isLoading;
    btn.style.opacity = isLoading ? '0.45' : '1';
    btn.style.cursor  = isLoading ? 'not-allowed' : 'pointer';
    if (labelEl) labelEl.textContent = isLoading ? 'Exporting...' : 'Export CSV';
    if (errorEl && isLoading) errorEl.style.display = 'none';
  }

  // ════════════════════════════════════════════════════════════════════════════
  // INITIALISATION
  // ════════════════════════════════════════════════════════════════════════════

  function init() {
    btn     = document.getElementById('export-csv-btn');
    labelEl = document.getElementById('export-csv-label');
    errorEl = document.getElementById('export-csv-error');

    if (!btn) return; // component not present on this page

    btn.addEventListener('click', exportToCSV);

    // Hover — mirrors onMouseEnter / onMouseLeave in ExportCSVButton.tsx
    btn.addEventListener('mouseenter', () => {
      if (!loading) {
        btn.style.background  = C.green;
        btn.style.borderColor = C.green;
        btn.style.color       = '#ffffff';
      }
    });
    btn.addEventListener('mouseleave', () => {
      btn.style.background  = C.cardBg;
      btn.style.borderColor = C.borderInput;
      btn.style.color       = C.textPrimary;
    });
  }

  // ════════════════════════════════════════════════════════════════════════════
  // PUBLIC API
  // ════════════════════════════════════════════════════════════════════════════

  window.ExportCSVButton = {
    /**
     * Receives the currently filtered clock events from the parent page.
     * Called by attendance_log.js whenever filters change.
     * Mirrors the rows={filteredClockEvents} prop from ExportCSVButton.tsx.
     *
     * @param {Array} filteredRows - ClockEvent objects matching active filters
     */
    setRows(filteredRows) {
      rows = Array.isArray(filteredRows) ? filteredRows : [];
    },
  };

  // Boot when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
