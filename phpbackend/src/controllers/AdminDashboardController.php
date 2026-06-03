<?php

declare(strict_types=1);

class AdminDashboardController
{
    private AdminDashboardModel $model;
    private int $cacheTtlMs;
    private ?array $cache = null;
    private int $cacheExpiresAtMs = 0;
    private \Closure $clock;

    public function __construct(
        AdminDashboardModel $model,
        int $cacheTtlMs = 5_000,
        ?callable $clock = null
    ) {
        $this->model = $model;
        $this->cacheTtlMs = $cacheTtlMs;
        $this->clock = $clock !== null
            ? \Closure::fromCallable($clock)
            : static fn (): int => (int) floor(microtime(true) * 1_000);
    }

    public function stats(): array
    {
        $now = ($this->clock)();

        if ($this->cache !== null && $now < $this->cacheExpiresAtMs) {
            return [
                'status' => 200,
                'body' => $this->cache,
            ];
        }

        try {
            $body = $this->model->fetchStats();
            $this->cache = $body;
            $this->cacheExpiresAtMs = $now + $this->cacheTtlMs;

            return [
                'status' => 200,
                'body' => $body,
            ];
        } catch (\Throwable $exception) {
            $this->cache = null;
            $this->cacheExpiresAtMs = 0;

            return [
                'status' => 500,
                'body' => ['error' => $exception->getMessage()],
            ];
        }
    }

    public function recentActivity(int $page = 1, int $limit = 10): array
    {
        try {
            $records = $this->model->fetchRecentActivity($page, $limit);

            return [
                'status' => 200,
                'body' => [
                    'status' => 'success',
                    'data' => array_map([$this, 'formatRecentActivityRecord'], $records),
                ],
            ];
        } catch (\Throwable $exception) {
            return [
                'status' => 500,
                'body' => [
                    'status' => 'error',
                    'message' => 'Failed to fetch recent activity',
                ],
            ];
        }
    }

    public function onsite(): array
    {
        try {
            $records = $this->model->fetchCurrentlyOnsite();

            return [
                'status' => 200,
                'body' => [
                    'status' => 'success',
                    'data' => array_map([$this, 'formatOnsiteRecord'], $records),
                ],
            ];
        } catch (\Throwable $exception) {
            return [
                'status' => 500,
                'body' => [
                    'status' => 'error',
                    'message' => 'Failed to fetch currently onsite staff',
                ],
            ];
        }
    }

    private function formatRecentActivityRecord(array $record): array
    {
        return [
            'profile_id' => $record['profile_id'] ?? null,
            'event_time' => $record['event_time'] ?? null,
            'event_type' => $record['event_type'] ?? null,
            'sync_status' => $record['sync_status'] ?? null,
            'device_info' => $record['device_info'] ?? null,
            'staff' => $this->formatStaff($record),
        ];
    }

    private function formatOnsiteRecord(array $record): array
    {
        return [
            'profile_id' => $record['profile_id'] ?? null,
            'event_time' => $record['event_time'] ?? null,
            'location' => $record['location'] ?? null,
            'staff' => $this->formatStaff($record),
        ];
    }

    private function formatStaff(array $record): string
    {
        $user = $record['users'] ?? [];

        if (is_array($user) && array_is_list($user)) {
            $user = $user[0] ?? [];
        }

        if (!is_array($user)) {
            return '';
        }

        return trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    }
}
