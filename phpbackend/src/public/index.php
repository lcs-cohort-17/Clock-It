<?php
// Main file to handle incoming requests and route them to the appropriate controllers.

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/GoogleSheetsModel.php';
require_once __DIR__ . '/../services/GoogleSheetsService.php';
require_once __DIR__ . '/../controllers/GoogleSheetsController.php';
require_once __DIR__ . '/../controllers/AttendanceController.php';
require_once __DIR__ . '/../models/AdminDashboardDb.php';
require_once __DIR__ . '/../controllers/AdminDashboardController.php';
require_once __DIR__ . '/../routes/AdminDashboardRoutes.php';
require_once __DIR__ . '/../config/Database.php';
use Config\Database;



$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

if ($scriptName !== '' && str_starts_with($path, $scriptName)) {
    $path = substr($path, strlen($scriptName)) ?: '/';
}

if ($path === '/index.php') {
    $path = '/';
}

if (str_starts_with($path, '/index.php/')) {
    $path = substr($path, strlen('/index.php')) ?: '/';
}

header('Content-Type: application/json');

// ── Admin dashboard API (recent activity / onsite) ─────────────────────────────
if (
    $method === 'GET' &&
    in_array($path, ['/api/admin/recent-activity', '/api/admin/onsite', '/api/admin/stats'], true)
) {
    $database = Database::getInstance();
    $dashboardModel = new AdminDashboardModel($database->getConnection());
    $dashboardController = new AdminDashboardController($dashboardModel);


    $router = new AdminDashboardRouter($dashboardController);
    $routerPath = substr($path, strlen('/api/admin')) ?: '/';
    $response = $router->dispatch($method, $routerPath);


    http_response_code($response['status']);

    foreach ($response['headers'] as $name => $value) {
        header("{$name}: {$value}");
    }

    echo json_encode($response['body']);
    return;
}

if ($method === 'GET' && $path === '/api/admin/attendance') {
    $controller = new AttendanceController();
    $response = $controller->index();

    http_response_code($response['status']);

    foreach ($response['headers'] as $name => $value) {
        header("{$name}: {$value}");
    }

    echo json_encode($response['body']);
    return;
}

if ($method === 'GET' && $path === '/api/admin/sheets/status') {
    $controller = new GoogleSheetsController();

    echo json_encode($controller->status());
    return;
}

if ($method === 'POST' && $path === '/api/admin/sheets/export') {
    $controller = new AttendanceController();
    $response = $controller->exportToSheets();

    http_response_code($response['status']);

    foreach ($response['headers'] as $name => $value) {
        header("{$name}: {$value}");
    }

    echo json_encode($response['body']);
    return;
}

if ($method === 'POST' && $path === '/api/admin/sheets/sync') {
    $controller = new GoogleSheetsController();
    $response = $controller->sync();

    echo json_encode($response);
    return;
}

if ($method === 'POST' && $path === '/api/admin/sheets/push') {
    $controller = new GoogleSheetsController();
    $response = $controller->pushPendingAttendance();

    echo json_encode($response);
    return;
}

if ($method === 'GET' && $path === '/api/admin/sheets/settings') {
    $controller = new GoogleSheetsController();
    $response = $controller->getSettings();

    echo json_encode($response);
    return;
}

if ($method === 'POST' && $path === '/api/admin/sheets/settings') {
    $controller = new GoogleSheetsController();
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $response = $controller->updateSettings($body);

    echo json_encode($response);
    return;
}

if ($method === 'POST' && $path === '/api/admin/sheets/connect') {
    $controller = new GoogleSheetsController();
    $response = $controller->connect();

    echo json_encode($response);
    return;
}

if ($method === 'POST' && $path === '/api/admin/sheets/disconnect') {
    $controller = new GoogleSheetsController();
    $response = $controller->disconnect();

    echo json_encode($response);
    return;
}

ob_start();
require_once __DIR__ . '/../routes/Admin.php';
$adminOutput = ob_get_clean();

if ($adminOutput !== '') {
    echo $adminOutput;
    return;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
