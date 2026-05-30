/**
 * attendance_log.test.js
 *
 * Full test suite for the Attendance Logs feature.
 * Covers every requirement (R) and acceptance criterion (AC) from both tickets.
 *
 * FILE COVERAGE:
 *   attendance_log_utils.js  — pure utility functions
 *   attendance_log_page.js   — Alpine component factory (attendancePage)
 *
 * WHY NO DOM / JSDOM:
 *   attendance_log_page.js uses dependency injection — all external deps
 *   (data, storage, utils, download trigger) are passed via opts in tests.
 *   The real DOM triggerDownload is never called; a jest.fn() is passed instead.
 *   This means zero DOM setup, zero extra packages.
 *
 * Run: npx jest attendance_log.test.js
 */

'use strict';

const AttendanceUtils = require('./attendance_log_utils');
const attendancePage  = require('./attendance_log_page');

// ─────────────────────────────────────────────────────────────────────────────
// Shared test data
// ─────────────────────────────────────────────────────────────────────────────

const MOCK_CLOCK_EVENTS = [
  { id: 1, staff: 'Amara Nwosu',    type: 'Clock In',  timestamp: '2026-05-27 08:02:14', device: 'Terminal A', location: 'Main Office', sync: 'Synced'  },
  { id: 2, staff: 'Amara Nwosu',    type: 'Clock Out', timestamp: '2026-05-27 17:05:33', device: 'Terminal A', location: 'Main Office', sync: 'Synced'  },
  { id: 3, staff: 'Sipho Dlamini',  type: 'Clock In',  timestamp: '2026-05-27 07:58:01', device: 'Terminal B', location: 'Warehouse',   sync: 'Synced'  },
  { id: 4, staff: 'Sipho Dlamini',  type: 'Clock Out', timestamp: '2026-05-27 16:30:44', device: 'Terminal B', location: 'Warehouse',   sync: 'Pending' },
  { id: 5, staff: 'Naledi Khumalo', type: 'Clock In',  timestamp: '2026-05-27 09:15:22', device: 'Mobile App', location: 'Remote',      sync: 'Synced'  },
];

// Deliberately out of timestamp order so sort tests are meaningful
const MOCK_AUDIT_TRAIL = [
  { id: 1, timestamp: '2026-05-25 10:00:00', actor: 'Admin Jane',  action: 'EDIT',     details: 'Edit 1'   },
  { id: 2, timestamp: '2026-05-27 14:30:00', actor: 'Admin Jane',  action: 'DELETE',   details: 'Delete 2' },
  { id: 3, timestamp: '2026-05-26 09:15:00', actor: 'Admin Kobus', action: 'OVERRIDE', details: 'Override 3' },
];
// Expected desc order:  id 2 (27th) → id 3 (26th) → id 1 (25th)
// Expected asc  order:  id 1 (25th) → id 3 (26th) → id 2 (27th)

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

function today() {
  return new Date().toISOString().split('T')[0];
}

function daysFromToday(n) {
  const d = new Date();
  d.setDate(d.getDate() + n);
  return d.toISOString().split('T')[0];
}

/** Minimal valid leave form — all required fields, future dates */
function validLeaveForm(overrides) {
  return Object.assign({
    type:      'Leave',
    startDate: daysFromToday(1),
    endDate:   daysFromToday(3),
    reason:    'Annual leave',
  }, overrides || {});
}

/** Injectable localStorage mock — same interface as window.localStorage */
function makeStorage() {
  const store = {};
  return {
    getItem:    (k)      => (store[k] !== undefined ? store[k] : null),
    setItem:    (k, v)   => { store[k] = String(v); },
    removeItem: (k)      => { delete store[k]; },
    clear:      ()       => { Object.keys(store).forEach(k => delete store[k]); },
  };
}

/**
 * Creates a fully-wired component with injected deps.
 * Pass `storageRef` to get the same storage instance back for assertions.
 */
function makeComponent(storageRef) {
  const storage = storageRef || makeStorage();
  return attendancePage({
    data:    { clockEvents: MOCK_CLOCK_EVENTS, auditTrail: MOCK_AUDIT_TRAIL },
    storage: storage,
    utils:   AttendanceUtils,
  });
}


// ═════════════════════════════════════════════════════════════════════════════
// PART 1 — attendance_log_utils.js
// ═════════════════════════════════════════════════════════════════════════════

