<?php
// =============================================
// ERROR HANDLING
// =============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

set_exception_handler(function ($exception) {
    error_log('UNCAUGHT EXCEPTION: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal Server Error',
        'message' => $exception->getMessage()
    ]);
    exit;
});

// =============================================
// DOTENV BOOTSTRAP
// =============================================
require __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

// =============================================
// AUTOLOAD
// =============================================
if (!class_exists('Middleware\AuthMiddleware')) {
    require_once __DIR__ . '/../middleware/AuthMiddleware.php';
}

// =============================================
// HEADERS
// =============================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =============================================
// REQUEST
// =============================================
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/api#', '', $path);

$input = json_decode(file_get_contents("php://input"), true) ?? [];

$request = [
    'headers' => getallheaders(),
    'method' => $method,
    'path' => $path,
    'body' => $input
];

// =============================================
// DB + AUTH
// =============================================
use Config\Database;
use Middleware\AuthMiddleware;

$db = Database::getInstance()->getConnection();
$auth = new AuthMiddleware($_ENV['JWT_SECRET'] ?? 'secret', $db);

// =============================================
// ROUTE: ATTENDANCE CLOCK (NEW TICKET)
// =============================================
if ($method === 'POST' && $path === '/attendance/clock') {

    // 1. AUTH CHECK
    $authCheck = $auth->requireLogin($request);
    if ($authCheck !== null) {
        http_response_code($authCheck['status']);
        echo json_encode($authCheck['body']);
        exit;
    }

    $user = $request['user'];
    $userId = $user['user_id'] ?? null;

    if (!$userId) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid user']);
        exit;
    }

    $qrToken = $input['qr_token'] ?? null;
    $device = $input['device_info'] ?? null;
    $location = $input['location'] ?? null;

    if (!$qrToken) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'QR token required']);
        exit;
    }

    try {

        // 2. VALIDATE QR
        $stmt = $db->prepare("
            SELECT * FROM qr_codes
            WHERE token = ?
              AND used_at IS NULL
              AND expires_at > NOW()
        ");
        $stmt->execute([$qrToken]);
        $qr = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$qr) {
            http_response_code(410);
            echo json_encode(['success' => false, 'error' => 'QR code expired or already used']);
            exit;
        }

        // 3. CHECK USER ACTIVE
        $stmt = $db->prepare("SELECT is_active FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $isActive = $stmt->fetchColumn();

        if (!$isActive) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'User inactive']);
            exit;
        }

        // 4. LAST ATTENDANCE CHECK
        $stmt = $db->prepare("
            SELECT event_type
            FROM attendance_logs
            WHERE user_id = ?
            AND DATE(event_time) = CURDATE()
            ORDER BY event_time DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $last = $stmt->fetchColumn();

        $newType = ($last === 'in') ? 'out' : 'in';

        if ($last === $newType) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Duplicate clock action']);
            exit;
        }

        // 5. TRANSACTION
        $db->beginTransaction();

        // INSERT ATTENDANCE
        $stmt = $db->prepare("
            INSERT INTO attendance_logs
            (user_id, event_type, event_time, check_in_method, sync_status, location, device_info, qr_code_id, created_at)
            VALUES (?, ?, NOW(), 'qr', 1, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $userId,
            $newType,
            $location,
            $device,
            $qr['id']
        ]);

        // MARK QR USED
        $stmt = $db->prepare("
            UPDATE qr_codes
            SET used_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$qr['id']]);

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => "Clock {$newType} successful"
        ]);
        exit;

    } catch (Throwable $e) {
        $db->rollBack();

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

// =============================================
// 404
// =============================================
http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => 'Route not found'
]);
exit;