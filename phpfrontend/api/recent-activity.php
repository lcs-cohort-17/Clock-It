<?php
require_once 'db.php';
checkAdminRole();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $stmt = $pdo->prepare("
        SELECT a.id, u.name, a.type as action, a.timestamp 
        FROM attendance a 
        JOIN users u ON a.user_id = u.id 
        ORDER BY a.timestamp DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $events = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $events ?: [],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}