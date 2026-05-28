<?php

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($path === '/api/attendance/clock-events' && $method === 'GET') {
    header('Content-Type: application/json');

    echo json_encode([
        'data' => [
            [
                'staff_name' => 'Joshua Jacobs',
                'employee_id' => 'A-005',
                'event_type' => 'clock_in',
                'timestamp' => date('Y-m-d') . ' 08:00:00',
                'device' => 'Web',
                'location' => 'Cape Town Office',
                'sync_status' => 'synced',
            ],
            [
                'staff_name' => 'Sarah Johnson',
                'employee_id' => 'A-010',
                'event_type' => 'clock_out',
                'timestamp' => date('Y-m-d') . ' 17:00:00',
                'device' => 'Web',
                'location' => 'Cape Town Office',
                'sync_status' => 'synced',
            ],
        ],
    ]);
    exit;
}

$routes = [
    '/' => __DIR__ . '/../src/views/admin/dashboard.php',
    '/admin' => __DIR__ . '/../src/views/admin/dashboard.php',
    '/admin/dashboard' => __DIR__ . '/../src/views/admin/dashboard.php',
    '/login' => __DIR__ . '/../src/views/login.php',
    '/staff/dashboard' => __DIR__ . '/../src/views/staff/dashboard.php',
];

if (isset($routes[$path])) {
    require $routes[$path];
    exit;
}

http_response_code(404);
require __DIR__ . '/../src/views/404.php';
