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

// =============================================
// HEADERS
// =============================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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
// QR ADMIN GENERATE
// =============================================
if ($method === 'POST' && $path === '/admin/qr/generate') {

    // FIXED BUG: capture result
    $authResult = $auth->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }

    $token = bin2hex(random_bytes(16)); // 32-char token

    // FIXED: enforce ticket types
    $type = $input['type'] ?? null;
    if (!in_array($type, ['clock_in', 'clock_out'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid type. Must be clock_in or clock_out'
        ]);
        exit;
    }

    $expiresAt = date('Y-m-d H:i:s', time() + 60);

    // FIXED: safe created_by extraction
    $createdBy = $request['user']['user_id'] ?? null;

    $stmt = $db->prepare("
        INSERT INTO qr_codes (token, type, expires_at, created_by)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $token,
        $type,
        $expiresAt,
        $createdBy
    ]);

    echo json_encode([
        'success' => true,
        'qr_token' => $token,
        'type' => $type,
        'expires_at' => $expiresAt
    ]);
    exit;
}

// =============================================
// QR VALIDATION (PUBLIC)
// =============================================
if ($method === 'POST' && $path === '/scan/validate') {

    $token = $input['token'] ?? null;

    if (!$token) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Token required']);
        exit;
    }

    $stmt = $db->prepare("
        SELECT token, type, expires_at, used_at
        FROM qr_codes
        WHERE token = ?
    ");

    $stmt->execute([$token]);
    $qr = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$qr) {
        http_response_code(404);
        echo json_encode(['valid' => false, 'error' => 'Not found']);
        exit;
    }

    if ($qr['used_at']) {
        http_response_code(410);
        echo json_encode(['valid' => false, 'error' => 'Already used']);
        exit;
    }

    if (strtotime($qr['expires_at']) < time()) {
        http_response_code(410);
        echo json_encode(['valid' => false, 'error' => 'Expired']);
        exit;
    }

    echo json_encode([
        'valid' => true,
        'type' => $qr['type']
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