<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;
use Throwable;

class EmailAlertDb
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function fetchLateArrivals(string $workDate, string $lateAfter): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                u.user_id,
                u.employee_id,
                u.first_name,
                u.last_name,
                u.email,
                MIN(al.event_time) AS clock_in_time
             FROM users u
             INNER JOIN attendance_logs al ON al.user_id = u.user_id
             WHERE u.is_active = 1
               AND al.event_type IN ('in', 'clock-in')
               AND DATE(al.event_time) = :work_date
             GROUP BY u.user_id, u.employee_id, u.first_name, u.last_name, u.email
             HAVING clock_in_time > :late_after
             ORDER BY clock_in_time ASC"
        );

        $stmt->execute([
            ':work_date' => $workDate,
            ':late_after' => "{$workDate} {$lateAfter}:00",
        ]);

        return $stmt->fetchAll() ?: [];
    }

    public function fetchNoShows(string $workDate): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                u.user_id,
                u.employee_id,
                u.first_name,
                u.last_name,
                u.email
             FROM users u
             WHERE u.is_active = 1
               AND u.role IN ('staff', 'manager', 'admin')
               AND NOT EXISTS (
                   SELECT 1
                   FROM attendance_logs al
                   WHERE al.user_id = u.user_id
                     AND al.event_type IN ('in', 'clock-in')
                     AND DATE(al.event_time) = :work_date
               )
             ORDER BY u.first_name ASC, u.last_name ASC"
        );

        $stmt->execute([':work_date' => $workDate]);

        return $stmt->fetchAll() ?: [];
    }

    public function storeDailyAlert(string $title, string $message, array $payload, string $createdAt): bool
    {
        return true;
    }

    public function storeAttendanceAlerts(string $workDate, array $lateArrivals, array $noShows, string $createdAt): int
    {
        $stored = 0;

        foreach ($lateArrivals as $staff) {
            $message = $this->staffName($staff) . " clocked in late on {$workDate}";

            if ($this->storeAlertRow($staff, 'late', $workDate, $message, $createdAt)) {
                $stored++;
            }
        }

        foreach ($noShows as $staff) {
            $message = $this->staffName($staff) . " did not clock in on {$workDate}";

            if ($this->storeAlertRow($staff, 'no-show', $workDate, $message, $createdAt)) {
                $stored++;
            }
        }

        return $stored;
    }

    private function storeAlertRow(array $staff, string $type, string $date, string $message, string $createdAt): bool
    {
        $userId = $staff['user_id'] ?? $staff['profile_id'] ?? $staff['id'] ?? null;
        if ($userId === null) {
            return false;
        }

        if ($this->alertExists($userId, $type, $date)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO alerts (user_id, type, date, message, is_read, created_at)
             VALUES (:user_id, :type, :date, :message, 0, :created_at)"
        );

        return $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':date' => $date,
            ':message' => $message,
            ':created_at' => $createdAt,
        ]);
    }

    private function alertExists(string $userId, string $type, string $date): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS count
             FROM alerts
             WHERE user_id = :user_id
               AND type = :type
               AND date = :date"
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':date' => $date,
        ]);

        $row = $stmt->fetch();

        return (int) ($row['count'] ?? 0) > 0;
    }

    private function staffName(array $staff): string
    {
        $name = trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''));

        return $name !== '' ? $name : ($staff['employee_id'] ?? 'Unknown staff');
    }
}
