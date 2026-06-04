<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Attendance.php';

class AttendanceRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getLastAttendanceToday($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM attendance 
            WHERE user_id = ? 
            AND DATE(timestamp) = CURDATE() 
            ORDER BY timestamp DESC LIMIT 1
        ");
        $stmt->execute([$userId]);
        $data = $stmt->fetch();
        return $data ? new Attendance($data) : null;
    }

    public function create(Attendance $attendance) {
        $stmt = $this->pdo->prepare("
            INSERT INTO attendance 
            (user_id, type, device, location, qr_token_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $attendance->user_id,
            $attendance->type,
            $attendance->device,
            $attendance->location,
            $attendance->qr_token_id
        ]);
        $attendance->id = $this->pdo->lastInsertId();
        return $attendance;
    }
}