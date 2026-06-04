<?php
// =============================================
// ERROR HANDLING
// =============================================
error_reporting(E_ALL);
ini_set('display_errors', 1); // Set to 1 for debugging

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

$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

// =============================================
// MANUAL AUTOLOAD FIX FOR MIDDLEWARE
// =============================================
if (!class_exists('Middleware\AuthMiddleware')) {
    require_once __DIR__ . '/../middleware/AuthMiddleware.php';
}

// =============================================
// CORS HEADERS
// =============================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =============================================
// ROUTING
// =============================================

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/api#', '', $path);

error_log("[DEBUG] $method $path");

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$request = [
    'headers' => getallheaders(),
    'method' => $method,
    'path' => $path,
    'query' => $_GET,
    'body' => $input
];

// =============================================
// DEBUG HEADERS
// =============================================
error_log("DEBUG: Authorization header: " . ($request['headers']['Authorization'] ?? 'NOT FOUND'));

// =============================================
// PROFILE ROUTES
// =============================================

use App\Models\ProfileDb;
use Controllers\ProfileController;
use Middleware\AuthMiddleware;
use Config\Database;

$jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this';
$db = Database::getInstance()->getConnection();

$authMiddleware = new AuthMiddleware($jwtSecret, $db);

$model = new ProfileDb();
$controller = new ProfileController($model);

// =====================================================
// QR CODE MODEL (ADDED/MIHLE)
// =====================================================
use App\Models\QrCodeDb;
$qrModel = new QrCodeDb();


// POST - Forgot password (public)
if ($method === 'POST' && $path === '/forgot-password') {
    $controller->forgotPassword($input);
    exit;
}

// POST - Reset password (public)
if ($method === 'POST' && $path === '/reset-password') {
    $controller->resetPasswordWithToken($input);
    exit;
}

// TEST
if ($method === 'GET' && $path === '/test') {
    echo json_encode(['message' => 'Test route works!']);
    exit;
}

// LOGIN
if ($method === 'POST' && $path === '/login') {
    $controller->loginProfile($input);
    exit;
}

// =====================================================
// AUTH ROUTES
// =====================================================

if ($method === 'GET' && $path === '/user/profile') {
    $authResult = $authMiddleware->requireLogin($request);
    if ($authResult) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->getCurrentUserProfile($request);
    exit;
}

// =====================================================
// ADMIN ROUTES
// =====================================================

if ($method === 'GET' && $path === '/admin/users') {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->adminGettingAllUsers();
    exit;
}

// =====================================================
// QR CODE ROUTES (ADDED/MIHLE)
// =====================================================

// ADMIN: generate QR token (clock_in / clock_out)
if ($method === 'POST' && $path === '/admin/qr/generate') {

    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }

    $userId = $request['user']['user_id'] ?? null;

    if (!$userId) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid admin user']);
        exit;
    }

    $token = bin2hex(random_bytes(16)); // 32 chars
    $type = $input['type'] ?? 'clock_in';

    $qrModel->createToken($token, $type, $userId);

    echo json_encode([
        'success' => true,
        'token' => $token,
        'type' => $type,
        'expires_in' => 60
    ]);
    exit;
}

// PUBLIC: validate QR token
if ($method === 'POST' && $path === '/api/scan/validate') {

    $token = $input['qr_token'] ?? null;

    if (!$token) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'QR token required']);
        exit;
    }

    $qr = $qrModel->validateAndUseToken($token);

    if (!$qr) {
        http_response_code(410);
        echo json_encode([
            'success' => false,
            'error' => 'QR code expired or already used'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'type' => $qr['type']
    ]);
    exit;
}

// =====================================================
// 404
// =====================================================
http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => 'Route not found: ' . $method . ' ' . $path
]);
exit;