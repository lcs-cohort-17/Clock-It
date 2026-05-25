// ExportCSVButton.test.tsx
//
// Covers all 10 ticket subtasks from DAT-EPIC-02:
//
//  1.  Button with icon is visible on page
//  2.  Clicking button downloads CSV file
//  3.  CSV includes current filters (filtered rows, not all data)
//  4.  Dynamic filename with current date
//  5.  Standard comma delimiter (Google Sheets compatible)
//  6.  Empty dataset shows headers only + user notification
//  7.  Loading state shown during export
//  8.  Error message shown if export fails
//  9.  Unit test for CSV formatting (headers + row data)
// 10.  Test for empty data handling

import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import ExportCSVButton from '../components/ExportCSVButton';
import type { ClockEvent } from '../components/AttendanceLog';

// ── Mock data ─────────────────────────────────────────────────────────────────
// ClockType union only accepts: CLOCK_IN | CLOCK_OUT | BREAK_START | BREAK_END
// The other developer had type: "IN" which is not a valid ClockType value.
const mockRows: ClockEvent[] = [
  {
    id:        '1',
    staff:     'John Doe',
    type:      'CLOCK_IN',
    timestamp: '2026-05-21 08:00',
    device:    'iPhone',
    location:  'Cape Town',
    sync:      'SYNCED',
  },
  {
    id:        '2',
    staff:     'Aisha Patel',
    type:      'CLOCK_OUT',
    timestamp: '2026-05-21 17:00',
    device:    'Android',
    location:  'Johannesburg',
    sync:      'PENDING',
  },
];

// ── Helpers ───────────────────────────────────────────────────────────────────

// Creates a fake <a> element that records href and download without interfering
// with React's own document.createElement calls (which use tags like 'div').
// The original createElement is preserved for all non-'a' tags.
function mockAnchorElement() {
  const original = document.createElement.bind(document);

  let capturedHref     = '';
  let capturedDownload = '';

  const fakeLink = {
    click: vi.fn(),
    get href()          { return capturedHref; },
    set href(v: string) { capturedHref = v; },
    get download()          { return capturedDownload; },
    set download(v: string) { capturedDownload = v; },
  };

  vi.spyOn(document, 'createElement').mockImplementation((tag: string) => {
    if (tag === 'a') return fakeLink as unknown as HTMLElement;
    return original(tag);
  });

  // Prevent the fake link from being appended to / removed from document.body
  vi.spyOn(document.body, 'appendChild').mockImplementation(() => document.body as unknown as Node);
  vi.spyOn(document.body, 'removeChild').mockImplementation(() => document.body as unknown as Node);

  return { fakeLink, getCapturedDownload: () => capturedDownload };
}

