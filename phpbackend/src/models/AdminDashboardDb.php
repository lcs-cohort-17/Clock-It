<?php

declare(strict_types=1);

class AdminDashboardModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // =========================================================
    // TODAY RANGE
    // =========================================================
    private function getTodayRange(): array
    {
        return [
            'start' => date('Y-m-d 00:00:00'),
            'end'   => date('Y-m-d 23:59:59'),
        ];
    }

    // =========================================================
    // STATS CARD (OPTIMIZED)
    // =========================================================
    public function fetchStats(): array
    {
        $today = $this->getTodayRange();

        // currently onsite (latest state per user)
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT user_id)
            FROM attendance_logs
            WHERE event_time BETWEEN :start AND :end
            GROUP BY user_id
            HAVING SUM(event_type = 'in') > SUM(event_type = 'out')
        ");
        $stmt->execute($today);
        $currentlyOnsite = count($stmt->fetchAll(PDO::FETCH_COLUMN));

        // total clock-ins today
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM attendance_logs
            WHERE event_type = 'in'
            AND event_time BETWEEN :start AND :end
        ");
        $stmt->execute($today);
        $totalClockedInToday = (int)$stmt->fetchColumn();

        // total events today
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM attendance_logs
            WHERE event_time BETWEEN :start AND :end
        ");
        $stmt->execute($today);
        $totalEventsToday = (int)$stmt->fetchColumn();

        return [
            'currentlyOnsite' => $currentlyOnsite,
            'totalClockedInToday' => $totalClockedInToday,
            'pendingSync' => 0,
            'totalEventsToday' => $totalEventsToday,
        ];
    }

    // =========================================================
    // RECENT ACTIVITY (FAST INDEX FRIENDLY)
    // =========================================================
    public function fetchRecentActivity(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT
                al.user_id,
                al.event_type,
                al.event_time,
                u.first_name,
                u.last_name,
                u.role
            FROM attendance_logs al
            LEFT JOIN users u ON u.user_id = al.user_id
            ORDER BY al.event_time DESC
            LIMIT $limit
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================================================
    // ONSITE STAFF (OPTIMIZED VERSION)
    // =========================================================
    public function fetchCurrentlyOnsite(): array
    {
        $today = $this->getTodayRange();

        // Fast state-based computation instead of NOT EXISTS
        $stmt = $this->db->prepare("
            SELECT 
                t.user_id,
                t.sign_in_time,
                u.first_name,
                u.last_name,
                u.role
            FROM (
                SELECT 
                    user_id,
                    MAX(event_time) AS sign_in_time
                FROM attendance_logs
                WHERE event_time BETWEEN :start AND :end
                GROUP BY user_id
                HAVING SUM(event_type = 'in') > SUM(event_type = 'out')
            ) t
            LEFT JOIN users u ON u.user_id = t.user_id
            ORDER BY t.sign_in_time DESC
        ");

        $stmt->execute($today);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}