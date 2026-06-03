<?php
// =============================================
// ERROR HANDLING
// =============================================
error_reporting(E_ALL);
ini_set('display_errors', 1); // Set to 1 for debugging

// Global exception handler
set_exception_handler(function ($exception) {
    error_log('UNCAUGHT EXCEPTION: ' . $exception->getMessage());
    error_log('Stack: ' . $exception->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal Server Error',
        'message' => $exception->getMessage()
    ]);
    exit;
});

// =============================================
// SETUP
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

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =============================================
// ROUTING
// =============================================

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove /api prefix if present
$path = preg_replace('#^/api#', '', $path);

error_log("[DEBUG] $method $path");

// Get JSON input for POST/PATCH/PUT requests
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Build request array for middleware
$request = [
    'headers' => getallheaders(),
    'method' => $method,
    'path' => $path,
    'query' => $_GET,
    'body' => $input
];

// =============================================
// DEBUG: Check headers (MOVED HERE - AFTER $request is defined!)
// =============================================
error_log("DEBUG: All headers: " . print_r(getallheaders(), true));
error_log("DEBUG: Authorization header: " . ($request['headers']['Authorization'] ?? 'NOT FOUND'));
error_log("DEBUG: authorization header: " . ($request['headers']['authorization'] ?? 'NOT FOUND'));

// =============================================
// PROFILE ROUTES
// =============================================

use App\Models\ProfileDb;
use Controllers\ProfileController;
use Middleware\AuthMiddleware;

// Initialize JWT secret
$jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this';
$authMiddleware = new AuthMiddleware($jwtSecret);

$model = new ProfileDb();
$controller = new ProfileController($model);

// =============================================
// TEST ROUTE (PUBLIC - no auth needed)
// =============================================
if ($method === 'GET' && $path === '/test') {
    echo json_encode(['message' => 'Test route works!']);
    exit;
}

// =============================================
// PUBLIC ROUTES (no token required)
// =============================================

// POST - Login
if ($method === 'POST' && $path === '/login') {
    $controller->loginProfile($input);
    exit;
}

// =============================================
// AUTHENTICATED ROUTES (require token)
// =============================================

// GET - Get current user's own profile
if ($method === 'GET' && $path === '/user/profile') {
    $authResult = $authMiddleware->requireLogin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->getCurrentUserProfile($request);
    exit;
}

// GET all users (admin only)
if ($method === 'GET' && $path === '/admin/users') {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->adminGettingAllUsers();
    exit;
}

// PATCH - Soft delete user
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)/deactivate$#', $path, $matches)) {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->softDeleteUser($matches[1]);
    exit;
}

// PATCH - Activate user
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)/activate$#', $path, $matches)) {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->activateUser($matches[1]);
    exit;
}

// GET user by employee_id (admin only)
if ($method === 'GET' && preg_match('#^/admin/users/([^/]+)$#', $path, $matches)) {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->getProfileById($matches[1]);
    exit;
}

// PATCH - Update user (admin only)
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)$#', $path, $matches)) {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->adminUpdatingUser($matches[1], $input);
    exit;
}

// POST - Create user (admin only)
if ($method === 'POST' && $path === '/admin/users') {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->adminCreatingUser($input);
    exit;
}

// PATCH - Reset password (admin)
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)/reset-password$#', $path, $matches)) {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->resetPassword($matches[1]);
    exit;
}

// PATCH - Update own password (requires auth)
if ($method === 'PATCH' && $path === '/user/update-password') {
    $authResult = $authMiddleware->requireLogin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $employee_id = $request['user']['employee_id'] ?? null;
    if (!$employee_id) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    $controller->updatePassword($employee_id, $input);
    exit;
}

// POST - Clear cache (requires auth)
if ($method === 'POST' && $path === '/user/cache/clear') {
    $authResult = $authMiddleware->requireLogin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    // Pass empty body and the authenticated user
    $controller->clearCache([], $request['user'] ?? null);
    exit;
}

// =============================================
// 404 NOT FOUND
// =============================================
http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => 'Route not found: ' . $method . ' ' . $path
]);
exit;