<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

$staff_id = $_GET['staff_id'] ?? null;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

$sql = "SELECT * FROM audit_logs WHERE 1=1";
$params = [];
$types = "";

if ($staff_id) {
    $sql .= " AND record_id = ?";
    $params[] = $staff_id;
    $types .= "s";
}
if ($start_date) {
    $sql .= " AND changed_at >= ?";
    $params[] = $start_date . ' 00:00:00';
    $types .= "s";
}
if ($end_date) {
    $sql .= " AND changed_at <= ?";
    $params[] = $end_date . ' 23:59:59';
    $types .= "s";
}

$sql .= " ORDER BY changed_at DESC LIMIT 200";

$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$logs = $result->fetch_all(MYSQLI_ASSOC);

// Decode JSON fields so frontend doesn't have to
foreach ($logs as &$log) {
    $log['old_values'] = json_decode($log['old_values'], true);
    $log['new_values'] = json_decode($log['new_values'], true);
}

echo json_encode(['success' => true, 'data' => $logs, 'count' => count($logs)]);