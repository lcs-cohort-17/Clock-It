<?php

// =============================================
// ERROR HANDLING
// =============================================
error_reporting(E_ALL);
ini_set('display_errors', 0);

set_exception_handler(function ($exception) {
    error_log('UNCAUGHT EXCEPTION: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal Server Error', 'message' => $exception->getMessage()]);
    exit;
});

// =============================================
// SETUP
// =============================================
require __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Models\ProfileDb;
use App\Models\QrCodeDb;
use App\Middleware\AuthMiddleware;
use Controllers\ProfileController;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

// Initialize Services
$auth = new AuthMiddleware($_ENV['JWT_SECRET']);
$model = new ProfileDb();
$qrModel = new QrCodeDb();
$controller = new ProfileController($model);

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
$path = preg_replace('#^/api#', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// [EXISTING PROFILE ROUTES]
if ($method === 'GET' && $path === '/test') { echo json_encode(['message' => 'Test route works!']); exit; }
if ($method === 'GET' && $path === '/admin/users') { $controller->adminGettingAllUsers(); exit; }
if ($method === 'GET' && preg_match('#^/admin/users/([^/]+)$#', $path, $matches)) { $controller->getProfileById($matches[1]); exit; }
if ($method === 'POST' && $path === '/admin/users') { $controller->adminCreatingUser($input); exit; }
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)$#', $path, $matches)) { $controller->adminUpdatingUser($matches[1], $input); exit; }
if ($method === 'DELETE' && preg_match('#^/admin/users/([^/]+)$#', $path, $matches)) { $controller->adminDeletingUser($matches[1]); exit; }
if ($method === 'POST' && $path === '/login') { $controller->loginProfile($input); exit; }
if ($method === 'POST' && $path === '/admin/users/clear-cache') { $controller->clearCache($input); exit; }
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)/update-password$#', $path, $matches)) { $controller->updatePassword($matches[1], $input); exit; }
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)/reset-password$#', $path, $matches)) { $controller->resetPassword($matches[1]); exit; }

// =============================================
// QR CODE ROUTES (NEW)
// =============================================

// Generate QR (Admin)
if ($method === 'POST' && $path === '/admin/qr/generate') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $guard = $auth->requireAdmin(['headers' => ['authorization' => $authHeader]]);
    
    if ($guard !== null) {
        http_response_code($guard['status']);
        echo json_encode($guard['body']);
        exit;
    }

    $token = bin2hex(random_bytes(16));
    $type = $input['type'] ?? 'clock_in';
    $userId = 'admin_user'; // Replace with logic from your token if needed

    $qrModel->createToken($token, $type, $userId);
    http_response_code(201);
    echo json_encode(['success' => true, 'token' => $token]);
    exit;
}

// Validate QR (Public)
if ($method === 'POST' && $path === '/scan/validate') {
    $token = $input['token'] ?? '';
    $data = $qrModel->validateAndUseToken($token);

    if ($data) {
        echo json_encode(['valid' => true, 'type' => $data['type']]);
    } else {
        http_response_code(400);
        echo json_encode(['valid' => false, 'error' => 'Invalid, expired, or used token']);
    }
    exit;
}

// =============================================
// 404 NOT FOUND
// =============================================
http_response_code(404);
echo json_encode(['success' => false, 'error' => 'Route not found: ' . $method . ' ' . $path]);