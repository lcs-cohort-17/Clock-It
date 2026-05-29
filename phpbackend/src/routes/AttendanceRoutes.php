<?php

namespace App\Routes;

use App\Controllers\AttendanceController;

class AttendanceRoutes
{
    private AttendanceController $controller;

    // same as const router = express.Router()
    public function __construct(?AttendanceController $controller = null)
    {
        $this->controller = $controller ?? new AttendanceController();
    }

    // same as buildAdminDashboardRouter()
    public function handle(string $uri, string $method): void
    {
        // same as rate limiter — simple request throttle
        $this->rateLimit();

        // same as router.get('/stats', handleGetStats)
        if ($uri === '/api/attendance/stats' && $method === 'GET') {
            $this->controller->handleGetStats();
            return;
        }

        // same as router.get('/recent-activity', getRecentActivityController)
        if ($uri === '/api/attendance/recent-activity' && $method === 'GET') {
            $this->controller->getRecentActivity();
            return;
        }

        // same as router.get('/onsite', getCurrentlyOnsiteController)
        if ($uri === '/api/attendance/onsite' && $method === 'GET') {
            $this->controller->getCurrentlyOnsite();
            return;
        }

        // same as router.use((req, res) => res.status(404).json({ error: 'Not found' }))
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }

    // same as statsRateLimiter — max 1 request per second
    private function rateLimit(): void
    {
        $maxRequests = 1;
        $windowMs    = 1;

        $ip         = $_SERVER['REMOTE_ADDR'];
        $cacheKey   = 'rate_limit_' . md5($ip);
        $cacheFile  = sys_get_temp_dir() . '/' . $cacheKey;

        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);

            if (time() - $data['time'] < $windowMs && $data['count'] >= $maxRequests) {
                http_response_code(429);
                echo json_encode(['error' => 'Too many requests. Max 1 request per second.']);
                exit;
            }

            if (time() - $data['time'] >= $windowMs) {
                $data = ['time' => time(), 'count' => 1];
            } else {
                $data['count']++;
            }
        } else {
            $data = ['time' => time(), 'count' => 1];
        }

        file_put_contents($cacheFile, json_encode($data));
    }
}