<?php
require_once 'db.php';
checkAdminRole();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Finds users whose absolute most recent event is an 'in'
    // This matches the logic in dashboard-stats.php
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.role, a_latest.timestamp as sign_in_time 
        FROM attendance a_latest
        JOIN users u ON a_latest.user_id = u.id
        WHERE a_latest.timestamp = (
            SELECT MAX(timestamp) FROM attendance 
            WHERE user_id = a_latest.user_id
        )
        AND a_latest.type = 'in'
        ORDER BY a_latest.timestamp DESC
    ");
    $stmt->execute();
    $staff = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $staff ?: [],
        'count' => count($staff),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}