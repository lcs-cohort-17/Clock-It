<?php

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once dirname(__DIR__, 2) . '/phpfrontend/src/views/includes/config.php';
require_once dirname(__DIR__, 2) . '/phpfrontend/src/views/includes/functions.php';
require dirname(__DIR__, 2) . '/phpfrontend/src/Data/data.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
$employeeId = isset($_GET['employee_id'])
    ? sanitizeInput((string) $_GET['employee_id'])
    : null;

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$attendanceData = $mockAttendanceData ?? [];

if ($employeeId !== null && $employeeId !== '') {
    $attendanceData = array_values(array_filter(
        $attendanceData,
        static fn (array $record): bool => ($record['employeeId'] ?? null) === $employeeId
    ));
}

if ($action === 'dashboard') {
    $employees = [];
    foreach ($attendanceData as $record) {
        $employees[$record['employeeId']] = $record['employeeName'];
    }

    $today = date('Y-m-d');
    $todayRecords = array_filter(
        $attendanceData,
        static fn (array $record): bool => ($record['date'] ?? null) === $today
    );

    echo json_encode([
        'stats' => [
            'totalEmployees' => count($employees),
            'presentToday' => count(array_filter($todayRecords, static fn (array $record): bool => ($record['status'] ?? null) === 'Present')),
            'lateToday' => count(array_filter($todayRecords, static fn (array $record): bool => ($record['status'] ?? null) === 'Late')),
            'absentToday' => count(array_filter($todayRecords, static fn (array $record): bool => ($record['status'] ?? null) === 'Absent')),
        ],
        'recentRecords' => array_slice($attendanceData, 0, 10),
    ]);
    exit();
}

echo json_encode(array_values($attendanceData));
