<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

// GET /api/admin/leave.php - List leave/sick requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $staff_id = $_GET['staff_id'] ?? null;
    $status = $_GET['status'] ?? null; // pending, approved, rejected
    $type = $_GET['type'] ?? null; // leave, sick, annual, personal
    $start_date = $_GET['start_date'] ?? null;
    
    $sql = "SELECT * FROM leave_requests WHERE 1=1";
    $params = [];
    $types = "";
    
    if ($staff_id) {
        $sql .= " AND staff_id = ?";
        $params[] = $staff_id;
        $types .= "s";
    }
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    if ($type) {
        $sql .= " AND type = ?";
        $params[] = $type;
        $types .= "s";
    }
    if ($start_date) {
        $sql .= " AND start_date >= ?";
        $params[] = $start_date;
        $types .= "s";
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT 200";
    
    $stmt = $mysqli->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $requests, 'count' => count($requests)]);
    exit;
}

// POST /api/admin/leave.php - Approve/Reject leave request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'], $data['status'], $data['admin_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields: id, status, admin_id']);
        exit;
    }
    
    $id = $data['id'];
    $status = $data['status']; // approved or rejected
    $admin_id = $data['admin_id'];
    $admin_note = $data['admin_note'] ?? null;
    
    $stmt = $mysqli->prepare("
        UPDATE leave_requests 
        SET status = ?, admin_id = ?, admin_note = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("ssss", $status, $admin_id, $admin_note, $id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => "Leave request {$status}"]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Update failed']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);