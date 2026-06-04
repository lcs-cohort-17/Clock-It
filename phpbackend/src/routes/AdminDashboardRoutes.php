<?php

declare(strict_types=1);

use Middleware\AuthMiddleware;
use App\Models\AdminDashboardModel;
use App\Controllers\AdminDashboardController;

class AdminDashboardRouter
{
    private AdminDashboardController $controller;
    private AuthMiddleware $auth;

    public function __construct(
        AdminDashboardController $controller,
        AuthMiddleware $auth
    ) {
        $this->controller = $controller;
        $this->auth = $auth;
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $path = rtrim($path, '/') ?: '/';

        $request = [
            'headers' => getallheaders()
        ];

        // =====================================================
        // AUTH CHECK (ADMIN ONLY)
        // =====================================================
        $guard = $this->auth->requireAdmin($request);
        if ($guard !== null) {
            http_response_code($guard['status']);
            header('Content-Type: application/json');
            echo json_encode($guard['body']);
            exit;
        }

        // =====================================================
        // ROUTES
        // =====================================================

        if ($method === 'GET' && $path === '/api/admin/dashboard/stats') {
            $result = $this->controller->stats();
            $this->respond($result);
        }

        if ($method === 'GET' && $path === '/api/admin/dashboard/recent-activity') {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = max(1, (int)($_GET['limit'] ?? 10));

            $result = $this->controller->recentActivity($page, $limit);
            $this->respond($result);
        }

        if ($method === 'GET' && $path === '/api/admin/dashboard/onsite') {
            $result = $this->controller->onsite();
            $this->respond($result);
        }

        // =====================================================
        // NOT FOUND
        // =====================================================
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Route not found'
        ]);
        exit;
    }

    private function respond(array $result): void
    {
        http_response_code($result['status']);
        header('Content-Type: application/json');
        echo json_encode($result['body']);
        exit;
    }
}