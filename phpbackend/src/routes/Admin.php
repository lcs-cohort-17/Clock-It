<?php

require_once __DIR__ . '/../controllers/AttendanceController.php';

$attendanceController = new AttendanceController();

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $_SERVER['REQUEST_URI'] === '/api/admin/export/sheets'
) {
    $response = $attendanceController->exportToSheets();

    http_response_code($response['status']);

    foreach ($response['headers'] as $name => $value) {
        header("{$name}: {$value}");
    }

    echo json_encode($response['body']);
}