// ── Suite ─────────────────────────────────────────────────────────────────────
describe('ExportCSVButton — DAT-EPIC-02', () => {

  beforeEach(() => {
    // Stub URL methods used in the download flow.
    vi.stubGlobal('URL', {
      createObjectURL: vi.fn(() => 'mock-url'),
      revokeObjectURL: vi.fn(),
    });

    // Stub alert so empty-dataset tests don't open a real dialog.
    vi.stubGlobal('alert', vi.fn());
  });

  afterEach(() => {
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
  });

  // ── Subtask 1 ──────────────────────────────────────────────────────────────
  // "Add Export CSV button with icon to Attendance Logs page"
  it('renders Export CSV button with text and icon', () => {
    render(<ExportCSVButton rows={mockRows} />);

    const button = screen.getByTestId('export-csv-button');
    expect(button).toBeInTheDocument();

    // Button text
    expect(screen.getByText(/export csv/i)).toBeInTheDocument();

    // Icon — lucide-react renders an <svg> inside the button
    expect(button.querySelector('svg')).not.toBeNull();
  });

  // ── Subtask 2 ──────────────────────────────────────────────────────────────
  // "Clicking button downloads CSV file"
  it('clicking button triggers CSV download', async () => {
    render(<ExportCSVButton rows={mockRows} />);

    // Mock AFTER render — mockAnchorElement replaces document.createElement globally.
    // If called before render(), React's own createElement calls are intercepted
    // and the component never mounts (body is empty).
    const { fakeLink } = mockAnchorElement();

    await act(async () => {
      fireEvent.click(screen.getByTestId('export-csv-button'));
    });

    await waitFor(() => {
      // A Blob URL was created
      expect(URL.createObjectURL).toHaveBeenCalledTimes(1);
      // The link was programmatically clicked
      expect(fakeLink.click).toHaveBeenCalledTimes(1);
      // The Blob URL was released after download
      expect(URL.revokeObjectURL).toHaveBeenCalledWith('mock-url');
    });
  });

  // ── Subtask 3 ──────────────────────────────────────────────────────────────
  // "Add current filters to export data (respect active filters)"
  // The component receives `rows` from AttendanceLog as `filteredClockEvents`,
  // so whatever is in `rows` is exactly what gets exported.
  // This test verifies only the passed-in rows appear in the CSV — not all data.
  it('exports only the rows passed in (respects active filters)', async () => {
    const filteredOnly: ClockEvent[] = [mockRows[0]]; // only John Doe
    const blobSpy = vi.spyOn(window, 'Blob');

    render(<ExportCSVButton rows={filteredOnly} />);

    await act(async () => {
      fireEvent.click(screen.getByTestId('export-csv-button'));
    });

    const blobContent = (blobSpy.mock.calls[0][0] as string[])[0];

    // John Doe IS in the export
    expect(blobContent).toContain('John Doe');
    // Aisha Patel is NOT — she was filtered out
    expect(blobContent).not.toContain('Aisha Patel');
  });

  // ── Subtask 4 ──────────────────────────────────────────────────────────────
  // "Set dynamic filename: attendance_logs_YYYY-MM-DD.csv"
  it('generates filename with current date', async () => {
    render(<ExportCSVButton rows={mockRows} />);

    // Mock AFTER render for the same reason as the download test above.
    const { getCapturedDownload } = mockAnchorElement();

    await act(async () => {
      fireEvent.click(screen.getByTestId('export-csv-button'));
    });

    const today = new Date().toISOString().split('T')[0];

    await waitFor(() => {
      expect(getCapturedDownload()).toBe(`attendance_logs_${today}.csv`);
    });
  });

  // ── Subtask 5 ──────────────────────────────────────────────────────────────
  // "Ensure CSV uses standard comma delimiter (Google Sheets default)"
  it('CSV uses comma delimiter and includes BOM for Google Sheets', async () => {
    const blobSpy = vi.spyOn(window, 'Blob');

    render(<ExportCSVButton rows={mockRows} />);

    await act(async () => {
      fireEvent.click(screen.getByTestId('export-csv-button'));
    });

    const blobContent = (blobSpy.mock.calls[0][0] as string[])[0];

    // BOM prefix ensures UTF-8 encoding is recognised by Google Sheets
    expect(blobContent.startsWith('\uFEFF')).toBe(true);

    // First data line uses commas — headers joined with ,
    const firstLine = blobContent.replace('\uFEFF', '').split('\n')[0];
    expect(firstLine).toBe('Staff Name,Timestamp,Event Type,Device,Location,Sync Status');

    // Data cells are comma-separated and quoted
    const secondLine = blobContent.split('\n')[1];
    expect(secondLine).toContain('","');

    // Blob type is set correctly for Google Sheets compatibility
    const blobOptions = blobSpy.mock.calls[0][1] as BlobPropertyBag;
    expect(blobOptions.type).toBe('text/csv;charset=utf-8;');
  });

  // ── Subtask 6 + Subtask 10 ────────────────────────────────────────────────
  // "Handle empty dataset — export headers only or toast message"
  // "Write test for empty data handling"
  it('shows alert notification for empty dataset', async () => {
    render(<ExportCSVButton rows={[]} />);

    await act(async () => {
      fireEvent.click(screen.getByTestId('export-csv-button'));
    });

    expect(window.alert).toHaveBeenCalledWith(
      'No attendance data found. Exporting headers only.'
    );
  });

  it('still exports headers when dataset is empty', async () => {
    const blobSpy = vi.spyOn(window, 'Blob');

    render(<ExportCSVButton rows={[]} />);

    await act(async () => {
      fireEvent.click(screen.getByTestId('export-csv-button'));
    });

    const blobContent = (blobSpy.mock.calls[0][0] as string[])[0];

    // Headers must still be present
    expect(blobContent).toContain('Staff Name');
    expect(blobContent).toContain('Timestamp');
    expect(blobContent).toContain('Event Type');
    expect(blobContent).toContain('Device');
    expect(blobContent).toContain('Location');
    expect(blobContent).toContain('Sync Status');

    // No data rows beyond the header line
    const lines = blobContent.replace('\uFEFF', '').split('\n');
    expect(lines).toHaveLength(1); // header line only
  });

  // ── Subtask 7 ──────────────────────────────────────────────────────────────
  // "Add loading state while generating export"
  it('button is enabled initially and not in loading state', () => {
    render(<ExportCSVButton rows={mockRows} />);

    const button = screen.getByTestId('export-csv-button');
    expect(button).not.toBeDisabled();
    expect(button).toHaveTextContent(/export csv/i);
  });

  it('shows Exporting... and disables button while export runs', async () => {
    // createObjectURL returns a Promise we control — it won't resolve until
    // we call resolveUrl(). Because the component now awaits this value, the
    // export is genuinely paused at that line, giving us a window to assert
    // the loading state before the download completes.
    let resolveUrl!: (url: string) => void;
    vi.stubGlobal('URL', {
      createObjectURL: vi.fn(
        () => new Promise<string>(res => { resolveUrl = res; })
      ),
      revokeObjectURL: vi.fn(),
    });

    render(<ExportCSVButton rows={mockRows} />);
    mockAnchorElement();

    const button = screen.getByTestId('export-csv-button');

    // Click starts exportToCSV. It calls setLoading(true), setError(''), then
    // hits "await Promise.resolve()" — the first yield point. fireEvent's
    // internal act flushes the setLoading(true) render before returning.
    fireEvent.click(button);

    // Advance the component past the first yield (after setLoading(true)).
    // It continues synchronously until "await createObjectURL()" which returns
    // the hanging Promise — the component suspends there.
    await act(async () => {
      await Promise.resolve();
    });

    // Component is suspended waiting for the URL. Loading state is visible.
    expect(screen.getByText(/exporting\.\.\./i)).toBeInTheDocument();
    expect(button).toBeDisabled();

    // Resolve the URL — export continues: link is clicked, cleanup runs, loading clears.
    await act(async () => {
      resolveUrl('mock-url');
      await Promise.resolve();
    });

    await waitFor(() => {
      expect(screen.getByText(/export csv/i)).toBeInTheDocument();
      expect(button).not.toBeDisabled();
    });
  });

  // ── Subtask 8 ──────────────────────────────────────────────────────────────
  // "Add error toast if export fails"
  it('shows error message if export fails', async () => {
    // Make URL.createObjectURL throw — triggers the catch block
    vi.stubGlobal('URL', {
      createObjectURL: vi.fn(() => { throw new Error('Network error'); }),
      revokeObjectURL: vi.fn(),
    });

    render(<ExportCSVButton rows={mockRows} />);

    await act(async () => {
      fireEvent.click(screen.getByTestId('export-csv-button'));
    });

    await waitFor(() => {
      const alert = screen.getByRole('alert');
      expect(alert).toBeInTheDocument();
      expect(alert).toHaveTextContent('Failed to export CSV file.');
    });

    // Button should re-enable after failure
    expect(screen.getByTestId('export-csv-button')).not.toBeDisabled();
  });

  // ── Subtask 9 ──────────────────────────────────────────────────────────────
  // "Write unit test for CSV formatting (headers + row data)"
  it('CSV contains correct headers matching all required columns', async () => {
    const blobSpy = vi.spyOn(window, 'Blob');

    render(<ExportCSVButton rows={mockRows} />);

    await act(async () => {
      fireEvent.click(screen.getByTestId('export-csv-button'));
    });

    const blobContent = (blobSpy.mock.calls[0][0] as string[])[0];

    // Every required column must be present
    expect(blobContent).toContain('Staff Name');
    expect(blobContent).toContain('Timestamp');
    expect(blobContent).toContain('Event Type');
    expect(blobContent).toContain('Device');
    expect(blobContent).toContain('Location');
    expect(blobContent).toContain('Sync Status');
  });

  it('CSV contains correct row data for all passed events', async () => {
    const blobSpy = vi.spyOn(window, 'Blob');

    render(<ExportCSVButton rows={mockRows} />);

    await act(async () => {
      fireEvent.click(screen.getByTestId('export-csv-button'));
    });

    const blobContent = (blobSpy.mock.calls[0][0] as string[])[0];

    // Row 1 — John Doe
    expect(blobContent).toContain('John Doe');
    expect(blobContent).toContain('2026-05-21 08:00');
    expect(blobContent).toContain('CLOCK_IN');
    expect(blobContent).toContain('iPhone');
    expect(blobContent).toContain('Cape Town');
    expect(blobContent).toContain('SYNCED');

    // Row 2 — Aisha Patel
    expect(blobContent).toContain('Aisha Patel');
    expect(blobContent).toContain('2026-05-21 17:00');
    expect(blobContent).toContain('CLOCK_OUT');
    expect(blobContent).toContain('Android');
    expect(blobContent).toContain('Johannesburg');
    expect(blobContent).toContain('PENDING');
  });
});
