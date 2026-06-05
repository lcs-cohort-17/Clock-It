<?php

require_once __DIR__ . '/../controllers/AttendanceController.php';

$attendanceController = new AttendanceController();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' &&
    in_array($path, ['/api/admin/export/sheets', '/api/admin/sheets/export'], true)
) {
    $response = $attendanceController->exportToSheets();

    http_response_code($response['status']);

    foreach ($response['headers'] as $name => $value) {
        header("{$name}: {$value}");
    }

    echo json_encode($response['body']);
}
