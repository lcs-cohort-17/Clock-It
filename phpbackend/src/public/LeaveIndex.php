<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../types/LeaveInterface.php';
require_once __DIR__ . '/../utils/LeaveValidator.php';
require_once __DIR__ . '/../models/LeaveDb.php';
require_once __DIR__ . '/../controllers/LeaveController.php';
require_once __DIR__ . '/../routes/LeaveRoutes.php';

function buildLeaveRequest(): array
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];

    return array_filter([
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        'uri' => parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/',
        'headers' => array_change_key_case($headers, CASE_LOWER),
        'body' => leaveReadJsonBody(),
        'query' => $_GET ?? [],
    ], static fn($value) => $value !== null);
}

function jsonResponse(array $payload, int $status): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
}

try {
    $pdo = Database::getInstance()->getConnection();
    $model = new LeaveDbModel(new LeaveDb($pdo));
    $jwtSecret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: '';

    if ($jwtSecret === '') {
        jsonResponse([
            'message' => 'JWT secret is not configured',
        ], 500);
        return;
    }

    $middleware = new \App\Middleware\AuthMiddleware($jwtSecret);
    $controller = new LeaveController($model, $middleware);
    $request = buildLeaveRequest();

    $response = $middleware->handle($request, function (array $request) use ($controller): array {
        $GLOBALS['auth'] = $request['user'] ?? [];

        ob_start();
        handleLeaveRoutes($controller, $request);
        $body = ob_get_clean();

        return [
            'status' => http_response_code(),
            'body' => $body,
        ];
    });

    http_response_code($response['status']);
    header('Content-Type: application/json');
    echo is_array($response['body'])
        ? json_encode($response['body'], JSON_UNESCAPED_SLASHES)
        : $response['body'];
} catch (Throwable $exception) {
    jsonResponse([
        'message' => 'Internal Server Error',
        'error' => $exception->getMessage(),
    ], 500);
}