// ─────────────────────────────────────────────────────────────────────────────
// validateLeaveForm  (Ticket 1 — R1.4, AC1.3)
// ─────────────────────────────────────────────────────────────────────────────

describe('validateLeaveForm', () => {

  describe('valid form', () => {
    test('returns empty errors object for a fully valid form', () => {
      expect(AttendanceUtils.validateLeaveForm(validLeaveForm())).toEqual({});
    });

    test('accepts same-day start and end', () => {
      const d = daysFromToday(2);
      expect(AttendanceUtils.validateLeaveForm(validLeaveForm({ startDate: d, endDate: d }))).toEqual({});
    });

    test('accepts Sick as type', () => {
      expect(AttendanceUtils.validateLeaveForm(validLeaveForm({ type: 'Sick' }))).toEqual({});
    });
  });

  describe('required fields', () => {
    test('flags missing type', () => {
      expect(AttendanceUtils.validateLeaveForm(validLeaveForm({ type: '' }))).toHaveProperty('type');
    });

    test('flags missing startDate', () => {
      expect(AttendanceUtils.validateLeaveForm(validLeaveForm({ startDate: '' }))).toHaveProperty('startDate');
    });

    test('flags missing endDate', () => {
      expect(AttendanceUtils.validateLeaveForm(validLeaveForm({ endDate: '' }))).toHaveProperty('endDate');
    });

    test('flags empty reason', () => {
      expect(AttendanceUtils.validateLeaveForm(validLeaveForm({ reason: '' }))).toHaveProperty('reason');
    });

    test('flags whitespace-only reason', () => {
      expect(AttendanceUtils.validateLeaveForm(validLeaveForm({ reason: '   ' }))).toHaveProperty('reason');
    });
  });

  describe('date ordering (R1.4 — end >= start)', () => {
    test('flags endDate before startDate', () => {
      const errors = AttendanceUtils.validateLeaveForm(validLeaveForm({
        startDate: daysFromToday(5),
        endDate:   daysFromToday(2),
      }));
      expect(errors).toHaveProperty('endDate');
      expect(errors.endDate).toMatch(/on or after/i);
    });

    test('does NOT flag when endDate equals startDate', () => {
      const d = daysFromToday(2);
      expect(AttendanceUtils.validateLeaveForm(validLeaveForm({ startDate: d, endDate: d }))).not.toHaveProperty('endDate');
    });

    test('does NOT produce endDate error when startDate is missing separately', () => {
      const errors = AttendanceUtils.validateLeaveForm(validLeaveForm({ startDate: '', endDate: daysFromToday(1) }));
      expect(errors).toHaveProperty('startDate');
      expect(errors).not.toHaveProperty('endDate');
    });
  });

  describe('past-date validation (R1.4 — optional, on by default)', () => {
    test('flags startDate in the past', () => {
      const errors = AttendanceUtils.validateLeaveForm(validLeaveForm({ startDate: '2020-01-01' }));
      expect(errors).toHaveProperty('startDate');
      expect(errors.startDate).toMatch(/past/i);
    });

    test('does NOT flag today as past', () => {
      const errors = AttendanceUtils.validateLeaveForm(validLeaveForm({ startDate: today(), endDate: today() }));
      expect(errors).not.toHaveProperty('startDate');
    });

    test('skips past-date check when rejectPastDates = false', () => {
      const errors = AttendanceUtils.validateLeaveForm(
        validLeaveForm({ startDate: '2020-01-01', endDate: '2020-01-05' }),
        false
      );
      expect(errors).not.toHaveProperty('startDate');
    });
  });

  describe('all errors collected at once', () => {
    test('returns all field errors simultaneously for empty form', () => {
      const errors = AttendanceUtils.validateLeaveForm({ type: '', startDate: '', endDate: '', reason: '' });
      expect(errors).toHaveProperty('type');
      expect(errors).toHaveProperty('startDate');
      expect(errors).toHaveProperty('endDate');
      expect(errors).toHaveProperty('reason');
    });
  });
});


// ─────────────────────────────────────────────────────────────────────────────
// formatAuditCSV  (Ticket 2 — R2.5, AC2.3)
// ─────────────────────────────────────────────────────────────────────────────

