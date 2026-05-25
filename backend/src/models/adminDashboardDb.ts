//models/adminDashboardDb.ts
//models/adminDashboardDb.ts
import { supabase } from '../config/supabase.js';

export async function fetchRecentActivity(page: number, limit: number) {
    const from = (page - 1) * limit;
    const to = from + limit - 1;

    const { data, error } = await supabase
        .from('attendance_logs')
        .select(`
            profile_id,
            event_time,
            event_type,
            sync_status,
            device_info,
            profiles (
                first_name,
                last_name
            )
            `)
        .order('event_time', { ascending: false })
        .range(from, to);

        if (error) throw error;
        return data ?? [];
};



export async function fetchCurrentlyOnsite () {
  const { data, error } = await supabase
    .from('attendance_logs')
    .select(`
        profile_id,
        event_time,
        location,
         event_type,
         profiles (
         first_name,
         last_name
        )
    `)
    .order('event_time', { ascending: false });
        if (error) throw error;
        return data ?? [];
};
