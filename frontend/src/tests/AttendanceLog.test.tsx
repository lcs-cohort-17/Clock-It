import { act, render, screen, fireEvent } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import AttendanceLogPage from '../pages/AttendanceLogPage';
import AttendanceLog from '../components/AttendanceLog';

function advance() {
  act(() => {
    vi.advanceTimersByTime(400);
  });
}

describe('AttendanceLog (Page + Component Integration)', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
    vi.restoreAllMocks();
  });

  describe('Page rendering', () => {
    it('renders page heading and subtitle', () => {
      render(<AttendanceLogPage />);
      expect(screen.getByText('Attendance Logs')).toBeInTheDocument();
      expect(
        screen.getByText('Full clock-event history with override and audit trail.')
      ).toBeInTheDocument();
    });

    it('defaults to clock events tab', () => {
      render(<AttendanceLogPage />);
      expect(screen.getByText('Clock events')).toBeInTheDocument();
    });
  });

  describe('Clock Events UI', () => {
    it('renders search input and filters', () => {
      render(<AttendanceLogPage />);
      expect(screen.getByTestId('search-input')).toBeInTheDocument();
      expect(screen.getByText('All staff')).toBeInTheDocument();
      expect(screen.getByText('All statuses')).toBeInTheDocument();
    });

    it('search input updates value', () => {
      render(<AttendanceLogPage />);
      const input = screen.getByTestId('search-input') as HTMLInputElement;

      fireEvent.change(input, { target: { value: 'John' } });

      expect(input.value).toBe('John');
    });

    it('filters results to empty state when no match', async () => {
      render(<AttendanceLogPage />);

      const input = screen.getByTestId('search-input');
      fireEvent.change(input, { target: { value: 'zzzz-invalid' } });

      advance();

      expect(screen.getByText('No records.')).toBeInTheDocument();
    });
  });

  describe('Tab switching', () => {
    it('switches to audit trail tab', () => {
      render(<AttendanceLogPage />);

      fireEvent.click(screen.getByText('Audit trail'));

      expect(screen.getByText('Export audit')).toBeInTheDocument();
    });

    it('audit table shows sortable header', () => {
      render(<AttendanceLogPage />);

      fireEvent.click(screen.getByText('Audit trail'));

      expect(screen.getByTestId('timestamp-sort')).toBeInTheDocument();
    });
  });

  describe('Audit Export', () => {
    it('export button exists and can be clicked', () => {
      render(<AttendanceLogPage />);

      fireEvent.click(screen.getByText('Audit trail'));

      const btn = screen.getByRole('button', { name: /export audit/i });

      expect(btn).toBeInTheDocument();

      fireEvent.click(btn);
    });
  });

  describe('Manual edit indicator', () => {
    it('renders without crashing for edited rows', () => {
      const mock = [
        {
          id: '1',
          staff: 'John',
          type: 'CLOCK_IN',
          timestamp: '2026-01-01',
          device: 'iPhone',
          location: 'Office',
          sync: 'SYNCED',
          manuallyEdited: true,
        },
      ];

      render(
        <AttendanceLog
          clockEvents={mock}
          auditEvents={[]}
          staffOptions={['John']}
          clockLoading={false}
          auditLoading={false}
          clockError={null}
          auditError={null}
          onRetryClockEvents={() => {}}
          onRetryAuditEvents={() => {}}
        />
      );

      expect(screen.getByText('John')).toBeInTheDocument();
    });
  });
});
