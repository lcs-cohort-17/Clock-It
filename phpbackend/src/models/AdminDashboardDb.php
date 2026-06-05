<?php

declare(strict_types=1);

class AdminDashboardModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function getTodayRange(): array
    {
        $start = strtotime(gmdate('Y-m-d 00:00:00'));
        $end = $start + 86400;

        return [
            'start' => gmdate('Y-m-d H:i:s', $start),
            'end' => gmdate('Y-m-d H:i:s', $end),
        ];
    }

    public function fetchStats(): array
    {
        $errors = [];
        $today = $this->getTodayRange();

        $active = $this->tryFetchCount(
            'SELECT COUNT(*) AS count FROM sessions WHERE clock_out_time IS NULL',
            $errors
        );

        $clockIns = $this->tryFetchCount(
            "SELECT COUNT(*) AS count
             FROM attendance_logs
             WHERE event_type IN ('in', 'Clock In', 'clock-in')
               AND event_time >= '{$today['start']}'
               AND event_time < '{$today['end']}'",
            $errors
        );

        $pending = $this->tryFetchCount(
            "SELECT COUNT(*) AS count FROM attendance_logs WHERE sync_status = 'pending'",
            $errors
        );

        $events = $this->tryFetchCount(
            "SELECT COUNT(*) AS count
             FROM attendance_logs
             WHERE event_time >= '{$today['start']}'
               AND event_time < '{$today['end']}'",
            $errors
        );

        if (!empty($errors)) {
            throw new RuntimeException(implode(' | ', $errors));
        }

        return [
            'currentlyOnsite' => ['value' => $active, 'icon' => 'people'],
            'totalClockedInToday' => ['value' => $clockIns, 'icon' => 'login'],
            'pendingSync' => ['value' => $pending, 'icon' => 'sync_problem'],
            'totalEventsToday' => ['value' => $events, 'icon' => 'event'],
        ];
    }

    public function fetchRecentActivity(int $page, int $limit): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);
        $offset = ($page - 1) * $limit;

        return $this->fetchRows(
            "SELECT
                al.profile_id,
                al.event_time,
                al.event_type,
                al.sync_status,
                al.device_info,
                p.first_name,
                p.last_name
             FROM attendance_logs al
             LEFT JOIN users p ON p.user_id = al.profile_id
             ORDER BY al.event_time DESC
             LIMIT {$limit} OFFSET {$offset}"
        );
    }

    public function fetchCurrentlyOnsite(): array
    {
        return $this->fetchRows(
            "SELECT
                al.profile_id,
                al.event_time,
                al.location,
                al.event_type,
                p.first_name,
                p.last_name
             FROM attendance_logs al
             LEFT JOIN users p ON p.user_id = al.profile_id
             ORDER BY al.event_time DESC"
        );
    }

    private function tryFetchCount(string $sql, array &$errors): int
    {
        try {
            return $this->fetchCount($sql);
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
            return 0;
        }
    }

    private function fetchCount(string $sql): int
    {
        $stmt = $this->db->query($sql);

        if ($stmt === false) {
            throw new RuntimeException('Database query failed');
        }

        return (int) $stmt->fetchColumn();
    }

    private function fetchRows(string $sql): array
    {
        $stmt = $this->db->query($sql);

        if ($stmt === false) {
            throw new RuntimeException('Database query failed');
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function (array $row): array {
            $firstName = $row['first_name'] ?? null;
            $lastName = $row['last_name'] ?? null;
            unset($row['first_name'], $row['last_name']);

            $row['profiles'] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
            ];

            return $row;
        }, $rows ?: []);
    }
}
