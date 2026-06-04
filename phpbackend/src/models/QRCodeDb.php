<?php

namespace App\Models;

use Config\Database;
use PDO;

class QrCodeDb
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create QR token
     */
    public function createToken(string $token, string $type, string $userId): bool
    {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+60 seconds'));

        $stmt = $this->db->prepare("
            INSERT INTO qr_codes (token, type, expires_at, created_by)
            VALUES (?, ?, ?, ?)
        ");

        return $stmt->execute([$token, $type, $expiresAt, $userId]);
    }

    /**
     * Validate + consume token safely (atomic)
     */
    public function validateAndUseToken(string $token): array
    {
        try {
            $this->db->beginTransaction();

            // Lock row so it cannot be double-used
            $stmt = $this->db->prepare("
                SELECT * 
                FROM qr_codes 
                WHERE token = ?
                FOR UPDATE
            ");
            $stmt->execute([$token]);
            $qr = $stmt->fetch(PDO::FETCH_ASSOC);

            // ❌ Not found
            if (!$qr) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'status' => 404,
                    'error' => 'QR code not found'
                ];
            }

            // ❌ Already used
            if (!empty($qr['used_at'])) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'status' => 410,
                    'error' => 'QR code already used'
                ];
            }

            // ❌ Expired
            if (strtotime($qr['expires_at']) < time()) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'status' => 410,
                    'error' => 'QR code expired'
                ];
            }

            // Mark as used
            $update = $this->db->prepare("
                UPDATE qr_codes 
                SET used_at = NOW()
                WHERE id = ?
            ");
            $update->execute([$qr['id']]);

            $this->db->commit();

            return [
                'success' => true,
                'data' => $qr
            ];

        } catch (\Throwable $e) {
            $this->db->rollBack();

            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage()
            ];
        }
    }
}