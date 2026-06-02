<?php

return [

    'onsiteStaff' => [
        [
            'name' => 'Sipho Dlamini',
            'employee_id' => 'EMP001',
            'role' => 'Staff',
            'signedInAt' => '07:55'
        ],
        [
            'name' => 'Thandi Mokoena',
            'employee_id' => 'EMP002',
            'role' => 'Staff',
            'signedInAt' => '08:12'
        ],
        [
            'name' => 'Nandi Khumalo',
            'employee_id' => 'EMP003',
            'role' => 'Staff',
            'signedInAt' => '07:48'
        ]
    ],

    'recentActivity' => [
        [
            'name' => 'Sipho Dlamini',
            'action' => 'Clock In',
            'timestamp' => '2026-05-28 07:55:00'
        ],
        [
            'name' => 'Thandi Mokoena',
            'action' => 'Clock In',
            'timestamp' => '2026-05-28 08:12:00'
        ],
        [
            'name' => 'Lerato Molefe',
            'action' => 'Absent',
            'timestamp' => '2026-05-28 00:00:00'
        ],
        [
            'name' => 'Sipho Dlamini',
            'action' => 'Clock Out',
            'timestamp' => '2026-05-28 17:02:00'
        ]
    ],



    'attendanceLogs' => [

        [
            'employee_name' => 'Sipho Dlamini',
            'employee_id' => 'EMP001',
            'clock_in' => '2026-05-28 07:55:00',
            'clock_out' => '2026-05-28 17:02:00',
            'status' => 'Present',
        ],

        [
            'employee_name' => 'Thandi Mokoena',
            'employee_id' => 'EMP002',
            'clock_in' => '2026-05-28 08:12:00',
            'clock_out' => '2026-05-28 17:04:00',
            'status' => 'Late',
        ],

        [
            'employee_name' => 'Nandi Khumalo',
            'employee_id' => 'EMP003',
            'clock_in' => '2026-05-28 07:48:00',
            'clock_out' => '2026-05-28 16:58:00',
            'status' => 'Present',
        ],

        [
            'employee_name' => 'Lerato Molefe',
            'employee_id' => 'EMP004',
            'clock_in' => null,
            'clock_out' => null,
            'status' => 'Absent',
        ],

        [
            'employee_name' => 'Kagiso Maseko',
            'employee_id' => 'EMP005',
            'clock_in' => '2026-05-28 08:03:00',
            'clock_out' => '2026-05-28 17:10:00',
            'status' => 'Present',
        ],

        

        // keep the rest of your records here...
    ]
];

