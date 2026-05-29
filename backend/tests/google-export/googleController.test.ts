import { describe, it, expect, vi, beforeEach } from 'vitest';
import type { Request, Response } from 'express';

// ── Mock dependencies ─────────────────────────────────────────
vi.mock('../../src/models/googleDb.ts', () => ({
  getConnectionStatus: vi.fn(),
  saveCredentials: vi.fn(),
  getDecryptedTokens: vi.fn(),
  clearCredentials: vi.fn(),
}));

// vi.mock factories are hoisted to the top of the file by Vitest, so any
// variables they reference must also be hoisted via vi.hoisted().
const { mockGenerateAuthUrl, mockGetToken, mockRevokeToken } = vi.hoisted(() => ({
  mockGenerateAuthUrl: vi.fn(),
  mockGetToken: vi.fn(),
  mockRevokeToken: vi.fn(),
}));

vi.mock('../../src/config/google.ts', () => ({
  oauth2Client: {
    generateAuthUrl: mockGenerateAuthUrl,
    getToken: mockGetToken,
    revokeToken: mockRevokeToken,
    setCredentials: vi.fn(),
  },
  SCOPES: ['https://www.googleapis.com/auth/spreadsheets'],
}));

import {
  checkStatus,
  initiateAuth,
  handleCallback,
  disconnect,
} from '../../src/controllers/googleController.ts';

import {
  getConnectionStatus,
  saveCredentials,
  getDecryptedTokens,
  clearCredentials,
} from '../../src/models/googleDb.ts';

// ── Request / Response helpers ────────────────────────────────
function mockReq(overrides: Partial<Request> = {}): Partial<Request> {
  return { query: {}, body: {}, ...overrides };
}

function mockRes(): Partial<Response> & { _json: any; _status: number; _redirectUrl: string } {
  const res: any = {};
  res._status = 200;
  res._json = null;
  res._redirectUrl = '';
  res.status = vi.fn((code: number) => { res._status = code; return res; });
  res.json = vi.fn((body: any) => { res._json = body; return res; });
  res.redirect = vi.fn((url: string) => { res._redirectUrl = url; return res; });
  return res;
}

beforeEach(() => {
  vi.clearAllMocks();
  process.env.CLIENT_URL = 'http://localhost:3000';
});

// ─────────────────────────────────────────────────────────────
describe('checkStatus', () => {
  it('returns the connection status for the profile', async () => {
    (getConnectionStatus as any).mockResolvedValue({ connected: true, connectedAt: '2024-01-01' });
    const req = mockReq({ profile: { id: 'profile-1' } } as any);
    const res = mockRes();

    await checkStatus(req as Request, res as Response);

    expect(res.status).toHaveBeenCalledWith(200);
    expect(res.json).toHaveBeenCalledWith({ connected: true, connectedAt: '2024-01-01' });
  });

  it('returns 500 when getConnectionStatus throws', async () => {
    (getConnectionStatus as any).mockRejectedValue(new Error('DB error'));
    const req = mockReq({ profile: { id: 'profile-1' } } as any);
    const res = mockRes();

    await checkStatus(req as Request, res as Response);

    expect(res.status).toHaveBeenCalledWith(500);
    expect((res._json as any).error).toBeTruthy();
  });
});

// ─────────────────────────────────────────────────────────────
describe('initiateAuth', () => {
  it('returns a Google OAuth URL', () => {
    mockGenerateAuthUrl.mockReturnValue('https://accounts.google.com/o/oauth2/auth?...');
    const req = mockReq({ profile: { id: 'profile-1' } } as any);
    const res = mockRes();

    initiateAuth(req as Request, res as Response);

    expect(res.status).toHaveBeenCalledWith(200);
    expect(res.json).toHaveBeenCalledWith({ url: 'https://accounts.google.com/o/oauth2/auth?...' });
  });

  it('passes profileId as state in the OAuth URL options', () => {
    mockGenerateAuthUrl.mockReturnValue('https://oauth.url');
    const req = mockReq({ profile: { id: 'my-profile-id' } } as any);
    const res = mockRes();

    initiateAuth(req as Request, res as Response);

    expect(mockGenerateAuthUrl).toHaveBeenCalledWith(
      expect.objectContaining({
        state: 'my-profile-id',
        access_type: 'offline',
        prompt: 'consent',
      })
    );
  });

  it('returns 500 if generateAuthUrl throws', () => {
    mockGenerateAuthUrl.mockImplementation(() => { throw new Error('OAuth failure'); });
    const req = mockReq({ profile: { id: 'profile-1' } } as any);
    const res = mockRes();

    initiateAuth(req as Request, res as Response);

    expect(res.status).toHaveBeenCalledWith(500);
  });
});

