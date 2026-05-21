import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.SUPABASE_URL!,
  process.env.SUPABASE_KEY!
);

export interface ExportRow {
  staff_name: string;
  date: string;
  clock_in: string;
  clock_out: string | null;
  total_hours: string;
}

export interface DateRange {
  from?: string;   // ISO string e.g. "2025-01-01"
  to?: string;     // ISO string e.g. "2025-12-31"
}

export const getExportData = async (range?: DateRange): Promise<ExportRow[]> => {
  let query = supabase
    .from('sessions')
    .select(`
      clock_in_time,
      clock_out_time,
      duration_minutes,
      profiles (
        first_name,
        last_name
      )
    `)
    .eq('status', 'completed')
    .order('clock_in_time', { ascending: false });

  if (range?.from) query = query.gte('clock_in_time', range.from);
  if (range?.to)   query = query.lte('clock_in_time', range.to);

  const { data, error } = await query;
  if (error) throw new Error(`Export query failed: ${error.message}`);

  return (data || []).map((row: any) => ({
    staff_name: `${row.profiles.first_name} ${row.profiles.last_name}`,
    date: new Date(row.clock_in_time).toLocaleDateString(),
    clock_in: new Date(row.clock_in_time).toLocaleTimeString(),
    clock_out: row.clock_out_time
      ? new Date(row.clock_out_time).toLocaleTimeString()
      : 'Still clocked in',
    total_hours: row.duration_minutes
      ? `${Math.floor(row.duration_minutes / 60)}h ${row.duration_minutes % 60}m`
      : '—',
  }));
};