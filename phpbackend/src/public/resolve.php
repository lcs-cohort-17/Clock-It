<?php
$projectRoot = dirname(dirname(dirname(__DIR__)));
require $projectRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use App\Config\Database;
use App\Middleware\AuthMiddleware;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');

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

    $input = json_decode(file_get_contents('php://input'), true);
    $alert_id = $input['alert_id'] ?? null;

    if (!$alert_id) {
        http_response_code(400);
        echo json_encode(['error' => 'alert_id required']);
        exit;
    }

    $database = new Database();
    $conn = $database->connect();

    $stmt = $conn->prepare("UPDATE alerts SET is_read = 1 WHERE id = ?");
    $stmt->execute([$alert_id]);

    echo json_encode(['success' => true, 'message' => 'Alert resolved']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}