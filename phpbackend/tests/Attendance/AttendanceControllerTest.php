<?php

namespace App\Controllers;

use App\Models\AttendanceDb;
use App\Services\AttendanceService;

class AttendanceController
{
    private AttendanceDb $model;
    private AttendanceService $service;

    public function __construct(
        ?AttendanceDb $model = null,
        ?AttendanceService $service = null
    ) {
        $this->model = $model ?? new AttendanceDb();
        $this->service = $service ?? new AttendanceService();
    }

    public function getRecentActivity(): void
    {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

            $rawData = $this->model->fetchRecentActivity($page, $limit);

            $normalizedData = array_map(function ($item) {
                if (isset($item['profiles']) && is_array($item['profiles'])) {
                    $item['profiles'] = $item['profiles'][0];
                }

                return $item;
            }, $rawData);

            $formatted = $this->service->formatRecentActivity($normalizedData);

            http_response_code(200);

            echo json_encode([
                'status' => 'success',
                'data' => $formatted,
            ]);
        } catch (\Exception $e) {
            http_response_code(500);

            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch recent activity',
            ]);
        }
    }

    public function getCurrentlyOnsite(): void
    {
        try {
            $rawData = $this->model->fetchCurrentlyOnsite();

            $normalizedData = array_map(function ($item) {
                if (isset($item['profiles']) && is_array($item['profiles'])) {
                    $item['profiles'] = $item['profiles'][0];
                }

                return $item;
            }, $rawData);

            $formatted = $this->service->formatOnsite($normalizedData);

            http_response_code(200);

            echo json_encode([
                'status' => 'success',
                'data' => $formatted,
            ]);
        } catch (\Exception $e) {
            http_response_code(500);

            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch currently onsite staff',
            ]);
        }
    }
}