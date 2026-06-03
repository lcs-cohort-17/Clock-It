<?php
$projectRoot = dirname(dirname(dirname(dirname(__DIR__))));
require $projectRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use App\Config\Database;
use App\Middleware\AuthMiddleware;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $auth = new AuthMiddleware();
    $user = $auth->validateToken();

    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        exit;
    }

    $database = new Database();
    $conn = $database->connect();

    // Query matches your actual audit_logs table
    $stmt = $conn->prepare(
        "SELECT al.id, al.admin_user_id, al.action, al.table_name, al.record_id,
                al.old_value, al.new_value, al.created_at,
                u.name as admin_name, u.email as admin_email
         FROM audit_logs al
         LEFT JOIN users u ON al.admin_user_id = u.id
         WHERE al.table_name = 'attendance_logs'
         ORDER BY al.created_at DESC 
         LIMIT 50"
    );
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $logs]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}