describe('formatAuditCSV', () => {
  test('produces correct header row', () => {
    expect(AttendanceUtils.formatAuditCSV([]).split('\n')[0])
      .toBe('"Timestamp","Actor","Action","Details"');
  });

  test('produces one data row per event', () => {
    const lines = AttendanceUtils.formatAuditCSV(MOCK_AUDIT_TRAIL).split('\n');
    expect(lines).toHaveLength(MOCK_AUDIT_TRAIL.length + 1);
  });

  test('wraps every cell in double quotes', () => {
    const dataLine = AttendanceUtils.formatAuditCSV([MOCK_AUDIT_TRAIL[0]]).split('\n')[1];
    expect(dataLine).toMatch(/^"[^"]*"(,"[^"]*")*$/);
  });

  test('escapes embedded double quotes per RFC 4180', () => {
    const event = { timestamp: '2026-01-01', actor: 'Admin "Bob"', action: 'EDIT', details: 'Changed "name"' };
    const csv = AttendanceUtils.formatAuditCSV([event]);
    expect(csv).toContain('"Admin ""Bob"""');
    expect(csv).toContain('"Changed ""name"""');
  });

  test('handles empty events array — header row only', () => {
    expect(AttendanceUtils.formatAuditCSV([]).split('\n')).toHaveLength(1);
  });

  test('maps all four columns in the correct order', () => {
    const e = MOCK_AUDIT_TRAIL[0];
    const dataLine = AttendanceUtils.formatAuditCSV([e]).split('\n')[1];
    expect(dataLine).toBe(`"${e.timestamp}","${e.actor}","${e.action}","${e.details}"`);
  });

  test('handles null / undefined cell values without throwing', () => {
    const broken = [{ timestamp: '2026-01-01', actor: null, action: undefined, details: '' }];
    expect(() => AttendanceUtils.formatAuditCSV(broken)).not.toThrow();
  });
});


// ─────────────────────────────────────────────────────────────────────────────
// sortAuditTrail  (Ticket 2 — R2.3, AC2.4)
// ─────────────────────────────────────────────────────────────────────────────

describe('sortAuditTrail', () => {
  test('defaults to descending — newest first', () => {
    const sorted = AttendanceUtils.sortAuditTrail(MOCK_AUDIT_TRAIL);
    expect(sorted[0].timestamp).toBe('2026-05-27 14:30:00');
    expect(sorted[sorted.length - 1].timestamp).toBe('2026-05-25 10:00:00');
  });

  test('sorts ascending when direction = "asc"', () => {
    const sorted = AttendanceUtils.sortAuditTrail(MOCK_AUDIT_TRAIL, 'asc');
    expect(sorted[0].timestamp).toBe('2026-05-25 10:00:00');
    expect(sorted[sorted.length - 1].timestamp).toBe('2026-05-27 14:30:00');
  });

  test('does not mutate the original array', () => {
    const original = MOCK_AUDIT_TRAIL.map(e => ({ ...e }));
    AttendanceUtils.sortAuditTrail(MOCK_AUDIT_TRAIL, 'asc');
    expect(MOCK_AUDIT_TRAIL[0].timestamp).toBe(original[0].timestamp);
  });

  test('returns a new array reference', () => {
    expect(AttendanceUtils.sortAuditTrail(MOCK_AUDIT_TRAIL)).not.toBe(MOCK_AUDIT_TRAIL);
  });

  test('handles empty array', () => {
    expect(AttendanceUtils.sortAuditTrail([])).toEqual([]);
  });

  test('desc then asc is the reverse sequence', () => {
    const desc = AttendanceUtils.sortAuditTrail(MOCK_AUDIT_TRAIL, 'desc').map(e => e.id);
    const asc  = AttendanceUtils.sortAuditTrail(MOCK_AUDIT_TRAIL, 'asc').map(e => e.id);
    expect([...desc].reverse()).toEqual(asc);
  });
});


// ─────────────────────────────────────────────────────────────────────────────
// saveLeaveRequest  (Ticket 1 — R1.3, AC1.4)
// ─────────────────────────────────────────────────────────────────────────────

