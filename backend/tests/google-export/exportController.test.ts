import { describe, it, expect, vi, beforeEach } from 'vitest';
import type { Request, Response } from 'express';
import type { SupabaseClient } from '@supabase/supabase-js';

vi.mock('../../src/models/googleDb.ts', () => ({
  getDecryptedTokens: vi.fn(),
}));

vi.mock('../../src/models/exportDb.ts', () => ({
  getExportData: vi.fn(),
}));

const mockAppend = vi.fn().mockResolvedValue({});
const mockCreate = vi.fn();
const mockSheetsClient = {
  spreadsheets: {
    create: mockCreate,
    values: { append: mockAppend },
  },
};

vi.mock('googleapis', () => ({
  google: {
    auth: {
      OAuth2: vi.fn().mockImplementation(() => ({
        setCredentials: vi.fn(),
        generateAuthUrl: vi.fn(),
      })),
    },
    sheets: vi.fn(() => mockSheetsClient),
  },
}));

vi.mock('../../src/config/google.ts', () => ({
  oauth2Client: { setCredentials: vi.fn() },
}));

import { exportToSheets } from '../../src/controllers/exportController.ts';
import { getDecryptedTokens } from '../../src/models/googleDb.ts';
import { getExportData } from '../../src/models/exportDb.ts';

const SAMPLE_TOKENS = { access_token: 'at', refresh_token: 'rt', expiry_date: 9999 };

const SAMPLE_ROW = {
  staff_name: 'Jane Doe',
  date: '01/01/2024',
  clock_in: '09:00:00 AM',
  clock_out: '05:00:00 PM',
  total_hours: '8h 0m',
};

const mockSupabase = {} as SupabaseClient;

function makeRows(count: number) {
  return Array.from({ length: count }, (_, i) => ({ ...SAMPLE_ROW, staff_name: `Staff ${i}` }));
}

function mockRes(): any {
  const res: any = {};
  res.status = vi.fn(() => res);
  res.json = vi.fn(() => res);
  return res;
}

function mockReq(body = {}) {
  return { body: { from: '2024-01-01', to: '2024-01-31', profile_id: 'profile-1', ...body } };
}

beforeEach(() => {
  vi.clearAllMocks();
  mockCreate.mockResolvedValue({
    data: {
      spreadsheetId: 'sheet-id-123',
      spreadsheetUrl: 'https://docs.google.com/spreadsheets/d/sheet-id-123',
    },
  });
});

