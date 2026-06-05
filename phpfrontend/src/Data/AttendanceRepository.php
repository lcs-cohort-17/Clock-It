<?php

namespace ClockIt\Data;

class AttendanceRepository
{
    private array $data;

    public function __construct()
    {
        $this->data = require __DIR__ . '/MockData.php';
    }

    public function onsiteStaff(): array
    {
        return $this->data['onsiteStaff'];
    }

    public function recentActivity(): array
    {
        return $this->data['recentActivity'];
    }

    public function attendanceLogs(): array
    {
        return $this->data['attendanceLogs'];
    }

    public function quickActions(): array
    {
        return [
            ['label' => 'QR Generator', 'href' => '/qr-generator', 'variant' => 'light'],
            ['label' => 'Attendance Logs', 'href' => '/attendance-logs', 'variant' => 'light'],
            ['label' => 'Sheets', 'href' => '/sheets', 'variant' => 'light'],
        ];
    }

    private array $clockEvents = [
    [
        'id'        => 1,
        'staff'     => 'Amara Nwosu',
        'type'      => 'Clock In',
        'timestamp' => '2026-05-27 08:02:14',
        'device'    => 'Terminal A',
        'location'  => 'Main Office',
        'sync'      => 'Synced',
    ],
    [
        'id'        => 2,
        'staff'     => 'Amara Nwosu',
        'type'      => 'Clock Out',
        'timestamp' => '2026-05-27 17:05:33',
        'device'    => 'Terminal A',
        'location'  => 'Main Office',
        'sync'      => 'Synced',
    ],
    [
        'id'        => 3,
        'staff'     => 'Sipho Dlamini',
        'type'      => 'Clock In',
        'timestamp' => '2026-05-27 07:58:01',
        'device'    => 'Terminal B',
        'location'  => 'Warehouse',
        'sync'      => 'Synced',
    ],
    [
        'id'        => 4,
        'staff'     => 'Sipho Dlamini',
        'type'      => 'Clock Out',
        'timestamp' => '2026-05-27 16:30:44',
        'device'    => 'Terminal B',
        'location'  => 'Warehouse',
        'sync'      => 'Pending',
    ],
    [
        'id'        => 5,
        'staff'     => 'Naledi Khumalo',
        'type'      => 'Clock In',
        'timestamp' => '2026-05-27 09:15:22',
        'device'    => 'Mobile App',
        'location'  => 'Remote',
        'sync'      => 'Synced',
    ],
    [
        'id'        => 6,
        'staff'     => 'Naledi Khumalo',
        'type'      => 'Clock Out',
        'timestamp' => '2026-05-27 18:01:09',
        'device'    => 'Mobile App',
        'location'  => 'Remote',
        'sync'      => 'Synced',
    ],
    [
        'id'        => 7,
        'staff'     => 'Themba Mthembu',
        'type'      => 'Clock In',
        'timestamp' => '2026-05-26 08:45:00',
        'device'    => 'Terminal A',
        'location'  => 'Main Office',
        'sync'      => 'Synced',
    ],
    [
        'id'        => 8,
        'staff'     => 'Themba Mthembu',
        'type'      => 'Clock Out',
        'timestamp' => '2026-05-26 17:30:18',
        'device'    => 'Terminal A',
        'location'  => 'Main Office',
        'sync'      => 'Synced',
    ],
];

    private array $auditTrail = [
    [
        'id'        => 1,
        'timestamp' => '2026-05-27 14:30:00',
        'actor'     => 'Admin Jane',
        'action'    => 'EDIT',
        'oldValue'  => '17:00',
        'newValue'  => '17:05',
        'details'   => 'Modified clock-out time for Amara Nwosu (17:00 → 17:05)',
    ],
    [
        'id'        => 2,
        'timestamp' => '2026-05-27 11:12:45',
        'actor'     => 'Admin Jane',
        'action'    => 'OVERRIDE',
        'oldValue'  => 'Not recorded',
        'newValue'  => '08:01',
        'details'   => 'Manual clock-in added for Sipho Dlamini',
    ],
    [
        'id'        => 3,
        'timestamp' => '2026-05-26 16:55:10',
        'actor'     => 'Admin Kobus',
        'action'    => 'DELETE',
        'oldValue'  => '18:01',
        'newValue'  => 'Removed',
        'details'   => 'Removed duplicate clock-out entry for Naledi Khumalo',
    ],
    [
        'id'        => 4,
        'timestamp' => '2026-05-26 09:03:22',
        'actor'     => 'Admin Kobus',
        'action'    => 'EDIT',
        'oldValue'  => 'Unknown',
        'newValue'  => 'Terminal A',
        'details'   => 'Corrected device from "Unknown" to "Terminal A" for Themba Mthembu',
    ],
];

    public function getClockEvents(): array
    {
        return $this->clockEvents;
    }

    public function getAuditTrail(): array
    {
        return $this->auditTrail;
    }

}
