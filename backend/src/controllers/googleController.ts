import { Request, Response } from 'express';
import { oauth2Client, SCOPES } from '../config/google.ts';
import {
  getConnectionStatus,
  saveCredentials,
  getDecryptedTokens,
  clearCredentials,
} from '../models/googleDb.ts';

function getProfileId(req: Request): string | undefined {
  return (req as any).profile?.employee_id
    ?? (req as any).profile?.id
    ?? req.query.employee_id as string | undefined
    ?? req.query.profile_id as string | undefined;
}

// ── GET /api/google/status ────────────────────────────────────
export const checkStatus = async (req: Request, res: Response): Promise<void> => {
  try {
    const profileId = getProfileId(req);
    const status = await getConnectionStatus(profileId);
    res.status(200).json(status);
  } catch (err) {
    console.error('[Google] Status check error:', err);
    res.status(500).json({ error: 'Failed to check connection status' });
  }
};

// ── GET /api/google/auth ──────────────────────────────────────
export const initiateAuth = (req: Request, res: Response): void => {
  try {
    const profileId = getProfileId(req);
    if (!profileId) {
      res.status(400).json({ error: 'Missing employee_id' });
      return;
    }

    const url = oauth2Client.generateAuthUrl({
      access_type: 'offline',
      scope: SCOPES,
      prompt: 'consent',       // ensures refresh_token is always returned
      state: profileId,        // carries profileId through the OAuth redirect
    });

    res.status(200).json({ url });
  } catch (err) {
    console.error('[Google] Auth URL error:', err);
    res.status(500).json({ error: 'Failed to generate auth URL' });
  }
};

// ── GET /api/google/callback ──────────────────────────────────
// Google redirects here — no auth middleware (user isn't logged in yet at this point)
export const handleCallback = async (req: Request, res: Response): Promise<void> => {
  try {
    const { code, state: profileId, error: oauthError } = req.query as Record<string, string>;

    if (oauthError) {
      res.redirect(`${process.env.CLIENT_URL}/settings?google=denied`);
      return;
    }

    if (!code || !profileId) {
      res.status(400).json({ error: 'Missing code or state' });
      return;
    }

    const { tokens } = await oauth2Client.getToken(code);
    await saveCredentials(profileId, {
      access_token: tokens.access_token!,
      refresh_token: tokens.refresh_token,
      expiry_date: tokens.expiry_date,
    });

    res.redirect(`${process.env.CLIENT_URL}/settings?google=connected`);
  } catch (err) {
    console.error('[Google] Callback error:', err);
    res.redirect(`${process.env.CLIENT_URL}/settings?google=error`);
  }
};

// ── DELETE /api/google/disconnect ─────────────────────────────
export const disconnect = async (req: Request, res: Response): Promise<void> => {
  try {
    const profileId = getProfileId(req);
    const tokens = await getDecryptedTokens(profileId);

    if (!tokens) {
      res.status(404).json({ error: 'No Google connection found' });
      return;
    }

    // Revoke with Google first
    try {
      await oauth2Client.revokeToken(tokens.access_token);
    } catch (revokeErr: any) {
      // Log but don't fail — token may already be expired
      console.warn('[Google] Revoke warning:', revokeErr.message);
    }

    await clearCredentials(profileId);
    res.status(200).json({ success: true, message: 'Disconnected from Google Sheets' });
  } catch (err) {
    console.error('[Google] Disconnect error:', err);
    res.status(500).json({ error: 'Failed to disconnect' });
  }
};
