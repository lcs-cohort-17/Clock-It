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
                    'success' => true,
                    'data' => [
                        'currentlyOnsite' => (int)($data['currentlyOnsite'] ?? 0),
                        'totalClockedInToday' => (int)($data['totalClockedInToday'] ?? 0),
                        'pendingSync' => 0,
                        'totalEventsToday' => (int)($data['totalEventsToday'] ?? 0),
                    ],
                ],
            ];
        } catch (\Throwable $e) {
            return $this->error($e, 'Failed to fetch stats');
        }
    }

    // =========================================================
    // ONSITE STAFF
    // =========================================================
    public function onsite(): array
    {
        try {
            $records = $this->model->fetchCurrentlyOnsite();

            $mapped = array_map(function ($r) {
                return [
                    'name' => $this->formatName($r),
                    'role' => $r['role'] ?? null,
                    'sign_in_time' => $r['sign_in_time'] ?? null,
                ];
            }, $records ?: []);

            return [
                'status' => 200,
                'body' => [
                    'success' => true,
                    'data' => $mapped,
                ],
            ];
        } catch (\Throwable $e) {
            return $this->error($e, 'Failed to fetch onsite staff');
        }
    }

    // =========================================================
    // RECENT ACTIVITY
    // =========================================================
    public function recentActivity(): array
    {
        try {
            $records = $this->model->fetchRecentActivity(10);

            $mapped = array_map(function ($r) {
                return [
                    'name' => $this->formatName($r),
                    'action' => $r['event_type'] ?? null,
                    'timestamp' => $r['event_time'] ?? null,
                ];
            }, $records ?: []);

            return [
                'status' => 200,
                'body' => [
                    'success' => true,
                    'data' => $mapped,
                ],
            ];
        } catch (\Throwable $e) {
            return $this->error($e, 'Failed to fetch recent activity');
        }
    }

    // =========================================================
    // NAME FORMATTER (SAFE)
    // =========================================================
    private function formatName(array $r): string
    {
        $first = trim($r['first_name'] ?? '');
        $last = trim($r['last_name'] ?? '');

        return trim($first . ' ' . $last) ?: 'Unknown User';
    }

    // =========================================================
    // ERROR HANDLER
    // =========================================================
    private function error(\Throwable $e, string $message): array
    {
        return [
            'status' => 500,
            'body' => [
                'success' => false,
                'error' => $message,
                'debug' => $e->getMessage()
            ],
        ];
    }
}