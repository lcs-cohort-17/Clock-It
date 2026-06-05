<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class AttendanceDb
{
    private PDO $db;

public function __construct(?PDO $db = null)
{
    $this->db = $db ?? Database::getConnection();
}

    // same as getTodayRange() in Node.js
    private function getTodayRange(): array
    {
        $start = new \DateTime('today', new \DateTimeZone('UTC'));
        $end = new \DateTime('tomorrow', new \DateTimeZone('UTC'));

        return [
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $end->format('Y-m-d H:i:s'),
        ];
    }

    // same as fetchDashboardStats()
    public function fetchDashboardStats(): array
    {
        $range = $this->getTodayRange();

        // same as supabase.from('sessions').select().is('clock_out_time', null)
        $activeSessions = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM sessions 
            WHERE clock_out_time IS NULL
        ");
        $activeSessions->execute();
        $activeCount = $activeSessions->fetch()['count'];

        // same as supabase.from('attendance_logs').eq('event_type', 'in').gte().lt()
        $clockInsToday = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM attendance_logs 
            WHERE event_type IN ('in', 'Clock In', 'clock-in')
            AND event_time >= :start 
            AND event_time < :end
        ");
        $clockInsToday->execute([':start' => $range['start'], ':end' => $range['end']]);
        $clockInCount = $clockInsToday->fetch()['count'];

        // same as supabase.from('attendance_logs').eq('sync_status', 'pending')
        $pendingSync = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM attendance_logs 
            WHERE sync_status = 'pending'
        ");
        $pendingSync->execute();
        $pendingCount = $pendingSync->fetch()['count'];

        // same as supabase.from('attendance_logs').gte().lt()
        $totalEventsToday = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM attendance_logs 
            WHERE event_time >= :start 
            AND event_time < :end
        ");
        $totalEventsToday->execute([':start' => $range['start'], ':end' => $range['end']]);
        $totalCount = $totalEventsToday->fetch()['count'];

        return [
            'currentlyOnsite'     => ['value' => (int)$activeCount,   'icon' => 'people'],
            'totalClockedInToday' => ['value' => (int)$clockInCount,   'icon' => 'login'],
            'pendingSync'         => ['value' => (int)$pendingCount,   'icon' => 'sync_problem'],
            'totalEventsToday'    => ['value' => (int)$totalCount,     'icon' => 'event'],
        ];
    }

    // same as fetchRecentActivity(page, limit)
    public function fetchRecentActivity(int $page, int $limit): array
    {
        // same as (page - 1) * limit
        $offset = ($page - 1) * $limit;

        // same as supabase join with profiles table
        $stmt = $this->db->prepare("
            SELECT 
                al.profile_id,
                al.event_time,
                al.event_type,
                al.sync_status,
                al.device_info,
                p.first_name,
                p.last_name
            FROM attendance_logs al
            LEFT JOIN users p ON al.profile_id = p.user_id
            ORDER BY al.event_time DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    // same as fetchCurrentlyOnsite()
    public function fetchCurrentlyOnsite(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                al.profile_id,
                al.event_time,
                al.location,
                al.event_type,
                p.first_name,
                p.last_name
            FROM attendance_logs al
            LEFT JOIN users p ON al.profile_id = p.user_id
            ORDER BY al.event_time DESC
        ");

        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }
}