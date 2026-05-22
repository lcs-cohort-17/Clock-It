import { createClient, SupabaseClient } from '@supabase/supabase-js';
import { encrypt, decrypt } from '../utils/googleEncryption.ts';

// ── Singleton Supabase client ─────────────────────────────────
let supabaseInstance: SupabaseClient | null = null;

function getSupabase(): SupabaseClient {
  if (!supabaseInstance) {
    const url = process.env.SUPABASE_URL;
    const key = process.env.SUPABASE_KEY;
    if (!url || !key) throw new Error('SUPABASE_URL and SUPABASE_KEY are required');
    supabaseInstance = createClient(url, key);
  }
  return supabaseInstance;
}

// ── Types ─────────────────────────────────────────────────────
export interface StoredTokens {
  access_token: string;
  refresh_token?: string | null;
  expiry_date?: number | null; // Google returns ms timestamp; we convert before storing
}

export interface ConnectionStatus {
  connected: boolean;
  connectedAt?: string;
}

// ── Save (upsert) encrypted OAuth tokens ─────────────────────
export const saveCredentials = async (
  profileId: string,
  tokens: StoredTokens
): Promise<void> => {
  const supabase = getSupabase();
  const { error } = await supabase.from('google_credentials').upsert(
    {
      profile_id: profileId,
      access_token: encrypt(tokens.access_token),
      refresh_token: tokens.refresh_token ? encrypt(tokens.refresh_token) : null,
      // FIX: convert ms timestamp → ISO string to match timestamptz column
      token_expiry: tokens.expiry_date ? new Date(tokens.expiry_date).toISOString() : null,
      // FIX: mark as connected on every save/refresh
      is_connected: true,
      connected_at: new Date().toISOString(),
    },
    { onConflict: 'profile_id' }
  );
  if (error) throw new Error(`saveCredentials failed: ${error.message}`);
};

// ── Fetch and decrypt stored tokens ──────────────────────────
export const getDecryptedTokens = async (
  profileId: string
): Promise<StoredTokens | null> => {
  const supabase = getSupabase();
  const { data, error } = await supabase
    .from('google_credentials')
    // FIX: select token_expiry (actual column name), not expiry_date
    .select('access_token, refresh_token, token_expiry')
    .eq('profile_id', profileId)
    .single();

  if (error || !data) return null;

  return {
    access_token: decrypt(data.access_token),
    refresh_token: data.refresh_token ? decrypt(data.refresh_token) : null,
    // FIX: convert ISO string back to ms number so Google OAuth client is happy
    expiry_date: data.token_expiry ? new Date(data.token_expiry).getTime() : null,
  };
};

// ── Check whether a profile has connected Google ─────────────
export const getConnectionStatus = async (
  profileId: string
): Promise<ConnectionStatus> => {
  const supabase = getSupabase();
  const { data, error } = await supabase
    .from('google_credentials')
    .select('connected_at, is_connected')
    .eq('profile_id', profileId)
    .single();

  if (error || !data || !data.is_connected) return { connected: false };
  return { connected: true, connectedAt: data.connected_at };
};

// ── Delete stored credentials ─────────────────────────────────
export const clearCredentials = async (profileId: string): Promise<void> => {
  const supabase = getSupabase();
  // FIX: set is_connected = false instead of hard delete so the row (and audit trail) is preserved
  const { error } = await supabase
    .from('google_credentials')
    .update({
      is_connected: false,
      access_token: '',
      refresh_token: null,
      token_expiry: null,
    })
    .eq('profile_id', profileId);

  if (error) throw new Error(`clearCredentials failed: ${error.message}`);
};