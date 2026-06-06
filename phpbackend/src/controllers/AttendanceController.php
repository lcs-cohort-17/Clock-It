<?php

namespace App\Controllers;

use App\Models\AttendanceDb;
use App\Services\AttendanceService;

class AttendanceController
{
    private AttendanceDb $model;
    private AttendanceService $service;

    // cache — same as statsCache in Node.js
    private static ?array $cache = null;
    private static int $cacheExpiresAt = 0;
    private static int $cacheTtlMs = 5000;

    public function __construct(?AttendanceDb $model = null, ?AttendanceService $service = null)
    {
        $this->model   = $model ?? new AttendanceDb();
        $this->service = $service ?? new AttendanceService();
    }

    // same as handleGetStats()
    public function handleGetStats(): void
    {
        // same as if (Date.now() < statsCache.expiresAt && statsCache.data)
        $now = (int)(microtime(true) * 1000);
        if (self::$cache !== null && $now < self::$cacheExpiresAt) {
            http_response_code(200);
            echo json_encode(self::$cache);
            return;
        }

        try {
            // same as fetchDashboardStats(supabase)
            $data = $this->model->fetchDashboardStats();

            // same as statsCache.data = responsePayload
            self::$cache = $data;
            self::$cacheExpiresAt = $now + self::$cacheTtlMs;

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data'   => $data,
            ]);

        } catch (\Exception $e) {
            // same as res.status(500).json({ error: String(error) })
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage() ?? 'Unable to fetch dashboard statistics',
            ]);
        }
    }

    // same as getRecentActivityController()
    public function getRecentActivity(): void
    {
        try {
            $page  = isset($_GET['page'])  ? (int)$_GET['page']  : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

            $rawData    = $this->model->fetchRecentActivity($page, $limit);
            $formatted  = $this->service->formatRecentActivity($rawData);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data'   => $formatted,
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to fetch recent activity',
            ]);
        }
    }

    // same as getCurrentlyOnsiteController()
    public function getCurrentlyOnsite(): void
    {
        try {
            $rawData   = $this->model->fetchCurrentlyOnsite();
            $formatted = $this->service->formatOnsite($rawData);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data'   => $formatted,
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to fetch currently onsite staff',
            ]);
        }
    }
}