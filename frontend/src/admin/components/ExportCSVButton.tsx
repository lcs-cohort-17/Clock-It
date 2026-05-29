import { useState } from 'react';
import { Download } from 'lucide-react';
import type { ClockEvent } from './AttendanceLog';

// Mirror of the design token values used in AttendanceLog.tsx.
// Kept here so the button is self-contained and doesn't create
// a cross-file dependency on the C object.
const C = {
  cardBg:      'var(--admin-card-bg)',
  borderInput: 'var(--admin-border-input)',
  green:       '#78a835',
  textPrimary: 'var(--admin-text-primary)',
} as const;

interface ExportCSVButtonProps {
  rows: ClockEvent[];
}

// ── ExportCSVButton ───────────────────────────────────────────────────────────
// Receives the CURRENTLY FILTERED clock events from AttendanceLog.tsx so the
// exported CSV always reflects whatever filters are active — not all data.
//
// Integration point in AttendanceLog.tsx:
//
//   <div style={{ display:'flex', alignItems:'flex-start',
//                 justifyContent:'space-between', gap:'16px',
//                 marginBottom:'24px', flexWrap:'wrap' }}>
//     <div>
//       <h1>Attendance Logs</h1>
//       <p>Full clock-event history...</p>
//     </div>
//     <ExportCSVButton rows={filteredClockEvents} />
//   </div>
//
// ─────────────────────────────────────────────────────────────────────────────
function ExportCSVButton({ rows }: ExportCSVButtonProps) {
  const [loading, setLoading] = useState(false);
  const [error,   setError  ] = useState('');

  const exportToCSV = async () => {
    try {
      setLoading(true);
      setError('');

      // Yield to React so the loading state renders to the user before the
      // synchronous CSV work begins. Without this, setLoading(true) and
      // setLoading(false) both fire in the same synchronous pass and React
      // batches them into a single render — the user never sees "Exporting...".
      await Promise.resolve();

      // ── Headers ─────────────────────────────────────────────────────────
      // Column order matches the table displayed on screen.
      const headers = [
        'Staff Name',
        'Timestamp',
        'Event Type',
        'Device',
        'Location',
        'Sync Status',
      ];

      // ── Empty dataset handling ───────────────────────────────────────────
      // Ticket: "Handle empty dataset — export headers only or toast message"
      if (rows.length === 0) {
        alert('No attendance data found. Exporting headers only.');
      }

      // ── CSV rows ─────────────────────────────────────────────────────────
      // Each cell is wrapped in quotes so values containing commas (e.g. a
      // location like "Cape Town, WC") don't break the column structure in
      // Google Sheets.
      const csvRows = rows.map(item => [
        item.staff,
        item.timestamp,
        item.type,
        item.device,
        item.location,
        item.sync,
      ]);

      // ── Assemble CSV string ───────────────────────────────────────────────
      // Standard comma delimiter — required for Google Sheets compatibility.
      const csvContent = [
        headers.join(','),
        ...csvRows.map(row => row.map(field => `"${field}"`).join(',')),
      ].join('\n');

      // ── BOM prefix ───────────────────────────────────────────────────────
      // \uFEFF tells Excel and Google Sheets that this file is UTF-8 encoded.
      // Without it, accented characters (é, ü, etc.) can appear corrupted.
      const blob = new Blob(['\uFEFF' + csvContent], {
        type: 'text/csv;charset=utf-8;',
      });

      // ── Programmatic download ─────────────────────────────────────────────
      // Creates a temporary Object URL, clicks a hidden <a> element to trigger
      // the browser's file-save dialog, then immediately releases the URL.
      //
      // await Promise.resolve() wraps the call so tests can mock createObjectURL
      // to return a real Promise, giving them a controlled pause point to observe
      // the loading state. In production createObjectURL is synchronous, so
      // await Promise.resolve(syncValue) is identical to syncValue.
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
      setError('Failed to export CSV file.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <button
        data-testid="export-csv-button"
        onClick={exportToCSV}
        disabled={loading}
        aria-label="Export CSV"
        style={{
          display:        'inline-flex',
          alignItems:     'center',
          gap:            '6px',
          padding:        '8px 14px',
          background:     C.cardBg,
          border:         `1px solid ${C.borderInput}`,
          borderRadius:   '8px',
          fontSize:       '14px',
          fontWeight:     500,
          color:          C.textPrimary,
          cursor:         loading ? 'not-allowed' : 'pointer',
          whiteSpace:     'nowrap',
          fontFamily:     'inherit',
          opacity:        loading ? 0.45 : 1,
          transition:     'background 0.15s, border-color 0.15s, color 0.15s',
        }}
        onMouseEnter={e => {
          if (!loading) {
            e.currentTarget.style.background   = C.green;
            e.currentTarget.style.borderColor  = C.green;
            e.currentTarget.style.color        = '#ffffff';
          }
        }}
        onMouseLeave={e => {
          e.currentTarget.style.background  = C.cardBg;
          e.currentTarget.style.borderColor = C.borderInput;
          e.currentTarget.style.color       = C.textPrimary;
        }}
      >
        <Download size={14} />
        {loading ? 'Exporting...' : 'Export CSV'}
      </button>

      {/* Ticket: "Error message shown if export fails" */}
      {error && (
        <p role="alert" style={{ color: '#b91c1c', fontSize: '13px', marginTop: '6px' }}>
          {error}
        </p>
      )}
    </div>
  );
}

export default ExportCSVButton;