describe('exportToSheets', () => {
  it('returns 401 when no Google tokens are stored', async () => {
    (getDecryptedTokens as any).mockResolvedValue(null);

    const req = mockReq();
    const res = mockRes();

    await exportToSheets(mockSupabase)(req as Request, res as Response);

    expect(res.status).toHaveBeenCalledWith(401);
    expect(res.json).toHaveBeenCalledWith(
      expect.objectContaining({ error: expect.stringContaining('not connected') })
    );
    expect(mockCreate).not.toHaveBeenCalled();
  });

  it('returns 200 with null URL when no rows exist for the date range', async () => {
    (getDecryptedTokens as any).mockResolvedValue(SAMPLE_TOKENS);
    (getExportData as any).mockResolvedValue([]);

    const req = mockReq();
    const res = mockRes();

    await exportToSheets(mockSupabase)(req as Request, res as Response);

    expect(res.status).toHaveBeenCalledWith(200);
    expect(res.json).toHaveBeenCalledWith(
      expect.objectContaining({ url: null })
    );
    expect(mockCreate).not.toHaveBeenCalled();
  });

  it('creates a spreadsheet, writes rows, and returns the URL', async () => {
    (getDecryptedTokens as any).mockResolvedValue(SAMPLE_TOKENS);
    (getExportData as any).mockResolvedValue([SAMPLE_ROW]);

    const req = mockReq();
    const res = mockRes();

    await exportToSheets(mockSupabase)(req as Request, res as Response);

    expect(mockCreate).toHaveBeenCalledOnce();
    expect(mockAppend).toHaveBeenCalled();
    expect(res.status).toHaveBeenCalledWith(200);
    expect(res.json).toHaveBeenCalledWith(
      expect.objectContaining({
        success: true,
        url: 'https://docs.google.com/spreadsheets/d/sheet-id-123',
        rows_exported: 1,
      })
    );
  });

  it('includes the correct header row as the first append', async () => {
    (getDecryptedTokens as any).mockResolvedValue(SAMPLE_TOKENS);
    (getExportData as any).mockResolvedValue([SAMPLE_ROW]);

    await exportToSheets(mockSupabase)(mockReq() as Request, mockRes() as Response);

    const firstAppendCall = mockAppend.mock.calls[0][0];
    const firstRow = firstAppendCall.requestBody.values[0];
    expect(firstRow).toEqual(['Staff Name', 'Date', 'Clock In', 'Clock Out', 'Total Hours']);
  });

  it('uses "—" for clock_out when value is null/undefined', async () => {
    (getDecryptedTokens as any).mockResolvedValue(SAMPLE_TOKENS);
    (getExportData as any).mockResolvedValue([{ ...SAMPLE_ROW, clock_out: null }]);

    await exportToSheets(mockSupabase)(mockReq() as Request, mockRes() as Response);

    const appendCall = mockAppend.mock.calls[0][0];
    const dataRow = appendCall.requestBody.values[1]; // row after header
    expect(dataRow[3]).toBe('—');
  });

  it('splits large datasets into 500-row batches', async () => {
    (getDecryptedTokens as any).mockResolvedValue(SAMPLE_TOKENS);
    (getExportData as any).mockResolvedValue(makeRows(1050));

    const res = mockRes();
    await exportToSheets(mockSupabase)(mockReq() as Request, res as Response);

    // 1050 data rows + 1 header = 1051 total rows
    // Batch 1: rows 0–499 (500), Batch 2: rows 500–999 (500), Batch 3: rows 1000–1050 (51)
    expect(mockAppend).toHaveBeenCalledTimes(3);
    expect(res.json).toHaveBeenCalledWith(
      expect.objectContaining({ rows_exported: 1050 })
    );
  });

  it('returns 401 and a reconnect message on invalid_grant error', async () => {
    (getDecryptedTokens as any).mockResolvedValue(SAMPLE_TOKENS);
    (getExportData as any).mockResolvedValue([SAMPLE_ROW]);
    mockCreate.mockRejectedValue(new Error('invalid_grant'));

    const res = mockRes();
    await exportToSheets(mockSupabase)(mockReq() as Request, res as Response);

    expect(res.status).toHaveBeenCalledWith(401);
    expect(res.json).toHaveBeenCalledWith(
      expect.objectContaining({ error: expect.stringContaining('reconnect') })
    );
  });

  it('returns 500 for unexpected errors', async () => {
    (getDecryptedTokens as any).mockResolvedValue(SAMPLE_TOKENS);
    (getExportData as any).mockRejectedValue(new Error('Unexpected DB crash'));

    const res = mockRes();
    await exportToSheets(mockSupabase)(mockReq() as Request, res as Response);

    expect(res.status).toHaveBeenCalledWith(500);
    expect(res.json).toHaveBeenCalledWith(
      expect.objectContaining({ error: expect.any(String) })
    );
  });

  it('passes the correct date range to getExportData', async () => {
    (getDecryptedTokens as any).mockResolvedValue(SAMPLE_TOKENS);
    (getExportData as any).mockResolvedValue([]);

    await exportToSheets(mockSupabase)(
      mockReq({ from: '2024-03-01', to: '2024-03-31' }) as Request,
      mockRes() as Response
    );

    expect(getExportData).toHaveBeenCalledWith(mockSupabase, { from: '2024-03-01', to: '2024-03-31' });
  });
});