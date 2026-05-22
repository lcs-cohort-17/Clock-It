import { useState, useEffect, useRef } from 'react';
import { Download, Search, ChevronDown, Pencil, ArrowUpDown, AlertCircle, RefreshCw, Check } from 'lucide-react';
import ExportCSVButton from './ExportCSVButton';

export type Tab         = 'clock' | 'audit';
export type ClockType   = 'CLOCK_IN' | 'CLOCK_OUT' | 'BREAK_START' | 'BREAK_END';
export type SyncStatus  = 'SYNCED' | 'PENDING' | 'FAILED';
export type AuditAction = string;

export interface ClockEvent {
  id: string;
  staff: string;
  type: ClockType;
  timestamp: string;
  device: string;
  location: string;
  sync: SyncStatus;
  manuallyEdited?: boolean;
}

export interface AuditEvent {
  id: string;
  timestamp: string;
  actor: string;
  action: AuditAction;
  details?: string;
}

interface Props {
  clockEvents: ClockEvent[];
  auditEvents: AuditEvent[];
  staffOptions: string[];
  clockLoading: boolean;
  auditLoading: boolean;
  clockError: string | null;
  auditError: string | null;
  onRetryClockEvents: () => void;
  onRetryAuditEvents: () => void;
}

const STATUS_OPTIONS = ['All statuses', 'Clocked in', 'Clocked out', 'Pending'];

const C = {
  pageBg:      '#eef0f4',
  cardBg:      '#ffffff',
  border:      '#d0dae4',
  borderInput: '#c8d3de',
  focusRing:   '#1a3a5c',
  textPrimary: '#0d1f35',
  textSecond:  '#2a4860',
  textMuted:   '#637e96',
  green:       '#78a835',
  syncGreen:   '#2e7d32',
  syncAmber:   '#b45309',
  syncRed:     '#b91c1c',
} as const;

function downloadCSV(rows: string[][], filename: string) {
  const csv  = rows.map(r => r.map(v => `"${v}"`).join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv' });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href     = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
}

const card: React.CSSProperties = {
  background:   C.cardBg,
  border:       `1px solid ${C.border}`,
  borderRadius: '10px',
};

const thStyle: React.CSSProperties = {
  padding:       '12px 16px',
  textAlign:     'left',
  fontSize:      '11px',
  fontWeight:    600,
  color:         C.textMuted,
  letterSpacing: '0.08em',
  textTransform: 'uppercase',
  whiteSpace:    'nowrap',
};

const tdStyle: React.CSSProperties = {
  padding:   '14px 16px',
  fontSize:  '14px',
  color:     C.textSecond,
  textAlign: 'left',
  borderTop: `1px solid ${C.border}`,
};

interface DropdownProps {
  value: string;
  options: string[];
  onChange: (v: string) => void;
  testId?: string;
}

function CustomDropdown({ value, options, onChange, testId }: DropdownProps) {
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    function onOutsideClick(e: MouseEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) {
        setOpen(false);
      }
    }
    document.addEventListener('mousedown', onOutsideClick);
    return () => document.removeEventListener('mousedown', onOutsideClick);
  }, []);

  return (
    <div ref={ref} style={{ position: 'relative', flex: 1 }} data-testid={testId}>
      <button
        onClick={() => setOpen(o => !o)}
        aria-haspopup="listbox"
        aria-expanded={open}
        style={{
          width:          '100%',
          display:        'flex',
          alignItems:     'center',
          justifyContent: 'space-between',
          padding:        '10px 14px',
          background:     C.cardBg,
          border:         `1px solid ${C.borderInput}`,
          borderRadius:   '8px',
          fontSize:       '14px',
          color:          C.textPrimary,
          cursor:         'pointer',
          fontFamily:     'inherit',
          textAlign:      'left',
          outline:        'none',
          boxShadow:      open ? `0 0 0 2px ${C.focusRing}` : 'none',
          transition:     'box-shadow 0.15s',
        }}
      >
        <span>{value}</span>
        <ChevronDown size={14} style={{ color: C.textMuted, flexShrink: 0 }} />
      </button>

      {open && (
        <ul
          role="listbox"
          style={{
            position:     'absolute',
            top:          'calc(100% + 4px)',
            left:         0,
            right:        0,
            background:   C.cardBg,
            border:       `1px solid ${C.border}`,
            borderRadius: '8px',
            boxShadow:    '0 4px 12px rgba(13, 31, 53, 0.12)',
            zIndex:       50,
            overflow:     'hidden',
            listStyle:    'none',
            padding:      0,
            margin:       0,
          }}
        >
          {options.map(opt => {
            const isSelected = opt === value;
            return (
              <li
                key={opt}
                role="option"
                aria-selected={isSelected}
                onClick={() => { onChange(opt); setOpen(false); }}
                onMouseEnter={e => {
                  e.currentTarget.style.background = C.green;
                  e.currentTarget.style.color      = '#ffffff';
                }}
                onMouseLeave={e => {
                  e.currentTarget.style.background = isSelected ? C.green : 'transparent';
                  e.currentTarget.style.color      = isSelected ? '#ffffff' : C.textPrimary;
                }}
                style={{
                  display:    'flex',
                  alignItems: 'center',
                  gap:        '8px',
                  padding:    '10px 14px',
                  background: isSelected ? C.green : 'transparent',
                  color:      isSelected ? '#ffffff' : C.textPrimary,
                  fontSize:   '14px',
                  fontFamily: 'inherit',
                  cursor:     'pointer',
                  transition: 'background 0.1s',
                }}
              >
                <span style={{ width: '16px', flexShrink: 0 }}>
                  {isSelected && <Check size={13} strokeWidth={2.5} />}
                </span>
                {opt}
              </li>
            );
          })}
        </ul>
      )}
    </div>
  );
}