// ─────────────────────────────────────────────────────────────
describe('handleCallback', () => {
  it('redirects to /settings?google=connected on success', async () => {
    mockGetToken.mockResolvedValue({
      tokens: { access_token: 'at', refresh_token: 'rt', expiry_date: 9999 },
    });
    (saveCredentials as any).mockResolvedValue(undefined);

    const req = mockReq({ query: { code: 'auth-code', state: 'profile-1' } });
    const res = mockRes();

    await handleCallback(req as Request, res as Response);

    expect(saveCredentials).toHaveBeenCalledWith('profile-1', {
      access_token: 'at',
      refresh_token: 'rt',
      expiry_date: 9999,
    });
    expect(res.redirect).toHaveBeenCalledWith('http://localhost:3000/settings?google=connected');
  });

  it('redirects to /settings?google=denied when OAuth returns an error', async () => {
    const req = mockReq({ query: { error: 'access_denied', state: 'profile-1' } });
    const res = mockRes();

    await handleCallback(req as Request, res as Response);

    expect(res.redirect).toHaveBeenCalledWith('http://localhost:3000/settings?google=denied');
    expect(saveCredentials).not.toHaveBeenCalled();
  });

  it('returns 400 when code or state is missing', async () => {
    const req = mockReq({ query: { code: 'auth-code' } }); // no state
    const res = mockRes();

    await handleCallback(req as Request, res as Response);

    expect(res.status).toHaveBeenCalledWith(400);
    expect(saveCredentials).not.toHaveBeenCalled();
  });

  it('redirects to /settings?google=error when getToken throws', async () => {
    mockGetToken.mockRejectedValue(new Error('Token exchange failed'));
    const req = mockReq({ query: { code: 'bad-code', state: 'profile-1' } });
    const res = mockRes();

    await handleCallback(req as Request, res as Response);

    expect(res.redirect).toHaveBeenCalledWith('http://localhost:3000/settings?google=error');
  });
});

// ─────────────────────────────────────────────────────────────
describe('disconnect', () => {
  it('revokes the token, clears credentials, and returns success', async () => {
    (getDecryptedTokens as any).mockResolvedValue({ access_token: 'at', refresh_token: null });
    mockRevokeToken.mockResolvedValue({});
    (clearCredentials as any).mockResolvedValue(undefined);

    const req = mockReq({ profile: { id: 'profile-1' } } as any);
    const res = mockRes();

    await disconnect(req as Request, res as Response);

    expect(mockRevokeToken).toHaveBeenCalledWith('at');
    expect(clearCredentials).toHaveBeenCalledWith('profile-1');
    expect(res.status).toHaveBeenCalledWith(200);
    expect((res._json as any).success).toBe(true);
  });

  it('returns 404 when no Google connection exists', async () => {
    (getDecryptedTokens as any).mockResolvedValue(null);

    const req = mockReq({ profile: { id: 'profile-1' } } as any);
    const res = mockRes();

    await disconnect(req as Request, res as Response);

    expect(res.status).toHaveBeenCalledWith(404);
    expect(clearCredentials).not.toHaveBeenCalled();
  });

  it('still clears credentials even when revokeToken throws', async () => {
    (getDecryptedTokens as any).mockResolvedValue({ access_token: 'at' });
    mockRevokeToken.mockRejectedValue(new Error('Token already expired'));
    (clearCredentials as any).mockResolvedValue(undefined);

    const req = mockReq({ profile: { id: 'profile-1' } } as any);
    const res = mockRes();

    await disconnect(req as Request, res as Response);

    expect(clearCredentials).toHaveBeenCalledWith('profile-1');
    expect(res.status).toHaveBeenCalledWith(200);
  });

  it('returns 500 when clearCredentials throws', async () => {
    (getDecryptedTokens as any).mockResolvedValue({ access_token: 'at' });
    mockRevokeToken.mockResolvedValue({});
    (clearCredentials as any).mockRejectedValue(new Error('DB error'));

    const req = mockReq({ profile: { id: 'profile-1' } } as any);
    const res = mockRes();

    await disconnect(req as Request, res as Response);

    expect(res.status).toHaveBeenCalledWith(500);
  });
});