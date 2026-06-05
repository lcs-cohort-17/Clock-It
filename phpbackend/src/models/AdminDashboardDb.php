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
    // STATS CARD
    // =========================================================
    public function fetchStats(): array
    {
        $today = $this->getTodayRange();

        // currently onsite = last action is IN and no OUT after
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT al1.user_id)
            FROM attendance_logs al1
            WHERE al1.event_type = 'in'
            AND al1.event_time BETWEEN :start AND :end
            AND NOT EXISTS (
                SELECT 1
                FROM attendance_logs al2
                WHERE al2.user_id = al1.user_id
                AND al2.event_type = 'out'
                AND al2.event_time > al1.event_time
                AND al2.event_time BETWEEN :start AND :end
            )
        ");
        $stmt->execute($today);
        $currentlyOnsite = (int)$stmt->fetchColumn();

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
    // RECENT ACTIVITY (LAST 10)
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
            LEFT JOIN users u ON u.id = al.user_id
            ORDER BY al.event_time DESC
            LIMIT :limit
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================================================
    // ONSITE STAFF LIST
    // =========================================================
    public function fetchCurrentlyOnsite(): array
    {
        $today = $this->getTodayRange();

        $stmt = $this->db->prepare("
            SELECT
                al1.user_id,
                al1.event_time AS sign_in_time,
                u.first_name,
                u.last_name,
                u.role
            FROM attendance_logs al1
            JOIN users u ON u.id = al1.user_id
            WHERE al1.event_type = 'in'
            AND al1.event_time BETWEEN :start AND :end
            AND NOT EXISTS (
                SELECT 1
                FROM attendance_logs al2
                WHERE al2.user_id = al1.user_id
                AND al2.event_type = 'out'
                AND al2.event_time > al1.event_time
                AND al2.event_time BETWEEN :start AND :end
            )
            ORDER BY al1.event_time DESC
        ");

        $stmt->execute($today);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}