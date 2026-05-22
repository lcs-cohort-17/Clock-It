import { useState, useCallback, useEffect } from 'react';
import AttendanceLog, { ClockEvent, AuditEvent } from '../components/AttendanceLog';

interface FetchState<T> {
  data: T[];
  loading: boolean;
  error: string | null;
}

export default function AttendanceLogPage() {
  const [clockState, setClockState] = useState<FetchState<ClockEvent>>({
    data: [], loading: true, error: null,
  });

  const [auditState, setAuditState] = useState<FetchState<AuditEvent>>({
    data: [], loading: true, error: null,
  });

  const [staffOptions, setStaffOptions] = useState<string[]>([]);

  const fetchClockEvents = useCallback(async () => {
    setClockState(s => ({ ...s, loading: true, error: null }));
    try {
      // Replace with your real endpoint:
      // const res = await fetch('/api/attendance/clock-events');
      // if (!res.ok) throw new Error('Failed to load clock events');
      // const data: ClockEvent[] = await res.json();
      const data: ClockEvent[] = [];
      setClockState({ data, loading: false, error: null });
    } catch (_err) {
      setClockState({ data: [], loading: false, error: 'Failed to load clock events. Please try again.' });
    }
  }, []);

  const fetchAuditEvents = useCallback(async () => {
    setAuditState(s => ({ ...s, loading: true, error: null }));
    try {
      // Replace with your real endpoint:
      // const res = await fetch('/api/attendance/audit');
      // if (!res.ok) throw new Error('Failed to load audit trail');
      // const data: AuditEvent[] = await res.json();
      const data: AuditEvent[] = [];
      setAuditState({ data, loading: false, error: null });
    } catch (_err) {
      setAuditState({ data: [], loading: false, error: 'Failed to load audit trail. Please try again.' });
    }
  }, []);

  const fetchStaffOptions = useCallback(async () => {
    try {
      // Replace with your real endpoint:
      // const res = await fetch('/api/staff');
      // if (!res.ok) throw new Error('Failed to load staff');
      // const data: { name: string }[] = await res.json();
      // setStaffOptions(data.map(s => s.name));
      setStaffOptions([]);
    } catch (_err) {
      setStaffOptions([]);
    }
  }, []);

  useEffect(() => {
    fetchClockEvents();
    fetchAuditEvents();
    fetchStaffOptions();
  }, [fetchClockEvents, fetchAuditEvents, fetchStaffOptions]);

  return (
    <AttendanceLog
      clockEvents={clockState.data}
      auditEvents={auditState.data}
      staffOptions={staffOptions}
      clockLoading={clockState.loading}
      auditLoading={auditState.loading}
      clockError={clockState.error}
      auditError={auditState.error}
      onRetryClockEvents={fetchClockEvents}
      onRetryAuditEvents={fetchAuditEvents}
    />
  );
}