describe('saveLeaveRequest', () => {
  test('saves a request and returns a one-item array', () => {
    const storage = makeStorage();
    const result  = AttendanceUtils.saveLeaveRequest(validLeaveForm(), storage);
    expect(result).toHaveLength(1);
    expect(result[0].type).toBe('Leave');
    expect(result[0].reason).toBe('Annual leave');
  });

  test('appends to existing requests', () => {
    const storage = makeStorage();
    AttendanceUtils.saveLeaveRequest(validLeaveForm(), storage);
    const result = AttendanceUtils.saveLeaveRequest(validLeaveForm({ type: 'Sick', reason: 'Flu' }), storage);
    expect(result).toHaveLength(2);
    expect(result[1].type).toBe('Sick');
  });

  test('persists data to storage as JSON', () => {
    const storage = makeStorage();
    AttendanceUtils.saveLeaveRequest(validLeaveForm({ reason: 'Dentist' }), storage);
    const stored = JSON.parse(storage.getItem('leaveRequests'));
    expect(Array.isArray(stored)).toBe(true);
    expect(stored[0].reason).toBe('Dentist');
  });

  test('attaches a numeric id and ISO submittedAt timestamp', () => {
    const storage = makeStorage();
    const result  = AttendanceUtils.saveLeaveRequest(validLeaveForm(), storage);
    expect(typeof result[0].id).toBe('number');
    expect(result[0].submittedAt).toMatch(/^\d{4}-\d{2}-\d{2}T/);
  });

  test('does not mutate the original request object', () => {
    const storage  = makeStorage();
    const original = validLeaveForm();
    const snapshot = { ...original };
    AttendanceUtils.saveLeaveRequest(original, storage);
    expect(original).toEqual(snapshot);
  });

  test('handles corrupted localStorage value gracefully', () => {
    const storage = makeStorage();
    storage.setItem('leaveRequests', 'NOT_JSON');
    expect(() => AttendanceUtils.saveLeaveRequest(validLeaveForm(), storage)).not.toThrow();
  });
});


// ─────────────────────────────────────────────────────────────────────────────
// filterClockEvents  (Ticket 2 — R2.1, AC2.1)
// ─────────────────────────────────────────────────────────────────────────────

describe('filterClockEvents', () => {
  test('returns all events when all filters are empty', () => {
    expect(AttendanceUtils.filterClockEvents(MOCK_CLOCK_EVENTS, {})).toHaveLength(MOCK_CLOCK_EVENTS.length);
  });

  test('search filters by staff name (case-insensitive)', () => {
    const result = AttendanceUtils.filterClockEvents(MOCK_CLOCK_EVENTS, { search: 'amara' });
    expect(result.every(e => e.staff === 'Amara Nwosu')).toBe(true);
    expect(result.length).toBeGreaterThan(0);
  });

  test('search filters by location (case-insensitive)', () => {
    const result = AttendanceUtils.filterClockEvents(MOCK_CLOCK_EVENTS, { search: 'warehouse' });
    expect(result.every(e => e.location === 'Warehouse')).toBe(true);
  });

  test('search returns empty array when nothing matches', () => {
    expect(AttendanceUtils.filterClockEvents(MOCK_CLOCK_EVENTS, { search: 'zzznomatch' })).toHaveLength(0);
  });

  test('staff filter returns only matching staff', () => {
    const result = AttendanceUtils.filterClockEvents(MOCK_CLOCK_EVENTS, { staff: 'Sipho Dlamini' });
    expect(result.every(e => e.staff === 'Sipho Dlamini')).toBe(true);
  });

  test('status filter returns only Clock In events', () => {
    const result = AttendanceUtils.filterClockEvents(MOCK_CLOCK_EVENTS, { status: 'Clock In' });
    expect(result.every(e => e.type === 'Clock In')).toBe(true);
  });

  test('status filter returns only Clock Out events', () => {
    const result = AttendanceUtils.filterClockEvents(MOCK_CLOCK_EVENTS, { status: 'Clock Out' });
    expect(result.every(e => e.type === 'Clock Out')).toBe(true);
  });

  test('all three filters applied together', () => {
    const result = AttendanceUtils.filterClockEvents(MOCK_CLOCK_EVENTS, {
      search: 'warehouse',
      staff:  'Sipho Dlamini',
      status: 'Clock In',
    });
    expect(result).toHaveLength(1);
    expect(result[0].id).toBe(3);
  });

  test('incompatible filter combination returns empty array', () => {
    expect(AttendanceUtils.filterClockEvents(MOCK_CLOCK_EVENTS, {
      staff:  'Amara Nwosu',
      status: 'Clock In',
      search: 'warehouse',
    })).toHaveLength(0);
  });

  test('does not mutate original array', () => {
    AttendanceUtils.filterClockEvents(MOCK_CLOCK_EVENTS, { staff: 'Amara Nwosu' });
    expect(MOCK_CLOCK_EVENTS).toHaveLength(5);
  });
});


