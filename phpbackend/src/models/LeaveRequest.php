<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class LeaveRequest {
    public static function all() {
        $pdo = Database::connect();
        $stmt = $pdo->query("SELECT * FROM leave_requests ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $pdo = Database::connect();
        $id = $data['id']?? vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
        
        $stmt = $pdo->prepare("INSERT INTO leave_requests (id, staff_id, type, start_date, end_date, reason, status) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([
            $id,
            $data['staff_id'],
            $data['type'],
            $data['start_date'],
            $data['end_date'],
            $data['reason']?? null,
            $data['status']?? 'pending'
        ]);
        return $id;
    }

    public static function update($id, $data) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE leave_requests SET type =?, start_date =?, end_date =?, reason =?, status =?, admin_note =?, updated_at = NOW() WHERE id =?");
        $stmt->execute([
            $data['type'],
            $data['start_date'],
            $data['end_date'],
            $data['reason']?? null,
            $data['status']?? 'pending',
            $data['admin_note']?? null,
            $id
        ]);
    }

    public static function delete($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM leave_requests WHERE id =?");
        $stmt->execute([$id]);
    }
}