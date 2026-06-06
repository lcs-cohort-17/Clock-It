<?php

declare(strict_types=1);

$employees = [
    ['name' => 'Shaheed Karlie', 'id' => 'EMP001', 'department' => 'Engineering'],
    ['name' => 'Imaan Cummings', 'id' => 'EMP002', 'department' => 'Sales'],
    ['name' => 'Will Mxbanisi', 'id' => 'EMP003', 'department' => 'Marketing'],
    ['name' => 'Joshua Jacobs', 'id' => 'EMP004', 'department' => 'Finance'],
    ['name' => 'Mujahid Ariefdien', 'id' => 'EMP005', 'department' => 'Engineering'],
    ['name' => 'Nina Lewis', 'id' => 'EMP006', 'department' => 'Sales'],
    ['name' => 'Ebrahim Easton', 'id' => 'EMP007', 'department' => 'Marketing'],
    ['name' => 'Taaraa Haron', 'id' => 'EMP008', 'department' => 'Accounting'],
    ['name' => 'Natheefah Rayners', 'id' => 'EMP009', 'department' => 'Engineering'],
    ['name' => 'Keanu Visagie', 'id' => 'EMP010', 'department' => 'Sales'],
    ['name' => 'Qaasim Davids', 'id' => 'EMP011', 'department' => 'Marketing'],
    ['name' => 'Aiden Damon', 'id' => 'EMP012', 'department' => 'Accounting'],
];
$statuses = ['Present', 'Present', 'Late', 'Present', 'Absent', 'Half Day', 'Holiday'];
$currentDate = new DateTimeImmutable('today');
$records = [];

for ($index = 0; $index < 65; $index++) {
    // Modify date to find the previous weekday
    while (in_array((int)$currentDate->format('N'), [6, 7], true)) {
        $currentDate = $currentDate->modify('-1 day');
    }

    $employee = $employees[$index % count($employees)];
    $status = $statuses[$index % count($statuses)];
    $hours = match ($status) {
        'Present' => 8 + (($index % 5) / 10),
        'Late' => 7 + (($index % 8) / 10),
        'Half Day' => 4.0,
        default => 0.0,
    };
    $hasClockTimes = !in_array($status, ['Absent', 'Holiday'], true);
    $checkInHour = $status === 'Late' ? 9 : 8;
    $checkInMinute = ($index * 7) % 60;

    $records[] = [
        'id' => 'ATT-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
        'employeeName' => $employee['name'],
        'employeeId' => $employee['id'],
        'department' => $employee['department'],
        'date' => $currentDate->format('Y-m-d'),
        'checkInTime' => $hasClockTimes ? sprintf('%02d:%02d', $checkInHour, $checkInMinute) : '--:--',
        'checkOutTime' => $hasClockTimes ? ($status === 'Half Day' ? '12:30' : sprintf('17:%02d', ($index * 3) % 60)) : '--:--',
        'status' => $status,
        'workingHours' => $hours,
    ];

    $currentDate = $currentDate->modify('-1 day');
}

return $records;
