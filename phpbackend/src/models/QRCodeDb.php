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

    public function createToken(string $token, string $type, string $userId): bool
    {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+60 seconds'));
        $stmt = $this->db->prepare("INSERT INTO qr_codes (token, type, expires_at, created_by) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$token, $type, $expiresAt, $userId]);
    }

    public function validateAndUseToken(string $token): ?array
    {
        // 1. Find the token and check if valid
        $stmt = $this->db->prepare("SELECT * FROM qr_codes WHERE token = ? AND used_at IS NULL AND expires_at > NOW()");
        $stmt->execute([$token]);
        $qr = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$qr) return null;

        // 2. Mark as used
        $update = $this->db->prepare("UPDATE qr_codes SET used_at = NOW() WHERE id = ?");
        $update->execute([$qr['id']]);

        return $qr;
    }
}