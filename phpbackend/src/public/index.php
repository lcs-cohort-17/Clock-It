// main file to handle all the incoming requests and route them to the appropriate controllers
<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use Tuupola\Middleware\CorsMiddleware;

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
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$app = AppFactory::create();

// CORS
$app->add(new CorsMiddleware([
    "origin" => ["*"],
    "methods" => ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
    "headers.allow" => ["Authorization", "Content-Type"],
    "headers.expose" => [],
    "credentials" => false,
    "cache" => 0,
]));

// Parse JSON body
$app->addBodyParsingMiddleware();

// =============================================
// HARDCODED AUTH MIDDLEWARE (like in your Node code)
// =============================================
$app->add(function (Request $request, $handler) {
    $request = $request->withAttribute('auth', [
        'userId' => '07cd9434-1b18-4d96-b029-0595b26d067c',
        'role'   => 'admin',
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
$app->group('/api/admin', function ($group) {
    // Include your admin routes here
    require __DIR__ . '/../src/routes/adminDashboardRoutes.php';
});

$app->group('/api/google', function ($group) {
    require __DIR__ . '/../src/routes/googleRoutes.php';
});

$app->group('/api/leaves', function ($group) {
    require __DIR__ . '/../src/routes/leaveRoutes.php';
});

$app->group('/profiles', function ($group) {
    require __DIR__ . '/../src/routes/profileRoutes.php';
});

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