function SkeletonRows({ cols, rows = 4 }: { cols: number; rows?: number }) {
  return (
    <>
      {Array.from({ length: rows }).map((_, i) => (
        <tr key={i}>
          {Array.from({ length: cols }).map((_, j) => (
            <td key={j} style={tdStyle}>
              <div style={{
                height:         '12px',
                borderRadius:   '4px',
                background:     '#dce6f0',
                width:          `${55 + ((i * 3 + j * 7) % 35)}%`,
                animation:      'pulse 1.5s ease-in-out infinite',
                animationDelay: `${j * 80}ms`,
              }} />
            </td>
          ))}
        </tr>
      ))}
    </>
  );
}

function TableError({ cols, message, onRetry }: { cols: number; message: string; onRetry: () => void }) {
  return (
    <tr>
      <td colSpan={cols} style={{ padding: '56px 16px', textAlign: 'center' }}>
        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '12px' }}>
          <AlertCircle size={20} style={{ color: C.syncRed }} />
          <p style={{ margin: 0, fontSize: '14px', color: C.textMuted }}>{message}</p>
          <button
            onClick={onRetry}
            style={{
              display:      'inline-flex',
              alignItems:   'center',
              gap:          '6px',
              padding:      '6px 14px',
              background:   C.cardBg,
              border:       `1px solid ${C.borderInput}`,
              borderRadius: '6px',
              fontSize:     '13px',
              color:        C.textPrimary,
              cursor:       'pointer',
              fontFamily:   'inherit',
            }}
          >
            <RefreshCw size={12} />
            Retry
          </button>
        </div>
      </td>
    </tr>
  );
}

function ActionBadge({ action }: { action: AuditAction }) {
  return (
    <span style={{
      display:       'inline-block',
      padding:       '3px 10px',
      border:        `1px solid ${C.border}`,
      borderRadius:  '999px',
      fontSize:      '12px',
      fontWeight:    500,
      color:         C.textPrimary,
      background:    C.cardBg,
      letterSpacing: '0.02em',
    }}>
      {action}
    </span>
  );
}

