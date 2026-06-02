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
            "SELECT COUNT(*) AS count FROM sessions 
            WHERE clock_out_time IS NULL
             AND clock_in_time >= '{$today['start']}'
             AND clock_in_time < '{$today['end']}'",
            $errors
        );

        $clockIns = $this->tryFetchCount(
            "SELECT COUNT(*) AS count
             FROM attendance_logs
             WHERE event_type = 'in'
               AND event_time >= '{$today['start']}'
               AND event_time < '{$today['end']}'",
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
            'currentlyOnsite' =>  $active,
            'totalClockedInToday' =>  $clockIns,
            'pendingSync' =>  0,
            'totalEventsToday' =>  $events,
        ];
    }

    public function fetchRecentActivity(int $page, int $limit): array
{
    $page = max(1, $page);
    $limit = max(1, $limit);
    $offset = ($page - 1) * $limit;

        $sql = "
            SELECT
            al.user_id,
            al.event_time,
            al.event_type,
            u.first_name,
            u.last_name,
            u.role
        FROM attendance_logs al
        LEFT JOIN users u ON u.id = al.user_id
        ORDER BY al.event_time DESC
        LIMIT {$limit} OFFSET {$offset}";

        return $this->fetchRows($sql);
}



public function fetchCurrentlyOnsite(): array
    {
        $today = $this->getTodayRange();

        // Fixed: Only get staff who have an active session today
        $sql = "SELECT
                    s.user_id,
                    s.clock_in_time AS event_time,
                    u.first_name,
                    u.last_name,
                    u.role
                FROM sessions s
                LEFT JOIN users u ON u.id = s.user_id
                WHERE s.clock_out_time IS NULL
                  AND s.clock_in_time >= '{$today['start']}'
                ORDER BY s.clock_in_time DESC";

        return $this->fetchRows($sql);
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
            $role = $row['role'] ?? null;
            unset($row['first_name'], $row['last_name'], $row['role']);

            $row['users'] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role' => $role,
            ];

            return $row;
        }, $rows ?: []);
    }
}
