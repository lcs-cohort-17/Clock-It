<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/phpfrontend/src/bootstrap.php';
require_once dirname(__DIR__, 2) . '/phpbackend/src/config/Database.php';

header('Content-Type: application/json; charset=utf-8');

$db = \Config\Database::getInstance()->getConnection();
$stmt = $db->query("
    SELECT al.id, al.event_time, al.event_type, al.sync_status, al.device_info, al.location, u.first_name, u.last_name, u.role
    FROM attendance_logs al
    JOIN users u ON u.user_id = al.profile_id
    ORDER BY al.event_time DESC
    LIMIT 10
");
$logs = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

$activity = [];
foreach ($logs as $row) {
    $activity[] = [
        'id' => $row['id'],
        'userName' => ($row['first_name'] ?? '') . ($row['last_name'] ? ' ' . $row['last_name'] : ''),
        'type' => $row['event_type'] === 'in' ? 'clock-in' : 'clock-out',
        'timestamp' => date('Y-m-d H:i', strtotime($row['event_time'])),
        'sync' => ucfirst($row['sync_status'] ?? 'synced'),
        'device' => $row['device_info'] ?: 'QR Code Scanner',
        'location' => $row['location'] ?: 'Main Office'
    ];
}

echo json_encode([
    'data' => $activity,
    'meta' => [
        'count' => count($activity),
        'limit' => 10,
        'source' => 'live',
        'generatedAt' => date(DATE_ATOM),
    ],
], JSON_THROW_ON_ERROR);
