<?php

declare(strict_types=1);

class AdminDashboardRouter
{
    private AdminDashboardController $controller;
    private array $queryParams = [];
    private static array $rateLimitBuckets = [];
    private int $rateLimitMax;
    private int $rateLimitWindowMs;

    public function __construct(
        AdminDashboardController $controller,
        int $rateLimitMax = 1,
        int $rateLimitWindowMs = 1_000
    ) {
        $this->controller = $controller;
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
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $nowMs = (int) floor(microtime(true) * 1_000);
        $bucket = self::$rateLimitBuckets[$ip] ?? ['count' => 0, 'windowStart' => $nowMs];

        if (($nowMs - $bucket['windowStart']) >= $this->rateLimitWindowMs) {
            $bucket = ['count' => 0, 'windowStart' => $nowMs];
        }

        $bucket['count']++;
        self::$rateLimitBuckets[$ip] = $bucket;

        $remaining = max(0, $this->rateLimitMax - $bucket['count']);
        $rateLimitHeaders = [
            'ratelimit-limit' => (string) $this->rateLimitMax,
            'ratelimit-remaining' => (string) $remaining,
        ];

        if ($bucket['count'] > $this->rateLimitMax) {
            return [
                'status' => 429,
                'headers' => array_merge($rateLimitHeaders, ['content-type' => 'application/json']),
                'body' => ['error' => 'Too many requests. Max 1 request per second.'],
            ];
        }

        $result = $this->controller->stats();

        return [
            'status' => $result['status'],
            'headers' => array_merge($rateLimitHeaders, ['content-type' => 'application/json']),
            'body' => $result['body'],
        ];
    }

    private function handleExportSheets(): array
    {
        return [
            'status' => 501,
            'headers' => ['content-type' => 'application/json'],
            'body' => ['error' => 'Not implemented'],
        ];
    }

    private function handleRecentActivity(): array
    {
        $page = max(1, (int) ($this->queryParams['page'] ?? 1));
        $limit = max(1, (int) ($this->queryParams['limit'] ?? 10));

        $result = $this->controller->recentActivity($page, $limit);
        return [
            'status' => $result['status'],
            'headers' => ['content-type' => 'application/json'],
            'body' => $result['body'],
        ];
    }

    private function handleOnsite(): array
    {
        $result = $this->controller->onsite();

        return [
            'status' => $result['status'],
            'headers' => ['content-type' => 'application/json'],
            'body' => $result['body'],
        ];
    }

    private function notFound(): array
    {
        return [
            'status' => 404,
            'headers' => ['content-type' => 'application/json'],
            'body' => ['error' => 'Not found'],
        ];
    }

    public static function send(array $response): void
    {
        http_response_code($response['status']);

        foreach ($response['headers'] as $name => $value) {
            header("{$name}: {$value}");
        }

        echo json_encode($response['body']);
    }
}
