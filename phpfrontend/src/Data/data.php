<?php
// Mock employee data
$EMPLOYEES = [
    ['name' => 'Shaheed Karlie', 'id' => 'EMP001', 'dept' => 'Engineering'],
    ['name' => 'Imaan Cummings', 'id' => 'EMP002', 'dept' => 'Sales'],
    ['name' => 'Will Mxbanisi', 'id' => 'EMP003', 'dept' => 'Marketing'],
    ['name' => 'Joshua Jacobs', 'id' => 'EMP004', 'dept' => 'Finance'],
    ['name' => 'Mujahid Ariefdien', 'id' => 'EMP005', 'dept' => 'Engineering'],
    ['name' => 'Nina Lewis', 'id' => 'EMP006', 'dept' => 'Sales'],
    ['name' => 'Ebrahim Easton', 'id' => 'EMP007', 'dept' => 'Marketing'],
    ['name' => 'Taaraa Haron', 'id' => 'EMP008', 'dept' => 'Accounting'],
    ['name' => 'Natheefah Rayners', 'id' => 'EMP009', 'dept' => 'Engineering'],
    ['name' => 'Keanu Visagie', 'id' => 'EMP010', 'dept' => 'Sales'],
    ['name' => 'Qaasim Davids', 'id' => 'EMP011', 'dept' => 'Marketing'],
    ['name' => 'Aiden Damon', 'id' => 'EMP012', 'dept' => 'Accounting']
];

function getRandomDate($daysBack) {
    do {
        $date = new DateTime();
        $date->modify('-' . rand(0, $daysBack) . ' days');
        $dayOfWeek = (int)$date->format('N'); // 1 (Mon) - 7 (Sun)
    } while ($dayOfWeek === 6 || $dayOfWeek === 7);
    return $date->format('Y-m-d');
}

function getRandomStatus() {
    $statuses = ['Present', 'Present', 'Present', 'Late', 'Absent', 'Half Day', 'Holiday'];
    return $statuses[array_rand($statuses)];
}

function getWorkingHours($status) {
    switch ($status) {
        case 'Present': return round(8 + (rand(0, 5) / 10), 1);
        case 'Late': return round(7 + rand(0, 10) / 10, 1);
        case 'Half Day': return 4;
        default: return 0;
    }
}

function getCheckInTime($status) {
    if ($status === 'Absent' || $status === 'Holiday') return '--:--';
    $hour = $status === 'Late' ? 9 + rand(0, 1) : 8;
    $minute = rand(0, 59);
    return sprintf("%02d:%02d", $hour, $minute);
}

function getCheckOutTime($status, $workingHours) {
    if ($status === 'Absent' || $status === 'Holiday') return '--:--';
    if ($status === 'Half Day') return '12:30';
    $baseHour = 17;
    $extraMinutes = round(($workingHours - floor($workingHours)) * 60);
    return sprintf("%02d:%02d", $baseHour, $extraMinutes);
}

// Generate mock attendance data
$mockAttendanceData = [];

for ($i = 0; $i < 65; $i++) {
    $employee = $EMPLOYEES[$i % count($EMPLOYEES)];
    $status = getRandomStatus();
    $workingHours = getWorkingHours($status);
    
    $mockAttendanceData[] = [
        'id' => 'ATT-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
        'employeeName' => $employee['name'],
        'employeeId' => $employee['id'],
        'date' => getRandomDate(90),
        'checkInTime' => getCheckInTime($status),
        'checkOutTime' => getCheckOutTime($status, $workingHours),
        'status' => $status,
        'workingHours' => $workingHours,
        'department' => $employee['dept']
    ];
}

// Sort by date (most recent first)
usort($mockAttendanceData, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>