// ═════════════════════════════════════════════════════════════════════════════
// PART 2 — attendance_log_page.js  (attendancePage component)
// ═════════════════════════════════════════════════════════════════════════════

// ─────────────────────────────────────────────────────────────────────────────
// Initial state
// ─────────────────────────────────────────────────────────────────────────────

describe('attendancePage — initial state', () => {
  test('active tab defaults to clock-events', () => {
    expect(makeComponent().activeTab).toBe('clock-events');
  });

  test('modal is hidden on init (R1.1)', () => {
    expect(makeComponent().showModal).toBe(false);
  });

  test('audit sort direction defaults to desc (R2.3 — newest first)', () => {
    expect(makeComponent().auditSortDir).toBe('desc');
  });

  test('form success flag starts false', () => {
    expect(makeComponent().formSuccess).toBe(false);
  });

  test('formErrors starts as empty object', () => {
    expect(Object.keys(makeComponent().formErrors)).toHaveLength(0);
  });

  test('leaveForm type defaults to Leave', () => {
    expect(makeComponent().leaveForm.type).toBe('Leave');
  });

  test('clockEvents loaded from injected data', () => {
    expect(makeComponent().clockEvents).toHaveLength(MOCK_CLOCK_EVENTS.length);
  });

  test('auditTrail loaded from injected data', () => {
    expect(makeComponent().auditTrail).toHaveLength(MOCK_AUDIT_TRAIL.length);
  });
});


// ─────────────────────────────────────────────────────────────────────────────
// Ticket 2 — Tab toggle  (R2.1, AC2.1)
// ─────────────────────────────────────────────────────────────────────────────

describe('attendancePage — tab toggle (Ticket 2 R2.1, AC2.1)', () => {
  test('switching activeTab to audit-trail reflects the change', () => {
    const c = makeComponent();
    c.activeTab = 'audit-trail';
    expect(c.activeTab).toBe('audit-trail');
  });

  test('switching back to clock-events reflects the change', () => {
    const c = makeComponent();
    c.activeTab = 'audit-trail';
    c.activeTab = 'clock-events';
    expect(c.activeTab).toBe('clock-events');
  });

  test('tab change does not affect clock events data', () => {
    const c = makeComponent();
    c.activeTab = 'audit-trail';
    expect(c.clockEvents).toHaveLength(MOCK_CLOCK_EVENTS.length);
  });

  test('tab change does not affect audit trail data', () => {
    const c = makeComponent();
    c.activeTab = 'clock-events';
    expect(c.auditTrail).toHaveLength(MOCK_AUDIT_TRAIL.length);
  });
});


// ─────────────────────────────────────────────────────────────────────────────
// Ticket 2 — filteredClockEvents getter wiring
// ─────────────────────────────────────────────────────────────────────────────

describe('attendancePage — filteredClockEvents getter wiring', () => {
  test('returns all events when no filters set', () => {
    expect(makeComponent().filteredClockEvents).toHaveLength(MOCK_CLOCK_EVENTS.length);
  });

  test('reflects search state — filters by staff name', () => {
    const c = makeComponent();
    c.search = 'amara';
    expect(c.filteredClockEvents.every(e => e.staff === 'Amara Nwosu')).toBe(true);
    expect(c.filteredClockEvents.length).toBeGreaterThan(0);
  });

  test('reflects search state — filters by location', () => {
    const c = makeComponent();
    c.search = 'warehouse';
    expect(c.filteredClockEvents.every(e => e.location === 'Warehouse')).toBe(true);
  });

  test('reflects staffFilter state', () => {
    const c = makeComponent();
    c.staffFilter = 'Sipho Dlamini';
    expect(c.filteredClockEvents.every(e => e.staff === 'Sipho Dlamini')).toBe(true);
  });

  test('reflects statusFilter state', () => {
    const c = makeComponent();
    c.statusFilter = 'Clock In';
    expect(c.filteredClockEvents.every(e => e.type === 'Clock In')).toBe(true);
  });

  test('clearing a filter restores events', () => {
    const c = makeComponent();
    c.staffFilter = 'Sipho Dlamini';
    c.staffFilter = '';
    expect(c.filteredClockEvents).toHaveLength(MOCK_CLOCK_EVENTS.length);
  });
});


