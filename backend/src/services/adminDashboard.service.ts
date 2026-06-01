//adminDashboard.service.ts
import type { CurrentlyOnsiteRecord, RecentActivityRecord } from '../types/dashboardInterface.types.js';
export function formatRecentActivity(records: RecentActivityRecord[]) {
    return records.map((r) => ({
        profile_id: r.profile_id,
        event_time: r.event_time,
        event_type: r.event_type,
        sync_status: r.sync_status,
        device_info: r.device_info,
        staff: `${r.profiles.first_name} ${r.profiles.last_name}`,
    }));
}

export function formatOnsite(records: CurrentlyOnsiteRecord[]) {
    return records.map((r)=> ({
        profile_id: r.profile_id,
        event_time: r.event_time,
        location: r.location,
        staff: `${r.profiles.first_name} ${r.profiles.last_name}`,
    }))
}
