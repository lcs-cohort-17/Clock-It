import { describe, it, expect, vi, beforeEach } from 'vitest';

const { mockSupabase } = vi.hoisted(() => {
  const mockSupabase = { from: vi.fn() };
  return { mockSupabase };
});

vi.mock('@supabase/supabase-js', () => ({
  createClient: vi.fn(() => mockSupabase),
}));

vi.mock('../../src/utils/googleEncryption.ts', () => ({
  encrypt: vi.fn((t: string) => `enc::${t}`),
  decrypt: vi.fn((t: string) => t.replace('enc::', '')),
}));

process.env.SUPABASE_URL = 'https://test.supabase.co';
process.env.SUPABASE_KEY = 'test-key';

import {
  saveCredentials,
  getDecryptedTokens,
  getConnectionStatus,
  clearCredentials,
} from '../../src/models/googleDb.ts';

// ── Helper: build a chainable Supabase query mock ────────────
function buildChain(resolvedValue: { data: any; error: any }) {
  const single = vi.fn().mockResolvedValue(resolvedValue);
  const eq = vi.fn(() => ({ single }));
  const select = vi.fn(() => ({ eq }));
  const upsert = vi.fn().mockResolvedValue(resolvedValue);
  const deleteChain = { eq: vi.fn().mockResolvedValue(resolvedValue) };
  const deleteFn = vi.fn(() => deleteChain);
  return { select, eq, single, upsert, delete: deleteFn, _deleteEq: deleteChain.eq };
}

beforeEach(() => {
  vi.clearAllMocks();
});


describe('saveCredentials', () => {
  it('upserts encrypted tokens for a profile', async () => {
    const chain = buildChain({ data: null, error: null });
    mockSupabase.from.mockReturnValue(chain);

    await saveCredentials('profile-1', {
      access_token: 'abc',
      refresh_token: 'xyz',
      expiry_date: 9999,
    });

    expect(mockSupabase.from).toHaveBeenCalledWith('google_credentials');
    expect(chain.upsert).toHaveBeenCalledWith(
      expect.objectContaining({
        profile_id: 'profile-1',
        access_token: 'enc::abc',
        refresh_token: 'enc::xyz',
        expiry_date: 9999,
      }),
      { onConflict: 'profile_id' }
    );
  });

  it('stores null for refresh_token when not provided', async () => {
    const chain = buildChain({ data: null, error: null });
    mockSupabase.from.mockReturnValue(chain);

    await saveCredentials('profile-2', { access_token: 'abc' });

    expect(chain.upsert).toHaveBeenCalledWith(
      expect.objectContaining({ refresh_token: null }),
      expect.anything()
    );
  });

  it('throws when Supabase returns an error', async () => {
    const chain = buildChain({ data: null, error: { message: 'DB write failed' } });
    mockSupabase.from.mockReturnValue(chain);

    await expect(
      saveCredentials('profile-3', { access_token: 'abc' })
    ).rejects.toThrow('saveCredentials failed: DB write failed');
  });
});

// ─────────────────────────────────────────────────────────────
describe('getDecryptedTokens', () => {
  it('returns decrypted tokens when a row exists', async () => {
    const chain = buildChain({
      data: {
        access_token: 'enc::myAccessToken',
        refresh_token: 'enc::myRefreshToken',
        expiry_date: 12345,
      },
      error: null,
    });
    mockSupabase.from.mockReturnValue(chain);

    const result = await getDecryptedTokens('profile-1');

    expect(result).toEqual({
      access_token: 'myAccessToken',
      refresh_token: 'myRefreshToken',
      expiry_date: 12345,
    });
  });

  it('returns null when no row is found', async () => {
    const chain = buildChain({ data: null, error: { message: 'No rows found' } });
    mockSupabase.from.mockReturnValue(chain);

    const result = await getDecryptedTokens('unknown-profile');

    expect(result).toBeNull();
  });

  it('handles a null refresh_token gracefully', async () => {
    const chain = buildChain({
      data: { access_token: 'enc::at', refresh_token: null, expiry_date: null },
      error: null,
    });
    mockSupabase.from.mockReturnValue(chain);

    const result = await getDecryptedTokens('profile-1');

    expect(result?.refresh_token).toBeNull();
    expect(result?.expiry_date).toBeNull();
  });

  it('queries the correct table and profile_id', async () => {
    const chain = buildChain({ data: null, error: { message: 'nope' } });
    mockSupabase.from.mockReturnValue(chain);

    await getDecryptedTokens('profile-99');

    expect(mockSupabase.from).toHaveBeenCalledWith('google_credentials');
    expect(chain.eq).toHaveBeenCalledWith('profile_id', 'profile-99');
  });
});

// ─────────────────────────────────────────────────────────────
describe('getConnectionStatus', () => {
  it('returns connected: true with connectedAt when a row exists', async () => {
    const chain = buildChain({
      data: { connected_at: '2024-01-01T10:00:00Z' },
      error: null,
    });
    mockSupabase.from.mockReturnValue(chain);

    const result = await getConnectionStatus('profile-1');

    expect(result).toEqual({ connected: true, connectedAt: '2024-01-01T10:00:00Z' });
  });

  it('returns connected: false when no row is found', async () => {
    const chain = buildChain({ data: null, error: { message: 'No rows' } });
    mockSupabase.from.mockReturnValue(chain);

    const result = await getConnectionStatus('profile-1');

    expect(result).toEqual({ connected: false });
  });

  it('returns connected: false when data is null even without error', async () => {
    const chain = buildChain({ data: null, error: null });
    mockSupabase.from.mockReturnValue(chain);

    const result = await getConnectionStatus('profile-1');

    expect(result.connected).toBe(false);
  });
});

// ─────────────────────────────────────────────────────────────
describe('clearCredentials', () => {
  it('deletes the row for the given profile', async () => {
    const chain = buildChain({ data: null, error: null });
    mockSupabase.from.mockReturnValue(chain);

    await expect(clearCredentials('profile-1')).resolves.not.toThrow();

    expect(mockSupabase.from).toHaveBeenCalledWith('google_credentials');
    expect(chain._deleteEq).toHaveBeenCalledWith('profile_id', 'profile-1');
  });

  it('throws when Supabase returns an error', async () => {
    const chain = buildChain({ data: null, error: { message: 'Delete failed' } });
    mockSupabase.from.mockReturnValue(chain);

    await expect(clearCredentials('profile-1')).rejects.toThrow(
      'clearCredentials failed: Delete failed'
    );
  });
});