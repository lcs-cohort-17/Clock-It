<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

session_start();

// Dev Login for Testing
if ($requestUri === '/api/dev-login' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $userId = $input['user_id'] ?? null;

    if (!$userId) {
        http_response_code(400);
        echo json_encode(['error' => 'user_id is required']);
        return;
    }

    $_SESSION['user_id'] = $userId;
    echo json_encode(['success' => true, 'message' => 'Dev session created', 'user_id' => $userId]);
    return;
}

// Main Clock Endpoint
if ($requestUri === '/api/attendance/clock' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/AttendanceController.php';
    $controller = new AttendanceController();
    $controller->handle();
    return;
}

http_response_code(404);
echo json_encode(['error' => 'Not Found', 'path' => $requestUri]);