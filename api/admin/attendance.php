<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

// GET /api/admin/attendance.php - List clock events with filters
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $staff_id = $_GET['staff_id'] ?? null;
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    $sync_status = $_GET['sync_status'] ?? null; // 'pending' or 'synced'
    
    $sql = "SELECT 
                id,
                staff_id,
                type,
                timestamp,
                location,
                sync_status,
                updated_by,
                created_at
            FROM attendance_events
            WHERE type IN ('clock_in', 'clock_out')";
    
    $params = [];
    $types = "";
    
    if ($staff_id) {
        $sql .= " AND staff_id = ?";
        $params[] = $staff_id;
        $types .= "s";
    }
    if ($start_date) {
        $sql .= " AND timestamp >= ?";
        $params[] = $start_date . ' 00:00:00';
        $types .= "s";
    }
    if ($end_date) {
        $sql .= " AND timestamp <= ?";
        $params[] = $end_date . ' 23:59:59';
        $types .= "s";
    }
    if ($sync_status) {
        $sql .= " AND sync_status = ?";
        $params[] = $sync_status;
        $types .= "s";
    }
    
    $sql .= " ORDER BY timestamp DESC LIMIT 200";
    
    $stmt = $mysqli->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $events = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $events, 'count' => count($events)]);
    exit;
}

// POST /api/admin/attendance.php - Admin override
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'], $data['timestamp'], $data['admin_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields: id, timestamp, admin_id']);
        exit;
    }
    
    $admin_id = $data['admin_id'];
    $id = $data['id'];
    $timestamp = $data['timestamp'];
    $location = $data['location'] ?? null;
    
    $stmt = $mysqli->prepare("
        UPDATE attendance_events 
        SET timestamp = ?, location = ?, updated_by = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("ssss", $timestamp, $location, $admin_id, $id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Override logged']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Update failed or no rows changed']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
// POST /api/admin/attendance.php - Admin override
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'], $data['timestamp'], $data['admin_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields: id, timestamp, admin_id']);
        exit;
    }
    
    $admin_id = $data['admin_id'];
    $id = $data['id'];
    $timestamp = $data['timestamp'];
    $location = $data['location'] ?? null;
    
    $stmt = $mysqli->prepare("
        UPDATE attendance_events 
        SET timestamp = ?, location = ?, updated_by = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("ssss", $timestamp, $location, $admin_id, $id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Override logged']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Update failed or no rows changed']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