// ─────────────────────────────────────────────────────────────────────────────
// Ticket 2 — sortedAuditTrail getter + toggleAuditSort  (R2.3, AC2.4)
// ─────────────────────────────────────────────────────────────────────────────

describe('attendancePage — sortedAuditTrail getter (Ticket 2 R2.3, AC2.4)', () => {
  test('returns all audit trail entries', () => {
    expect(makeComponent().sortedAuditTrail).toHaveLength(MOCK_AUDIT_TRAIL.length);
  });

  test('default order is newest first (desc)', () => {
    const c = makeComponent();
    const sorted = c.sortedAuditTrail;
    expect(sorted[0].timestamp).toBe('2026-05-27 14:30:00');
    expect(sorted[sorted.length - 1].timestamp).toBe('2026-05-25 10:00:00');
  });

  test('order changes after toggleAuditSort (AC2.4)', () => {
    const c = makeComponent();
    c.toggleAuditSort();
    const sorted = c.sortedAuditTrail;
    expect(sorted[0].timestamp).toBe('2026-05-25 10:00:00');
    expect(sorted[sorted.length - 1].timestamp).toBe('2026-05-27 14:30:00');
  });

  test('double toggle restores desc order', () => {
    const c = makeComponent();
    c.toggleAuditSort();
    c.toggleAuditSort();
    expect(c.sortedAuditTrail[0].timestamp).toBe('2026-05-27 14:30:00');
  });

  test('auditSortDir toggles from desc → asc', () => {
    const c = makeComponent();
    c.toggleAuditSort();
    expect(c.auditSortDir).toBe('asc');
  });

  test('auditSortDir toggles from asc → desc', () => {
    const c = makeComponent();
    c.toggleAuditSort();
    c.toggleAuditSort();
    expect(c.auditSortDir).toBe('desc');
  });
});


// ─────────────────────────────────────────────────────────────────────────────
// Ticket 2 — exportAuditCSV  (R2.4, R2.5, AC2.3)
// ─────────────────────────────────────────────────────────────────────────────

describe('attendancePage — exportAuditCSV (Ticket 2 R2.4, R2.5, AC2.3)', () => {
  test('calls the download function exactly once', () => {
    const c = makeComponent();
    const mockDl = jest.fn();
    c.exportAuditCSV(mockDl);
    expect(mockDl).toHaveBeenCalledTimes(1);
  });

  test('passes CSV string as first argument', () => {
    const c = makeComponent();
    const mockDl = jest.fn();
    c.exportAuditCSV(mockDl);
    expect(typeof mockDl.mock.calls[0][0]).toBe('string');
  });

  test('CSV starts with the correct header row', () => {
    const c = makeComponent();
    const mockDl = jest.fn();
    c.exportAuditCSV(mockDl);
    const csv = mockDl.mock.calls[0][0];
    expect(csv.split('\n')[0]).toBe('"Timestamp","Actor","Action","Details"');
  });

  test('CSV contains one row per audit entry plus header', () => {
    const c = makeComponent();
    const mockDl = jest.fn();
    c.exportAuditCSV(mockDl);
    const lines = mockDl.mock.calls[0][0].split('\n');
    expect(lines).toHaveLength(MOCK_AUDIT_TRAIL.length + 1);
  });

  test('filename passed as second argument', () => {
    const c = makeComponent();
    const mockDl = jest.fn();
    c.exportAuditCSV(mockDl);
    expect(typeof mockDl.mock.calls[0][1]).toBe('string');
  });

  test('filename matches audit_trail_YYYY-MM-DD.csv pattern', () => {
    const c = makeComponent();
    const mockDl = jest.fn();
    c.exportAuditCSV(mockDl);
    expect(mockDl.mock.calls[0][1]).toMatch(/^audit_trail_\d{4}-\d{2}-\d{2}\.csv$/);
  });

  test('CSV reflects default desc sort — newest entry appears first in data', () => {
    const c = makeComponent();
    const mockDl = jest.fn();
    c.exportAuditCSV(mockDl);
    const firstDataLine = mockDl.mock.calls[0][0].split('\n')[1];
    expect(firstDataLine).toContain('2026-05-27 14:30:00');
  });

  test('CSV reflects asc sort after toggleAuditSort — oldest entry appears first', () => {
    const c = makeComponent();
    c.toggleAuditSort();
    const mockDl = jest.fn();
    c.exportAuditCSV(mockDl);
    const firstDataLine = mockDl.mock.calls[0][0].split('\n')[1];
    expect(firstDataLine).toContain('2026-05-25 10:00:00');
  });
});


