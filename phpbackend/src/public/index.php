<?php
// =============================================
// ERROR HANDLING
// =============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

set_exception_handler(function ($exception) {
    error_log('UNCAUGHT EXCEPTION: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal Server Error',
        'message' => $exception->getMessage()
    ]);
    exit;
});

// =============================================
// DOTENV BOOTSTRAP
// =============================================
require __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

$envDir = realpath(__DIR__ . '/../..');
if (!$envDir || !file_exists($envDir . '/.env')) {
    $envDir = __DIR__ . '/../..';
}

$dotenv = Dotenv::createImmutable($envDir);
$dotenv->load();

$jwtSecret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?? null;

if (!$jwtSecret) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Missing JWT secret'
    ]);
    exit;
}

// =============================================
// AUTOLOAD
// =============================================
if (!class_exists('Middleware\AuthMiddleware')) {
    require_once __DIR__ . '/../middleware/AuthMiddleware.php';
}

require_once __DIR__ . '/../models/AdminDashboardDb.php';
require_once __DIR__ . '/../controllers/AdminDashboardController.php';

// =============================================
// HEADERS
// =============================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =============================================
// REQUEST
// =============================================
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/api#', '', $path);

$input = json_decode(file_get_contents("php://input"), true) ?? [];

$request = [
    'headers' => getallheaders(),
    'method' => $method,
    'path' => $path,
    'body' => $input
];

// =============================================
// DB + AUTH
// =============================================
use Config\Database;
use Middleware\AuthMiddleware;

$db = Database::getInstance()->getConnection();
$auth = new AuthMiddleware($jwtSecret, $db);

// =============================================
// CONTROLLERS
// =============================================
$dashboardModel = new AdminDashboardModel($db);
$dashboardController = new AdminDashboardController($dashboardModel);

// =============================================
// QR SCAN VALIDATION (PUBLIC)
// =============================================
if ($method === 'POST' && $path === '/scan/validate') {

    $token = $input['qr_token'] ?? null;

    if (!$token) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'QR token required']);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM qr_codes WHERE token = ?");
    $stmt->execute([$token]);
    $qr = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$qr) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'QR code not found']);
        exit;
    }

    if (!empty($qr['used_at'])) {
        http_response_code(410);
        echo json_encode(['success' => false, 'error' => 'QR code already used']);
        exit;
    }

    if (strtotime($qr['expires_at']) < time()) {
        http_response_code(410);
        echo json_encode(['success' => false, 'error' => 'QR code expired']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'valid' => true,
        'type' => $qr['type']
    ]);
    exit;
}

// =============================================
// ATTENDANCE CLOCK (STAFF)
// =============================================
if ($method === 'POST' && $path === '/attendance/clock') {

    $authResult = $auth->requireLogin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }

    $user = $request['user'];
    $userId = $user['user_id'];

    $qrToken = $input['qr_token'] ?? null;
    $device = $input['device_info'] ?? null;
    $location = $input['location'] ?? null;

    if (!$qrToken) {
        http_response_code(400);
        echo json_encode(['error' => 'QR token required']);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM qr_codes WHERE token = ?");
    $stmt->execute([$qrToken]);
    $qr = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$qr || !empty($qr['used_at']) || strtotime($qr['expires_at']) < time()) {
        http_response_code(410);
        echo json_encode(['error' => 'Invalid QR']);
        exit;
    }

    $stmt = $db->prepare("SELECT is_active FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);

    if (!$stmt->fetchColumn()) {
        http_response_code(403);
        echo json_encode(['error' => 'User inactive']);
        exit;
    }

    $stmt = $db->prepare("
        SELECT event_type
        FROM attendance_logs
        WHERE user_id = ?
        AND DATE(event_time) = CURDATE()
        ORDER BY event_time DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $last = $stmt->fetchColumn();

    $newType = $qr['type'];

    if ($last === $newType) {
        http_response_code(409);
        echo json_encode(['error' => 'Duplicate action']);
        exit;
    }

    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO attendance_logs
        (user_id, event_type, event_time, check_in_method, sync_status, location, device_info, qr_code_id, created_at)
        VALUES (?, ?, NOW(), 'qr', 'synced', ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $userId,
        $newType,
        $location,
        $device,
        $qr['id']
    ]);

    $stmt = $db->prepare("UPDATE qr_codes SET used_at = NOW() WHERE id = ?");
    $stmt->execute([$qr['id']]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => "Clock {$newType} successful"
    ]);
    exit;
}

// =============================================
// ADMIN DASHBOARD (ADDED HERE)
// =============================================

// STATS
if ($method === 'GET' && $path === '/api/admin/dashboard/stats') {

    $authResult = $auth->requireAdmin($request);
    if ($authResult) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }

    $result = $dashboardController->stats();
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}

// ONSITE
if ($method === 'GET' && $path === '/api/admin/dashboard/onsite') {

    $authResult = $auth->requireAdmin($request);
    if ($authResult) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }

    $result = $dashboardController->onsite();
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}

// RECENT ACTIVITY
if ($method === 'GET' && $path === '/api/admin/dashboard/recent-activity') {

    $authResult = $auth->requireAdmin($request);
    if ($authResult) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }

    $result = $dashboardController->recentActivity();
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}

// =============================================
// ADMIN QR GENERATE
// =============================================
if ($method === 'POST' && $path === '/admin/qr/generate') {

    $authResult = $auth->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }

    $user = $request['user'];
    $createdBy = $user['user_id'];

    $token = bin2hex(random_bytes(16));
    $type = $input['type'] ?? 'clock_in';
    $expiresAt = date('Y-m-d H:i:s', time() + 60);

    $stmt = $db->prepare("
        INSERT INTO qr_codes (token, type, expires_at, created_by)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$token, $type, $expiresAt, $createdBy]);

    echo json_encode([
        'success' => true,
        'qr_token' => $token,
        'expires_at' => $expiresAt
    ]);
    exit;
}

// =============================================
// 404
// =============================================
http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => 'Route not found'
]);
exit;