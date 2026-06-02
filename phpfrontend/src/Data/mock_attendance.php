<?php
$mockAttendance = [
    ['id' => 1, 'date' => '2026-03-11', 'staff' => 'Lerato Khumalo', 'type' => 'Clock In', 'timestamp' => '2026-03-11 08:12 AM', 'device' => 'Mobile App', 'location' => 'Main HQ', 'syncStatus' => 'Synced', 'hours' => '8.0 hrs', 'clockOut' => '04:12 PM'],

    ['id' => 2, 'date' => '2026-03-12', 'staff' => 'Thabo Ndlovu', 'type' => 'Clock Out', 'timestamp' => '2026-03-12 05:10 PM', 'device' => 'Web Portal', 'location' => 'Remote', 'syncStatus' => 'Pending', 'hours' => '8.2 hrs', 'clockIn' => '09:00 AM'],

    ['id' => 3, 'date' => '2026-03-13', 'staff' => 'Amanda Sithole', 'type' => 'Clock In', 'timestamp' => '2026-03-13 07:55 AM', 'device' => 'Biometric Scanner', 'location' => 'Branch Office', 'syncStatus' => 'Synced', 'hours' => '8.5 hrs', 'clockOut' => '04:25 PM'],

    ['id' => 4, 'date' => '2026-03-14', 'staff' => 'Sipho Dlamini', 'type' => 'Clock In', 'timestamp' => '2026-03-14 08:30 AM', 'device' => 'Mobile App', 'location' => 'Warehouse', 'syncStatus' => 'Failed', 'hours' => '7.5 hrs', 'clockOut' => '04:00 PM'],

    ['id' => 5, 'date' => '2026-03-15', 'staff' => 'Naledi Mokoena', 'type' => 'Clock Out', 'timestamp' => '2026-03-15 05:05 PM', 'device' => 'Desktop App', 'location' => 'Main HQ', 'syncStatus' => 'Synced', 'hours' => '8.0 hrs', 'clockIn' => '09:05 AM'],

    ['id' => 6, 'date' => '2026-03-16', 'staff' => 'Brian Zulu', 'type' => 'Clock In', 'timestamp' => '2026-03-16 08:20 AM', 'device' => 'Tablet', 'location' => 'Remote', 'syncStatus' => 'Pending', 'hours' => '7.8 hrs', 'clockOut' => '04:08 PM'],

    ['id' => 7, 'date' => '2026-03-17', 'staff' => 'Grace Mabena', 'type' => 'Clock In', 'timestamp' => '2026-03-17 08:00 AM', 'device' => 'Mobile App', 'location' => 'Main HQ', 'syncStatus' => 'Synced', 'hours' => '8.0 hrs', 'clockOut' => '04:00 PM'],

    ['id' => 8, 'date' => '2026-03-18', 'staff' => 'Peter Nkosi', 'type' => 'Clock Out', 'timestamp' => '2026-03-18 05:20 PM', 'device' => 'Web Portal', 'location' => 'Branch Office', 'syncStatus' => 'Synced', 'hours' => '8.3 hrs', 'clockIn' => '09:00 AM'],

    ['id' => 9, 'date' => '2026-03-19', 'staff' => 'Faith Tshabalala', 'type' => 'Clock In', 'timestamp' => '2026-03-19 08:40 AM', 'device' => 'Biometric Scanner', 'location' => 'Warehouse', 'syncStatus' => 'Pending', 'hours' => '7.9 hrs', 'clockOut' => '04:35 PM'],

    ['id' => 10, 'date' => '2026-03-20', 'staff' => 'Daniel Mthembu', 'type' => 'Clock In', 'timestamp' => '2026-03-20 08:15 AM', 'device' => 'Desktop App', 'location' => 'Remote', 'syncStatus' => 'Synced', 'hours' => '8.1 hrs', 'clockOut' => '04:20 PM'],

    ['id' => 11, 'date' => '2026-03-21', 'staff' => 'Ayanda Nene', 'type' => 'Clock Out', 'timestamp' => '2026-03-21 05:00 PM', 'device' => 'Mobile App', 'location' => 'Main HQ', 'syncStatus' => 'Failed', 'hours' => '8.0 hrs', 'clockIn' => '09:00 AM'],

    ['id' => 12, 'date' => '2026-03-22', 'staff' => 'Jason Mabuza', 'type' => 'Clock In', 'timestamp' => '2026-03-22 08:05 AM', 'device' => 'Tablet', 'location' => 'Branch Office', 'syncStatus' => 'Synced', 'hours' => '8.4 hrs', 'clockOut' => '04:29 PM'],

    ['id' => 13, 'date' => '2026-03-23', 'staff' => 'Precious Khoza', 'type' => 'Clock In', 'timestamp' => '2026-03-23 08:11 AM', 'device' => 'Web Portal', 'location' => 'Remote', 'syncStatus' => 'Pending', 'hours' => '8.0 hrs', 'clockOut' => '04:11 PM'],

    ['id' => 14, 'date' => '2026-03-24', 'staff' => 'Chris Baloyi', 'type' => 'Clock Out', 'timestamp' => '2026-03-24 05:12 PM', 'device' => 'Biometric Scanner', 'location' => 'Warehouse', 'syncStatus' => 'Synced', 'hours' => '8.2 hrs', 'clockIn' => '09:00 AM'],

    ['id' => 15, 'date' => '2026-03-25', 'staff' => 'Linda Molefe', 'type' => 'Clock In', 'timestamp' => '2026-03-25 07:58 AM', 'device' => 'Desktop App', 'location' => 'Main HQ', 'syncStatus' => 'Synced', 'hours' => '8.6 hrs', 'clockOut' => '04:34 PM'],

    ['id' => 16, 'date' => '2026-03-26', 'staff' => 'Kevin Hlatshwayo', 'type' => 'Clock In', 'timestamp' => '2026-03-26 08:18 AM', 'device' => 'Mobile App', 'location' => 'Remote', 'syncStatus' => 'Failed', 'hours' => '7.6 hrs', 'clockOut' => '03:54 PM'],

    ['id' => 17, 'date' => '2026-03-27', 'staff' => 'Nomsa Zwane', 'type' => 'Clock Out', 'timestamp' => '2026-03-27 05:30 PM', 'device' => 'Tablet', 'location' => 'Branch Office', 'syncStatus' => 'Pending', 'hours' => '8.5 hrs', 'clockIn' => '09:00 AM'],

    ['id' => 18, 'date' => '2026-03-28', 'staff' => 'Richard Gumede', 'type' => 'Clock In', 'timestamp' => '2026-03-28 08:09 AM', 'device' => 'Web Portal', 'location' => 'Warehouse', 'syncStatus' => 'Synced', 'hours' => '8.0 hrs', 'clockOut' => '04:09 PM'],

    ['id' => 19, 'date' => '2026-03-29', 'staff' => 'Samantha Buthelezi', 'type' => 'Clock In', 'timestamp' => '2026-03-29 08:33 AM', 'device' => 'Mobile App', 'location' => 'Main HQ', 'syncStatus' => 'Synced', 'hours' => '7.7 hrs', 'clockOut' => '04:10 PM'],

    ['id' => 20, 'date' => '2026-03-30', 'staff' => 'Mpho Radebe', 'type' => 'Clock Out', 'timestamp' => '2026-03-30 05:18 PM', 'device' => 'Desktop App', 'location' => 'Remote', 'syncStatus' => 'Pending', 'hours' => '8.3 hrs', 'clockIn' => '09:00 AM'],
];

return $mockAttendance;