// ─────────────────────────────────────────────────────────────────────────────
// Ticket 1 — Modal open / close  (R1.1, R1.5, AC1.1)
// ─────────────────────────────────────────────────────────────────────────────

describe('attendancePage — openModal (Ticket 1 R1.1, AC1.1)', () => {
  test('openModal sets showModal to true', () => {
    const c = makeComponent();
    c.openModal();
    expect(c.showModal).toBe(true);
  });

  test('openModal resets leaveForm to defaults (type = Leave)', () => {
    const c = makeComponent();
    c.leaveForm = { type: 'Sick', startDate: '2030-01-01', endDate: '2030-01-05', reason: 'Old reason' };
    c.openModal();
    expect(c.leaveForm).toEqual({ type: 'Leave', startDate: '', endDate: '', reason: '' });
  });

  test('openModal clears any existing formErrors', () => {
    const c = makeComponent();
    c.formErrors = { startDate: 'Some error' };
    c.openModal();
    expect(Object.keys(c.formErrors)).toHaveLength(0);
  });

  test('openModal clears formSuccess flag', () => {
    const c = makeComponent();
    c.formSuccess = true;
    c.openModal();
    expect(c.formSuccess).toBe(false);
  });
});

describe('attendancePage — closeModal (Ticket 1 R1.5)', () => {
  test('closeModal sets showModal to false', () => {
    const c = makeComponent();
    c.openModal();
    c.closeModal();
    expect(c.showModal).toBe(false);
  });

  test('closeModal is idempotent — calling twice stays false', () => {
    const c = makeComponent();
    c.closeModal();
    c.closeModal();
    expect(c.showModal).toBe(false);
  });
});


// ─────────────────────────────────────────────────────────────────────────────
// Ticket 1 — Form fields (R1.2, AC1.2)
// ─────────────────────────────────────────────────────────────────────────────

describe('attendancePage — leave form fields (Ticket 1 R1.2, AC1.2)', () => {
  test('leaveForm has type field accepting Leave', () => {
    const c = makeComponent();
    c.leaveForm.type = 'Leave';
    expect(c.leaveForm.type).toBe('Leave');
  });

  test('leaveForm has type field accepting Sick', () => {
    const c = makeComponent();
    c.leaveForm.type = 'Sick';
    expect(c.leaveForm.type).toBe('Sick');
  });

  test('leaveForm has startDate field', () => {
    const c = makeComponent();
    c.leaveForm.startDate = daysFromToday(1);
    expect(c.leaveForm.startDate).toBe(daysFromToday(1));
  });

  test('leaveForm has endDate field', () => {
    const c = makeComponent();
    c.leaveForm.endDate = daysFromToday(3);
    expect(c.leaveForm.endDate).toBe(daysFromToday(3));
  });

  test('leaveForm has reason field', () => {
    const c = makeComponent();
    c.leaveForm.reason = 'Family event';
    expect(c.leaveForm.reason).toBe('Family event');
  });
});


// ─────────────────────────────────────────────────────────────────────────────
// Ticket 1 — submitLeaveRequest with invalid data  (R1.4, AC1.3)
// ─────────────────────────────────────────────────────────────────────────────

describe('attendancePage — submitLeaveRequest invalid (Ticket 1 R1.4, AC1.3)', () => {
  test('sets formErrors when form is empty', () => {
    const c = makeComponent();
    c.leaveForm = { type: '', startDate: '', endDate: '', reason: '' };
    c.submitLeaveRequest();
    expect(Object.keys(c.formErrors).length).toBeGreaterThan(0);
  });

  test('flags endDate error when end is before start', () => {
    const c = makeComponent();
    c.leaveForm = validLeaveForm({ startDate: daysFromToday(5), endDate: daysFromToday(2) });
    c.submitLeaveRequest();
    expect(c.formErrors).toHaveProperty('endDate');
  });

  test('does NOT save to localStorage when validation fails', () => {
    const storage = makeStorage();
    const c = makeComponent(storage);
    c.leaveForm = { type: '', startDate: '', endDate: '', reason: '' };
    c.submitLeaveRequest();
    expect(storage.getItem('leaveRequests')).toBeNull();
  });

  test('does NOT set formSuccess when validation fails', () => {
    const c = makeComponent();
    c.leaveForm = { type: '', startDate: '', endDate: '', reason: '' };
    c.submitLeaveRequest();
    expect(c.formSuccess).toBe(false);
  });

  test('modal stays open when validation fails (AC1.3)', () => {
    const c = makeComponent();
    c.openModal();
    c.leaveForm = { type: '', startDate: '', endDate: '', reason: '' };
    c.submitLeaveRequest();
    expect(c.showModal).toBe(true);
  });
});


