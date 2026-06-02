<?php

declare(strict_types=1);

namespace App\Router;

use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminDashboardRouter
{
    private AdminDashboardController $controller;
    private AuthMiddleware $auth; // Added middleware
    private array $queryParams = [];
    private static array $rateLimitBuckets = [];
    private int $rateLimitMax;
    private int $rateLimitWindowMs;

    public function __construct(
        AdminDashboardController $controller,
        AuthMiddleware $auth, // Inject middleware here
        int $rateLimitMax = 1,
        int $rateLimitWindowMs = 1_000
    ) {
        $this->controller = $controller;
        $this->auth = $auth;
        $this->rateLimitMax = $rateLimitMax;
        $this->rateLimitWindowMs = $rateLimitWindowMs;
    }

    public function dispatch(?string $method = null, ?string $path = null): array
    {
        $method = strtoupper($method ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $requestTarget = $path ?? ($_SERVER['REQUEST_URI'] ?? '/');
        $queryString = parse_url($requestTarget, PHP_URL_QUERY);
        $path = parse_url($requestTarget, PHP_URL_PATH) ?: '/';

        $this->queryParams = $_GET;
        if ($queryString !== null) {
            parse_str($queryString, $this->queryParams);
        }

        $path = rtrim($path, '/') ?: '/';

        return match (true) {
            $method === 'GET' && $path === '/api/admin/dashboard/stats' => $this->handleStats(),
            $method === 'POST' && $path === '/api/admin/dashboard/export/sheets' => $this->handleExportSheets(),
            $method === 'GET' && $path === '/api/admin/dashboard/recent-activity' => $this->handleRecentActivity(),
            $method === 'GET' && $path === '/api/admin/dashboard/onsite' => $this->handleOnsite(),
            default => $this->notFound(),
        };
    }

    private function handleStats(): array
    {
        // 1. Prepare Request with Headers
        $request = ['headers' => getallheaders()];
        
        // 2. Protect Route
        $guard = $this->auth->requireAdmin($request);
        if ($guard !== null) return $guard;

        // 3. Rate Limiting logic...
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $nowMs = (int) floor(microtime(true) * 1_000);
        $bucket = self::$rateLimitBuckets[$ip] ?? ['count' => 0, 'windowStart' => $nowMs];

        if (($nowMs - $bucket['windowStart']) >= $this->rateLimitWindowMs) {
            $bucket = ['count' => 0, 'windowStart' => $nowMs];
        }

        $bucket['count']++;
        self::$rateLimitBuckets[$ip] = $bucket;

        if ($bucket['count'] > $this->rateLimitMax) {
            return ['status' => 429, 'headers' => ['content-type' => 'application/json'], 'body' => ['error' => 'Too many requests']];
        }

        $result = $this->controller->stats();
        return ['status' => $result['status'], 'headers' => ['content-type' => 'application/json'], 'body' => $result['body']];
    }

    private function handleExportSheets(): array
    {
        $request = ['headers' => getallheaders()];
        $guard = $this->auth->requireAdmin($request);
        if ($guard !== null) return $guard;

        return ['status' => 501, 'headers' => ['content-type' => 'application/json'], 'body' => ['error' => 'Not implemented']];
    }

    private function handleRecentActivity(): array
    {
        $request = ['headers' => getallheaders()];
        $guard = $this->auth->requireAdmin($request);
        if ($guard !== null) return $guard;

        $page = max(1, (int) ($this->queryParams['page'] ?? 1));
        $limit = max(1, (int) ($this->queryParams['limit'] ?? 10));

        $result = $this->controller->recentActivity($page, $limit);
        return ['status' => $result['status'], 'headers' => ['content-type' => 'application/json'], 'body' => $result['body']];
    }

    private function handleOnsite(): array
    {
        $request = ['headers' => getallheaders()];
        $guard = $this->auth->requireAdmin($request);
        if ($guard !== null) return $guard;

        $result = $this->controller->onsite();
        return ['status' => $result['status'], 'headers' => ['content-type' => 'application/json'], 'body' => $result['body']];
    }

    private function notFound(): array
    {
        return ['status' => 404, 'headers' => ['content-type' => 'application/json'], 'body' => ['error' => 'Not found']];
    }

    public static function send(array $response): void
    {
        http_response_code($response['status']);
        foreach (($response['headers'] ?? []) as $name => $value) {
            header("{$name}: {$value}");
        }
        echo json_encode($response['body']);
    }
}

if (isset($group)) {
    require_once __DIR__ . '/../config/Database.php';
    require_once __DIR__ . '/../models/AdminDashboardDb.php';
    require_once __DIR__ . '/../controllers/AdminDashboardController.php';

    $db = \Database::getInstance()->getConnection();
    $model = new \AdminDashboardModel($db);
    $controller = new \AdminDashboardController($model);
    $auth = new AuthMiddleware($_ENV['JWT_SECRET'] ?? 'test_secret_key_that_is_long_enough');

    $checkAdmin = function (ServerRequestInterface $request) use ($auth) {
        $authRequest = [
            'headers' => [
                'authorization' => $request->getHeaderLine('Authorization'),
            ],
        ];

        return $auth->requireAdmin($authRequest);
    };

    $group->get('/stats', function (ServerRequestInterface $request, ResponseInterface $response) use ($controller, $checkAdmin) {
        $guard = $checkAdmin($request);
        if ($guard !== null) {
            $response->getBody()->write(json_encode($guard['body']));
            return $response->withStatus($guard['status'])->withHeader('Content-Type', 'application/json');
        }

        $result = $controller->stats();
        $response->getBody()->write(json_encode($result['body']));
        return $response->withStatus($result['status'])->withHeader('Content-Type', 'application/json');
    });

    $group->get('/recent-activity', function (ServerRequestInterface $request, ResponseInterface $response) use ($controller, $checkAdmin) {
        $guard = $checkAdmin($request);
        if ($guard !== null) {
            $response->getBody()->write(json_encode($guard['body']));
            return $response->withStatus($guard['status'])->withHeader('Content-Type', 'application/json');
        }

        parse_str($request->getUri()->getQuery(), $queryParams);
        $page = max(1, (int) ($queryParams['page'] ?? 1));
        $limit = max(1, (int) ($queryParams['limit'] ?? 10));

        $result = $controller->recentActivity($page, $limit);
        $response->getBody()->write(json_encode($result['body']));
        return $response->withStatus($result['status'])->withHeader('Content-Type', 'application/json');
    });

    $group->get('/onsite', function (ServerRequestInterface $request, ResponseInterface $response) use ($controller, $checkAdmin) {
        $guard = $checkAdmin($request);
        if ($guard !== null) {
            $response->getBody()->write(json_encode($guard['body']));
            return $response->withStatus($guard['status'])->withHeader('Content-Type', 'application/json');
        }

        $result = $controller->onsite();
        $response->getBody()->write(json_encode($result['body']));
        return $response->withStatus($result['status'])->withHeader('Content-Type', 'application/json');
    });
}
