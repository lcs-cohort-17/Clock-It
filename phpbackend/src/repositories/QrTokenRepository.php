<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/QrToken.php';

class QrTokenRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function findByToken($token) {
        $stmt = $this->pdo->prepare("SELECT * FROM qr_tokens WHERE token = ? LIMIT 1");
        $stmt->execute([$token]);
        $data = $stmt->fetch();
        return $data ? new QrToken($data) : null;
    }

    public function markAsUsed($id) {
        $stmt = $this->pdo->prepare("UPDATE qr_tokens SET used_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function beginTransaction() {
        $this->pdo->beginTransaction();
    }

    public function commit() {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    public function rollBack() {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }
}