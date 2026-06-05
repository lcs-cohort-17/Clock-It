<?php

namespace App\Services;

class AttendanceService
{
    // same as formatRecentActivity(records: RecentActivityRecord[])
    public function formatRecentActivity(array $records): array
    {
        // same as records.map()
        return array_map(function($record) {
            return [
                'profile_id'  => $record['profile_id'],
                'event_time'  => $record['event_time'],
                'event_type'  => $record['event_type'],
                'sync_status' => $record['sync_status'],
                'device_info' => $record['device_info'],
                // same as `${r.profiles.first_name} ${r.profiles.last_name}`
                'staff'       => $record['first_name'] . ' ' . $record['last_name'],
            ];
        }, $records);
    }

    // same as formatOnsite(records: CurrentlyOnsiteRecord[])
    public function formatOnsite(array $records): array
    {
        // same as records.map()
        return array_map(function($record) {
            return [
                'profile_id' => $record['profile_id'],
                'event_time' => $record['event_time'],
                'location'   => $record['location'],
                // same as `${r.profiles.first_name} ${r.profiles.last_name}`
                'staff'      => $record['first_name'] . ' ' . $record['last_name'],
            ];
        }, $records);
    }
}