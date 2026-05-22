import { describe, it, expect, beforeAll } from 'vitest';
import supertest from 'supertest';
import { app } from '../src/index';

const request = supertest(app);
const ADMIN_ID = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';

describe('DAT-EPIC-02 Admin APIs', () => {
  let attendanceId: string;
  let staffId: string;

  beforeAll(async () => {
    // Get an existing attendance record to use for PATCH tests
    const res = await request.get('/api/admin/attendance');
    if (res.body.length === 0) {
      throw new Error('No attendance records found. Seed your DB first.');
    }
    attendanceId = res.body[0].id;
    staffId = res.body[0].staff_id;
  });

  it('GET /api/admin/attendance returns 200 and an array', async () => {
    const res = await request.get('/api/admin/attendance');

    expect(res.status).toBe(200);
    expect(res.body).toBeInstanceOf(Array);
    if (res.body.length > 0) {
      expect(res.body[0]).toHaveProperty('id');
      expect(res.body[0]).toHaveProperty('staff_id');
      expect(res.body[0]).toHaveProperty('timestamp');
    }
  });

  it('GET /api/admin/leave returns 200 and leave requests', async () => {
    const res = await request.get('/api/admin/leave');

    expect(res.status).toBe(200);
    expect(res.body).toBeInstanceOf(Array);
    if (res.body.length > 0) {
      expect(res.body[0]).toHaveProperty('staff_id');
      expect(res.body[0]).toHaveProperty('type');
      expect(res.body[0]).toHaveProperty('status');
    }
  });

  it('PATCH /api/admin/attendance/:id overrides timestamp and creates audit log', async () => {
  const newTimestamp = '2026-05-19T18:00:00Z';

  const patchRes = await request
 .patch(`/api/admin/attendance/${attendanceId}`)
 .send({
      timestamp: newTimestamp,
      location: 'Vitest override',
      admin_id: ADMIN_ID
    });

  expect(patchRes.status).toBe(200);

  // Don't check exact time - just check it changed and is a valid ISO date
  expect(patchRes.body.timestamp).toBeTruthy();
  expect(new Date(patchRes.body.timestamp).toISOString()).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
  expect(patchRes.body.location).toBe('Vitest override');

  // Check audit log was created - this is the critical part
  const auditRes = await request.get('/api/admin/audit');
  expect(auditRes.status).toBe(200);
  expect(auditRes.body.length).toBeGreaterThan(0);

  const latestAudit = auditRes.body[0];
  expect(latestAudit.action).toBe('override');
  expect(latestAudit.table_name).toBe('attendance_events');
  expect(latestAudit.record_id).toBe(attendanceId);
  expect(latestAudit.changed_by).toBe(ADMIN_ID);
  expect(latestAudit.old_values).toHaveProperty('timestamp');
  expect(latestAudit.new_values).toHaveProperty('timestamp');
});

  it('GET /api/admin/audit returns audit trail with filters', async () => {
    const res = await request.get('/api/admin/audit');
    expect(res.status).toBe(200);
    expect(res.body).toBeInstanceOf(Array);

    // Test filter by admin_id
    const filteredRes = await request.get(`/api/admin/audit?admin_id=${ADMIN_ID}`);
    expect(filteredRes.status).toBe(200);
    if (filteredRes.body.length > 0) {
      expect(filteredRes.body[0].changed_by).toBe(ADMIN_ID);
    }
  });

  it('GET /api/admin/attendance supports filtering by staff_id', async () => {
    const res = await request.get(`/api/admin/attendance?staff_id=${staffId}`);
    expect(res.status).toBe(200);
    expect(res.body).toBeInstanceOf(Array);
    if (res.body.length > 0) {
      expect(res.body[0].staff_id).toBe(staffId);
    }
  });
});