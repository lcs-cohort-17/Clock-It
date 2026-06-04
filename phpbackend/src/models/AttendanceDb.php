<?php

namespace App\Models;

use PDO;

class AttendanceDb
{
    public PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ----------------------------------------
    // USER STATUS
    // ----------------------------------------
    public function getUserStatus(string $userId): ?int
    {
        $stmt = $this->db->prepare("
            SELECT is_active
            FROM users
            WHERE user_id = ?
        ");

        $stmt->execute([$userId]);
        $result = $stmt->fetchColumn();

        return $result !== false ? (int)$result : null;
    }

    // ----------------------------------------
    // GET QR (UPDATED - STRICT VALIDATION SPLIT)
    // ----------------------------------------
    public function getQrByToken(string $token): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM qr_codes
            WHERE token = ?
              AND used_at IS NULL
              AND expires_at > NOW()
        ");

        $stmt->execute([$token]);
        $qr = $stmt->fetch(PDO::FETCH_ASSOC);

        return $qr ?: null;
    }

    // ----------------------------------------
    // LAST EVENT (TODAY ONLY - FIXED)
    // ----------------------------------------
    public function getLastEvent(string $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT event_type
            FROM attendance_logs
            WHERE user_id = ?
              AND DATE(event_time) = CURDATE()
            ORDER BY event_time DESC
            LIMIT 1
        ");

        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    // ----------------------------------------
    // INSERT ATTENDANCE
    // ----------------------------------------
    public function insertAttendance(array $data): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO attendance_logs (
                user_id,
                event_type,
                event_time,
                check_in_method,
                sync_status,
                location,
                device_info,
                qr_code_id,
                created_at
            )
            VALUES (
                ?, ?, NOW(),
                'qr',
                'synced',
                ?, ?, ?, NOW()
            )
        ");

        $stmt->execute([
            $data['user_id'],
            $data['event_type'],
            $data['location'],
            $data['device_info'],
            $data['qr_code_id']
        ]);
    }

    // ----------------------------------------
    // MARK QR USED
    // ----------------------------------------
    public function markQrUsed(int $qrId): void
    {
        $stmt = $this->db->prepare("
            UPDATE qr_codes
            SET used_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$qrId]);
    }
}