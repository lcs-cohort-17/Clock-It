import { createClient } from '@supabase/supabase-js';
import { encrypt, decrypt } from '../utils/googleEncryption.ts';

const supabase = createClient(
  process.env.SUPABASE_URL!,
  process.env.SUPABASE_KEY!
);

// ── Types ──────────────────────────────────────────────────────
export interface GoogleCredential {
  profile_id: string;
  access_token: string;
  refresh_token: string | null;
  token_expiry: string | null;
  is_connected: boolean;
  connected_at: string | null;
}

export interface ConnectionStatus {
  connected: boolean;
  connected_at: string | null;
  token_expired: boolean;
}

// ── Check connection status for a profile ─────────────────────
export const getConnectionStatus = async (
  profileId: string
): Promise<ConnectionStatus> => {
  const { data, error } = await supabase
    .from('google_credentials')
    .select('is_connected, token_expiry, connected_at')
    .eq('profile_id', profileId)
    .single();

  if (error || !data) {
    return { connected: false, connected_at: null, token_expired: false };
  }

  const isExpired = data.token_expiry
    ? new Date(data.token_expiry) < new Date()
    : false;

  return {
    connected: data.is_connected && !isExpired,
    connected_at: data.connected_at,
    token_expired: isExpired,
  };
};

// ── Store tokens after OAuth callback ─────────────────────────
export const saveCredentials = async (
  profileId: string,
  tokens: {
    access_token: string;
    refresh_token?: string | null;
    expiry_date?: number | null;
  }
): Promise<void> => {
  const encryptedAccess = encrypt(tokens.access_token);
  const encryptedRefresh = tokens.refresh_token
    ? encrypt(tokens.refresh_token)
    : null;

  const { error } = await supabase
    .from('google_credentials')
    .upsert(
      {
        profile_id: profileId,
        access_token: encryptedAccess,
        refresh_token: encryptedRefresh,
        token_expiry: tokens.expiry_date
          ? new Date(tokens.expiry_date).toISOString()
          : null,
        is_connected: true,
        connected_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
      },
      { onConflict: 'profile_id' }
    );

  if (error) throw new Error(`Failed to save credentials: ${error.message}`);
};

// ── Fetch decrypted tokens (for making API calls) ─────────────
export const getDecryptedTokens = async (profileId: string) => {
  const { data, error } = await supabase
    .from('google_credentials')
    .select('access_token, refresh_token, token_expiry')
    .eq('profile_id', profileId)
    .single();

  if (error || !data) return null;

  return {
    access_token: decrypt(data.access_token),
    refresh_token: data.refresh_token ? decrypt(data.refresh_token) : null,
    expiry_date: data.token_expiry
      ? new Date(data.token_expiry).getTime()
      : null,
  };
};

// ── Disconnect — clears tokens, keeps row for audit ───────────
export const clearCredentials = async (profileId: string): Promise<void> => {
  const { error } = await supabase
    .from('google_credentials')
    .update({
      is_connected: false,
      access_token: '',
      refresh_token: null,
      updated_at: new Date().toISOString(),
    })
    .eq('profile_id', profileId);

  if (error) throw new Error(`Failed to clear credentials: ${error.message}`);
};