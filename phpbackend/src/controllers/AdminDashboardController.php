<?php

declare(strict_types=1);

class AdminDashboardController
{
    private AdminDashboardModel $model;

    public function __construct(AdminDashboardModel $model)
    {
        $this->model = $model;
    }

    // =========================================================
    // STATS CARD
    // =========================================================
    public function stats(): array
    {
        try {
            $data = $this->model->fetchStats();

            return [
                'status' => 200,
                'body' => [
                    'currentlyOnsite' => $data['currentlyOnsite'] ?? 0,
                    'totalClockedInToday' => $data['totalClockedInToday'] ?? 0,
                    'pendingSync' => 0,
                    'totalEventsToday' => $data['totalEventsToday'] ?? 0,
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 500,
                'body' => [
                    'success' => false,
                    'error' => $e->getMessage()
                ],
            ];
        }
    }

    // =========================================================
    // ONSITE STAFF
    // =========================================================
    public function onsite(): array
    {
        try {
            $records = $this->model->fetchCurrentlyOnsite();

            return [
                'status' => 200,
                'body' => [
                    'data' => array_map(function ($r) {
                        return [
                            'name' => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                            'role' => $r['role'] ?? null,
                            'sign_in_time' => $r['sign_in_time'] ?? null,
                        ];
                    }, $records),
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 500,
                'body' => [
                    'success' => false,
                    'error' => 'Failed to fetch onsite staff'
                ],
            ];
        }
    }

    // =========================================================
    // RECENT ACTIVITY (LAST 10)
    // =========================================================
    public function recentActivity(): array
    {
        try {
            $records = $this->model->fetchRecentActivity(10);

            return [
                'status' => 200,
                'body' => [
                    'data' => array_map(function ($r) {
                        return [
                            'name' => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                            'action' => $r['event_type'] ?? null,
                            'timestamp' => $r['event_time'] ?? null,
                        ];
                    }, $records),
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 500,
                'body' => [
                    'success' => false,
                    'error' => 'Failed to fetch recent activity'
                ],
            ];
        }
    }
}