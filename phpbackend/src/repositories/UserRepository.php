<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../Models/User.php';

class UserRepository {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function findById(int $id): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? new User($data) : null;
    }
}
