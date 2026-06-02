<?php

// =============================================
// ERROR HANDLING
// =============================================
error_reporting(E_ALL);
ini_set('display_errors', 0); // Change to 1 in development

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
// ROUTING - Use your own simple router
// =============================================

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove /api prefix if present
$path = preg_replace('#^/api#', '', $path);

error_log("[DEBUG] $method $path");

// Get JSON input for POST/PATCH/PUT requests
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// =============================================
// TEST ROUTE
// =============================================
if ($method === 'GET' && $path === '/test') {
    echo json_encode(['message' => 'Test route works!']);
    exit;
}

// =============================================
// PROFILE ROUTES - Your existing routes
// =============================================

// Load your controllers and routes
use App\Models\ProfileDb;
use Controllers\ProfileController;

$model = new ProfileDb();
$controller = new ProfileController($model);

// GET all users
if ($method === 'GET' && $path === '/admin/users') {
    $controller->adminGettingAllUsers();
    exit;
}

// GET user by employee_id
if ($method === 'GET' && preg_match('#^/admin/users/([^/]+)$#', $path, $matches)) {
    $controller->getProfileById($matches[1]);
    exit;
}

// POST - Create user
if ($method === 'POST' && $path === '/admin/users') {
    $controller->adminCreatingUser($input);
    exit;
}

// PATCH - Update user
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)$#', $path, $matches)) {
    $controller->adminUpdatingUser($matches[1], $input);
    exit;
}

// DELETE - Soft delete user
if ($method === 'DELETE' && preg_match('#^/admin/users/([^/]+)$#', $path, $matches)) {
    $controller->adminDeletingUser($matches[1]);
    exit;
}

// POST - Login
if ($method === 'POST' && $path === '/login') {
    $controller->loginProfile($input);
    exit;
}

// POST - Clear cache
if ($method === 'POST' && $path === '/admin/users/clear-cache') {
    $controller->clearCache($input);
    exit;
}

// PATCH - Update password
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)/update-password$#', $path, $matches)) {
    $controller->updatePassword($matches[1], $input);
    exit;
}

// PATCH - Reset password (admin)
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)/reset-password$#', $path, $matches)) {
    $controller->resetPassword($matches[1]);
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