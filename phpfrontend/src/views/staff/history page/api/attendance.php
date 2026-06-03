<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/data.php';

// Get employee filter if provided
$employeeId = isset($_GET['employee_id']) ? $_GET['employee_id'] : null;

if ($employeeId) {
    // Filter data for specific employee
    $filteredData = array_filter($mockAttendanceData, function($record) use ($employeeId) {
        return $record['employeeId'] === $employeeId;
    });
    echo json_encode(array_values($filteredData));
} else {
    // Return all data
    echo json_encode($mockAttendanceData);
}
?>