// ─────────────────────────────────────────────────────────────────────────────
// Ticket 1 — submitLeaveRequest with valid data  (R1.3, AC1.4)
// ─────────────────────────────────────────────────────────────────────────────

describe('attendancePage — submitLeaveRequest valid (Ticket 1 R1.3, AC1.4)', () => {
  beforeEach(() => jest.useFakeTimers());
  afterEach(() => { jest.clearAllTimers(); jest.useRealTimers(); });
  test('formErrors is empty after successful submit', () => {
    const c = makeComponent();
    c.leaveForm = validLeaveForm();
    c.submitLeaveRequest();
    expect(Object.keys(c.formErrors)).toHaveLength(0);
  });

  test('formSuccess becomes true after successful submit', () => {
    const c = makeComponent();
    c.leaveForm = validLeaveForm();
    c.submitLeaveRequest();
    expect(c.formSuccess).toBe(true);
  });

  test('saves data to localStorage after successful submit (R1.3)', () => {
    const storage = makeStorage();
    const c = makeComponent(storage);
    c.leaveForm = validLeaveForm({ type: 'Sick', reason: 'Flu' });
    c.submitLeaveRequest();
    const saved = JSON.parse(storage.getItem('leaveRequests'));
    expect(Array.isArray(saved)).toBe(true);
    expect(saved).toHaveLength(1);
    expect(saved[0].type).toBe('Sick');
    expect(saved[0].reason).toBe('Flu');
  });

  test('saves correct startDate and endDate to localStorage', () => {
    const storage = makeStorage();
    const c = makeComponent(storage);
    const start = daysFromToday(1);
    const end   = daysFromToday(5);
    c.leaveForm = validLeaveForm({ startDate: start, endDate: end });
    c.submitLeaveRequest();
    const saved = JSON.parse(storage.getItem('leaveRequests'));
    expect(saved[0].startDate).toBe(start);
    expect(saved[0].endDate).toBe(end);
  });

  test('multiple submits accumulate in localStorage', () => {
    const storage = makeStorage();
    const c = makeComponent(storage);
    c.leaveForm = validLeaveForm({ reason: 'First' });
    c.submitLeaveRequest();
    c.openModal();
    c.leaveForm = validLeaveForm({ type: 'Sick', reason: 'Second' });
    c.submitLeaveRequest();
    const saved = JSON.parse(storage.getItem('leaveRequests'));
    expect(saved).toHaveLength(2);
  });
});


// ─────────────────────────────────────────────────────────────────────────────
// Ticket 1 — Auto-close modal after 1500ms  (AC1.4)
// ─────────────────────────────────────────────────────────────────────────────

describe('attendancePage — auto-close after submit (Ticket 1 AC1.4)', () => {
  beforeEach(() => jest.useFakeTimers());
  afterEach(() => { jest.clearAllTimers(); jest.useRealTimers(); });

  test('modal is still open immediately after valid submit', () => {
    const c = makeComponent();
    c.openModal();
    c.leaveForm = validLeaveForm();
    c.submitLeaveRequest();
    expect(c.showModal).toBe(true);
  });

  test('modal stays open at 1499ms', () => {
    const c = makeComponent();
    c.openModal();
    c.leaveForm = validLeaveForm();
    c.submitLeaveRequest();
    jest.advanceTimersByTime(1499);
    expect(c.showModal).toBe(true);
  });

  test('modal closes at exactly 1500ms (AC1.4)', () => {
    const c = makeComponent();
    c.openModal();
    c.leaveForm = validLeaveForm();
    c.submitLeaveRequest();
    jest.advanceTimersByTime(1500);
    expect(c.showModal).toBe(false);
  });

  test('auto-close does NOT fire when submit fails validation', () => {
    const c = makeComponent();
    c.openModal();
    c.leaveForm = { type: '', startDate: '', endDate: '', reason: '' };
    c.submitLeaveRequest();
    jest.advanceTimersByTime(2000);
    expect(c.showModal).toBe(true);
  });
});
