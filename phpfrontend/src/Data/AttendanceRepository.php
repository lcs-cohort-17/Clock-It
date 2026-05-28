<?php

declare(strict_types=1);

namespace ClockIt\Data;

final class AttendanceRepository
{
    public function onsiteStaff(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Rhea Morgan',
                'role' => 'Site Manager',
                'signedInAt' => '8:12 AM',
                'avatar' => 'RM',
            ],
            [
                'id' => 2,
                'name' => 'Elijah Park',
                'role' => 'Technician',
                'signedInAt' => '8:24 AM',
                'avatar' => 'EP',
            ],
            [
                'id' => 3,
                'name' => 'Maya Chen',
                'role' => 'Field Engineer',
                'signedInAt' => '9:03 AM',
                'avatar' => 'MC',
            ],
        ];
    }

    public function recentActivity(): array
    {
        return [
            [
                'id' => 10,
                'name' => 'Noah Silva',
                'action' => 'Clock Out',
                'timestamp' => '5:01 PM',
                'type' => 'out',
            ],
            [
                'id' => 9,
                'name' => 'Maya Chen',
                'action' => 'Clock In',
                'timestamp' => '9:03 AM',
                'type' => 'in',
            ],
            [
                'id' => 8,
                'name' => 'Elijah Park',
                'action' => 'Clock In',
                'timestamp' => '8:24 AM',
                'type' => 'in',
            ],
            [
                'id' => 7,
                'name' => 'Rhea Morgan',
                'action' => 'Clock In',
                'timestamp' => '8:12 AM',
                'type' => 'in',
            ],
        ];
    }

    public function quickActions(): array
    {
        return [
            ['label' => 'QR Generator', 'href' => '/qr-generator', 'variant' => 'light'],
            ['label' => 'Attendance Logs', 'href' => '/attendance-logs', 'variant' => 'light'],
            ['label' => 'Sheets Integration', 'href' => '/sheets', 'variant' => 'light'],
        ];
    }

    public function sheetsConnected(): bool
    {
        return false;
    }
}