export default function AttendanceLog({
  clockEvents,
  auditEvents,
  staffOptions,
  clockLoading,
  auditLoading,
  clockError,
  auditError,
  onRetryClockEvents,
  onRetryAuditEvents,
}: Props) {
  const [activeTab, setActiveTab]         = useState<Tab>('clock');
  const [search, setSearch]               = useState('');
  const [debouncedSearch, setDebounced]   = useState('');
  const [searchFocused, setSearchFocused] = useState(false);
  const [staffFilter, setStaffFilter]     = useState('All staff');
  const [statusFilter, setStatusFilter]   = useState('All statuses');
  const [auditSortAsc, setAuditSortAsc]   = useState(false);

  const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    if (debounceRef.current !== null) clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(() => setDebounced(search), 300);
    return () => {
      if (debounceRef.current !== null) clearTimeout(debounceRef.current);
    };
  }, [search]);

  const allStaffOptions = ['All staff', ...staffOptions];

  const filteredClockEvents = clockEvents.filter(event => {
    const q           = debouncedSearch.toLowerCase();
    const matchSearch = !q || event.staff.toLowerCase().includes(q) || event.location.toLowerCase().includes(q);
    const matchStaff  = staffFilter  === 'All staff'    || event.staff === staffFilter;
    const matchStatus = statusFilter === 'All statuses' ||
                       (statusFilter === 'Clocked in' && event.type === 'CLOCK_IN') ||
                       (statusFilter === 'Clocked out' && event.type === 'CLOCK_OUT') ||
                       (statusFilter === 'Pending' && event.sync === 'PENDING');
                       return matchSearch && matchStaff && matchStatus;
  });

  const sortedAudit = [...auditEvents].sort((a, b) =>
    auditSortAsc ? a.timestamp.localeCompare(b.timestamp) : b.timestamp.localeCompare(a.timestamp)
  );

  const handleExportAudit = () => {
    downloadCSV(
      [
        ['Timestamp', 'Actor', 'Action', 'Details'],
        ...sortedAudit.map(e => [e.timestamp, e.actor, e.action, e.details ?? '']),
      ],
      'audit_trail.csv'
    );
  };

  const outlineBtn: React.CSSProperties = {
    display:      'inline-flex',
    alignItems:   'center',
    gap:          '8px',
    padding:      '8px 16px',
    background:   C.cardBg,
    border:       `1px solid ${C.borderInput}`,
    borderRadius: '8px',
    fontSize:     '14px',
    fontWeight:   500,
    color:        C.textPrimary,
    cursor:       'pointer',
    fontFamily:   'inherit',
    whiteSpace:   'nowrap' as const,
    transition:   'background 0.15s, border-color 0.15s',
  };

  return (
    <div
      className="flex-1"
      style={{ background: C.pageBg, textAlign: 'left', minHeight: '100vh', boxSizing: 'border-box' }}
    >
      <div style={{ padding: '28px 32px' }}>

        <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: '16px', marginBottom: '24px', flexWrap: 'wrap' }}>
          <div>
            <h1 style={{ fontSize: '26px', fontWeight: 700, margin: 0, color: C.textPrimary, letterSpacing: '-0.02em', lineHeight: 1.2 }}>
              Attendance Logs
            </h1>
            <p style={{ margin: '4px 0 0', fontSize: '14px', color: C.textMuted }}>
              Full clock-event history with override and audit trail.
            </p>
          </div>
          <ExportCSVButton rows={filteredClockEvents} />
        </div>

        <div style={{ display: 'flex', gap: '4px', marginBottom: '20px' }}>
          {(['clock', 'audit'] as Tab[]).map(tab => {
            const isActive = activeTab === tab;
            return (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                style={{
                  padding:      '7px 18px',
                  borderRadius: '8px',
                  fontSize:     '14px',
                  fontWeight:   isActive ? 600 : 400,
                  border:       isActive ? `1px solid ${C.borderInput}` : '1px solid transparent',
                  background:   isActive ? C.cardBg : '#e2e8ef',
                  color:        isActive ? C.textPrimary : C.textMuted,
                  cursor:       'pointer',
                  fontFamily:   'inherit',
                  transition:   'all 0.15s',
                  outline:      'none',
                }}
              >
                {tab === 'clock' ? 'Clock events' : 'Audit trail'}
              </button>
            );
          })}
        </div>

        {activeTab === 'clock' && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>

            <div style={{ ...card, padding: '12px', display: 'flex', gap: '12px', alignItems: 'center' }}>

              <div
                data-testid="search-container"
                style={{
                  flex:         1,
                  display:      'flex',
                  alignItems:   'center',
                  gap:          '10px',
                  padding:      '10px 14px',
                  background:   C.cardBg,
                  border:       `1px solid ${C.borderInput}`,
                  borderRadius: '8px',
                  outline:      searchFocused ? `2px solid ${C.focusRing}` : 'none',
                  outlineOffset: '1px',
                  transition:   'outline 0.15s',
                }}
              >
                <Search size={15} style={{ color: C.textMuted, flexShrink: 0 }} />
                <input
                  type="text"
                  placeholder="Search name or location"
                  value={search}
                  onChange={e => setSearch(e.target.value)}
                  onFocus={() => setSearchFocused(true)}
                  onBlur={() => setSearchFocused(false)}
                  data-testid="search-input"
                  style={{
                    flex:       1,
                    border:     'none',
                    outline:    'none',
                    fontSize:   '14px',
                    color:      C.textPrimary,
                    background: 'transparent',
                    fontFamily: 'inherit',
                  }}
                />
              </div>

              <CustomDropdown
                value={staffFilter}
                options={allStaffOptions}
                onChange={setStaffFilter}
                testId="staff-dropdown"
              />

              <CustomDropdown
                value={statusFilter}
                options={STATUS_OPTIONS}
                onChange={setStatusFilter}
                testId="status-dropdown"
              />
            </div>

            <div style={{ ...card, overflowX: 'auto' }}>
              <table style={{ width: '100%', minWidth: '680px', borderCollapse: 'collapse' }}>
                <thead>
                  <tr style={{ borderBottom: `1px solid ${C.border}` }}>
                    {['STAFF', 'TYPE', 'TIMESTAMP', 'DEVICE', 'LOCATION', 'SYNC', 'ACTIONS'].map(col => (
                      <th key={col} style={thStyle}>{col}</th>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  {clockLoading && <SkeletonRows cols={7} rows={5} />}

                  {!clockLoading && clockError && (
                    <TableError cols={7} message={clockError} onRetry={onRetryClockEvents} />
                  )}

                  {!clockLoading && !clockError && filteredClockEvents.length === 0 && (
                    <tr>
                      <td colSpan={7} style={{ padding: '56px 16px', textAlign: 'center', fontSize: '14px', color: C.textMuted }}>
                        No records.
                      </td>
                    </tr>
                  )}

                  {!clockLoading && !clockError && filteredClockEvents.map(event => (
                    <tr
                      key={event.id}
                      onMouseEnter={e => (e.currentTarget.style.background = '#f5f8fc')}
                      onMouseLeave={e => (e.currentTarget.style.background = 'transparent')}
                    >
                      <td style={{ ...tdStyle, color: C.textPrimary, fontWeight: 500 }}>
                        <span style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                          {event.staff}
                          {event.manuallyEdited && (
                            <span title="Manually edited by admin">
                              <Pencil size={11} style={{ color: C.syncAmber }} />
                            </span>
                          )}
                        </span>
                      </td>
                      <td style={tdStyle}>{event.type.replace(/_/g, ' ')}</td>
                      <td style={{ ...tdStyle, whiteSpace: 'nowrap' }}>{event.timestamp}</td>
                      <td style={tdStyle}>{event.device}</td>
                      <td style={tdStyle}>{event.location}</td>
                      <td style={tdStyle}>
                        <span style={{
                          fontSize:   '13px',
                          fontWeight: 500,
                          color: event.sync === 'SYNCED' ? C.syncGreen : event.sync === 'PENDING' ? C.syncAmber : C.syncRed,
                        }}>
                          {event.sync}
                        </span>
                      </td>
                      <td style={tdStyle}>
                        <button style={{ fontSize: '13px', color: '#2563eb', background: 'none', border: 'none', cursor: 'pointer', padding: 0, fontFamily: 'inherit' }}>
                          Edit
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {activeTab === 'audit' && (
          <div>
            <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: '16px' }}>
              <button
                onClick={handleExportAudit}
                disabled={auditLoading || !!auditError}
                style={{ ...outlineBtn, opacity: (auditLoading || !!auditError) ? 0.45 : 1 }}
                onMouseEnter={e => {
                  e.currentTarget.style.background  = C.green;
                  e.currentTarget.style.borderColor = C.green;
                  e.currentTarget.style.color       = '#ffffff';
                }}
                onMouseLeave={e => {
                  e.currentTarget.style.background  = C.cardBg;
                  e.currentTarget.style.borderColor = C.borderInput;
                  e.currentTarget.style.color       = C.textPrimary;
                }}
              >
                <Download size={14} />
                Export audit
              </button>
            </div>

            <div style={{ ...card, overflowX: 'auto' }}>
              <table style={{ width: '100%', minWidth: '480px', borderCollapse: 'collapse' }}>
                <thead>
                  <tr style={{ borderBottom: `1px solid ${C.border}` }}>
                    <th
                      data-testid="timestamp-sort"
                      style={{ ...thStyle, cursor: 'pointer', userSelect: 'none' }}
                      onClick={() => setAuditSortAsc(p => !p)}
                    >
                      <span style={{ display: 'inline-flex', alignItems: 'center', gap: '4px' }}>
                        TIMESTAMP
                        <ArrowUpDown size={10} style={{ opacity: 0.5 }} />
                      </span>
                    </th>
                    {['ACTOR', 'ACTION', 'DETAILS'].map(col => (
                      <th key={col} style={thStyle}>{col}</th>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  {auditLoading && <SkeletonRows cols={4} rows={5} />}

                  {!auditLoading && auditError && (
                    <TableError cols={4} message={auditError} onRetry={onRetryAuditEvents} />
                  )}

                  {!auditLoading && !auditError && sortedAudit.map(event => (
                    <tr
                      key={event.id}
                      onMouseEnter={e => (e.currentTarget.style.background = '#f5f8fc')}
                      onMouseLeave={e => (e.currentTarget.style.background = 'transparent')}
                    >
                      <td style={{ ...tdStyle, whiteSpace: 'nowrap' }}>{event.timestamp}</td>
                      <td style={{ ...tdStyle, color: C.textPrimary }}>{event.actor}</td>
                      <td style={tdStyle}><ActionBadge action={event.action} /></td>
                      <td style={{ ...tdStyle, color: C.textMuted }}>{event.details ?? '—'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

      </div>
    </div>
  );
}
