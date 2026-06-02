// main file to handle all the incoming requests and route them to the appropriate controllers
<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

// =============================================
// ERROR HANDLING (PHP equivalent)
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
});

// Global error handler for warnings/notices (similar to unhandledRejection)
set_error_handler(function ($severity, $message, $file, $line) {
    error_log("ERROR [$severity]: $message in $file on line $line");
});

// =============================================
// SETUP
// =============================================
require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$app = AppFactory::create();

// CORS
// Use this instead. It does NOT require any external "CorsMiddleware" class.
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Important: Add this to handle the "OPTIONS" pre-flight requests!
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

// Parse JSON body
$app->addBodyParsingMiddleware();

// =============================================
// HARDCODED AUTH MIDDLEWARE (like in your Node code)
// =============================================
$app->add(function (Request $request, $handler) {
    $request = $request->withAttribute('auth', [
        'userId' => '07cd9434-1b18-4d96-b029-0595b26d067c',
        'role'   => 'employee',
        'token'  => 'mock-token'
    ]);
    
    return $handler->handle($request);
});

// =============================================
// DEBUG LOGGING MIDDLEWARE
// =============================================
$app->add(function (Request $request, $handler) {
    $method = $request->getMethod();
    $uri = $request->getUri()->getPath();
    error_log("[DEBUG] $method $uri");
    
    return $handler->handle($request);
});

// =============================================
// ROUTES
// =============================================

// Test route
$app->get('/test', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode([
        'message' => 'Test route works!'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Mount your route groups
$app->group('/api/admin/dashboard', function ($group) {
    // Include your admin routes here
    require __DIR__ . '/../routes/AdminDashboardRoutes.php';
});
// Other route groups (google, attendance, profiles) are not mounted here
// to avoid requiring files that are not present in this PHP backend copy.

// 404 Not Found Handler
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode([
        'success' => false,
        'error' => 'Route not found: ' . $request->getMethod() . ' ' . $request->getUri()->getPath()
    ]));
    return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
});

// =============================================
// RUN SERVER
// =============================================
$app->run();

echo "Server running on http://localhost:4321\n"; // Only shows in CLI
