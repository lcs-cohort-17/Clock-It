<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../types/LeaveInterface.php';
require_once __DIR__ . '/../utils/LeaveValidator.php';
require_once __DIR__ . '/../models/LeaveDb.php';
require_once __DIR__ . '/../controllers/LeaveController.php';
require_once __DIR__ . '/../routes/LeaveRoutes.php';

use Config\Database;
use Dotenv\Dotenv;

// =============================================
// ERROR HANDLING
// =============================================
error_reporting(E_ALL);
ini_set('display_errors', '0');

set_exception_handler(function ($exception) {
    error_log('UNCAUGHT EXCEPTION: ' . $exception->getMessage());
    error_log('Stack: ' . $exception->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error'   => 'Internal Server Error',
        'message' => $exception->getMessage(),
    ], JSON_UNESCAPED_SLASHES);
});

set_error_handler(function ($severity, $message, $file, $line) {
    error_log("ERROR [$severity]: $message in $file on line $line");
});

// =============================================
// BOOTSTRAP
// =============================================
$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

// =============================================
// HELPERS
// =============================================
function sendCorsHeaders(): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
}

function leaveReadJsonBody(): array
{
    $rawBody = file_get_contents('php://input');
    if ($rawBody === false || trim($rawBody) === '') {
        return [];
    }
    $decoded = json_decode($rawBody, true);
    return is_array($decoded) ? $decoded : [];
}

function buildLeaveRequest(): array
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];

    return [
        'method'  => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        'uri'     => parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/',
        'headers' => array_change_key_case($headers, CASE_LOWER),
        'body'    => leaveReadJsonBody(),
        'query'   => $_GET ?? [],
    ];
}

function jsonResponse(array $payload, int $status): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
}

// =============================================
// ROUTER
// =============================================
sendCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$request = buildLeaveRequest();
error_log(sprintf('[Leave API] %s %s', $request['method'], $request['uri']));

try {
    $pdo       = Database::getInstance()->getConnection();
    $model     = new LeaveDbModel(new LeaveDb($pdo));
    $jwtSecret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: '';

    if ($jwtSecret === '') {
        jsonResponse(['success' => false, 'error' => 'JWT secret is not configured.'], 500);
        exit;
    }

    $middleware = new \App\Middleware\AuthMiddleware($jwtSecret);
    $controller = new LeaveController($model, $middleware);

    $response = $middleware->handle($request, function (array $request) use ($controller): array {
        $GLOBALS['auth'] = $request['user'] ?? [];

        ob_start();
        handleLeaveRoutes($controller, $request);
        $body = ob_get_clean();

        return [
            'status' => http_response_code() ?: 200,
            'body'   => $body,
        ];
    });

    if ($response['status'] === 404) {
        jsonResponse([
            'success' => false,
            'error'   => 'Route not found: ' . $request['method'] . ' ' . $request['uri'],
        ], 404);
        exit;
    }

    http_response_code($response['status']);
    header('Content-Type: application/json');
    echo is_array($response['body'])
        ? json_encode($response['body'], JSON_UNESCAPED_SLASHES)
        : $response['body'];
} catch (Throwable $exception) {
    jsonResponse([
        'success' => false,
        'error'   => 'Internal Server Error',
        'message' => $exception->getMessage(),
    ], 500);
}