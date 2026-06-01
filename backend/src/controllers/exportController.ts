import { Request, Response } from 'express';
import type { SupabaseClient } from '@supabase/supabase-js';
import { google } from 'googleapis';
import { oauth2Client } from '../config/google.ts';
import { getDecryptedTokens } from '../models/googleDb.ts';
import { getExportData } from '../models/exportDb.ts';

export function exportToSheets(supabase: SupabaseClient) {
  return async (req: Request, res: Response): Promise<void> => {
    try {
      const { from, to, employee_id, profile_id } = req.body;
      const profileId = employee_id ?? profile_id;

      if (!profileId) {
        res.status(400).json({ error: 'Missing employee_id' });
        return;
      }

    // Step 1 — get stored OAuth tokens
    const tokens = await getDecryptedTokens(profileId);
    if (!tokens) {
      res.status(401).json({
        error: 'Google Sheets not connected. Connect your account in Settings first.'
      });
      return;
    }

    // Step 2 — set tokens on OAuth client
    oauth2Client.setCredentials(tokens);
    const sheets = google.sheets({ version: 'v4', auth: oauth2Client });

    // Step 3 — fetch attendance data from DB
    const rows = await getExportData(supabase, { from, to });
    if (rows.length === 0) {
      res.status(200).json({
        message: 'No data found for the selected date range.',
        url: null
      });
      return;
    }

    // Step 4 — create a new Google Sheet
    const title = `Attendance Export ${new Date().toLocaleDateString()}`;
    const spreadsheet = await sheets.spreadsheets.create({
      requestBody: { properties: { title } }
    });
    const spreadsheetId = spreadsheet.data.spreadsheetId!;
    const sheetUrl = spreadsheet.data.spreadsheetUrl!;

    // Step 5 — build rows: header first, then data
    const header = [['Staff Name', 'Date', 'Clock In', 'Clock Out', 'Total Hours']];
    const dataRows = rows.map((r: any) => [
      r.staff_name, r.date, r.clock_in, r.clock_out ?? '—', r.total_hours
    ]);

    // Step 6 — batch write in chunks of 500
    const BATCH_SIZE = 500;
    const allRows = [...header, ...dataRows];

    for (let i = 0; i < allRows.length; i += BATCH_SIZE) {
      const chunk = allRows.slice(i, i + BATCH_SIZE);
      await sheets.spreadsheets.values.append({
        spreadsheetId,
        range: 'Sheet1',
        valueInputOption: 'RAW',
        requestBody: { values: chunk }
      });
    }

    // Step 7 — return the sheet URL
    res.status(200).json({
      success: true,
      url: sheetUrl,
      rows_exported: dataRows.length
    });

  } catch (err: any) {
    console.error('[Export] Error:', err.message);

    if (err.message?.includes('invalid_grant')) {
      res.status(401).json({
        error: 'Google token expired. Please reconnect your Google account in Settings.'
      });
      return;
    }

    res.status(500).json({
      error: 'Export failed. Please try again or contact support.'
    });
  }
  }
}
