<?php
// =============================================================================
// components/export_csv_button.php
// =============================================================================
// PHP equivalent of ExportCSVButton.tsx.
//
// USAGE — include this wherever the button is needed:
//
//   <?php include __DIR__ . '/components/export_csv_button.php'; 
//
// COMMUNICATION WITH PARENT:
//   This component's JS (export_csv_button.js) exposes:
//
//     window.ExportCSVButton.setRows(rows)
//
//   The parent (attendance_log.js) calls setRows() with the currently
//   filtered clock events whenever filters change — mirroring the
//   rows={filteredClockEvents} prop from the React version.
//
// SCRIPT DEPENDENCY:
//   export_csv_button.js must be loaded on the page. attendance_log.php
//   handles this with its own <script> tag so the component stays
//   self-contained and doesn't load itself twice.
// =============================================================================
?>

<div id="export-csv-wrapper">
  <button
    id="export-csv-btn"
    data-testid="export-csv-button"
    aria-label="Export CSV"
    style="display:inline-flex;align-items:center;gap:6px;
           padding:8px 14px;background:#ffffff;
           border:1px solid #c8d3de;border-radius:8px;
           font-size:14px;font-weight:500;color:#0d1f35;
           cursor:pointer;white-space:nowrap;font-family:inherit;
           opacity:1;transition:background 0.15s,border-color 0.15s,color 0.15s;"
  >
    <!-- Download icon — mirrors <Download size={14} /> from lucide-react -->
    <svg
      width="14" height="14" viewBox="0 0 24 24"
      fill="none" stroke="currentColor" stroke-width="2"
      stroke-linecap="round" stroke-linejoin="round"
      style="flex-shrink:0;"
    >
      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
      <polyline points="7 10 12 15 17 10"/>
      <line x1="12" y1="15" x2="12" y2="3"/>
    </svg>

    <span id="export-csv-label">Export CSV</span>
  </button>

  <!-- Error message — mirrors the {error && <p role="alert">…</p>} in ExportCSVButton.tsx -->
  <p
    id="export-csv-error"
    role="alert"
    style="color:#b91c1c;font-size:13px;margin-top:6px;display:none;"
  ></p>
</div>
