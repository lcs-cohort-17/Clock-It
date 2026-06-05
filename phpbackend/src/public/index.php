<?php
// =============================================
// ERROR HANDLING
// =============================================
error_reporting(E_ALL);
ini_set('display_errors', 1); // Set to 1 for debugging

// Global exception handler
set_exception_handler(function ($exception) {
    error_log('UNCAUGHT EXCEPTION: ' . $exception->getMessage());
    error_log('Stack: ' . $exception->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal Server Error',
        'message' => $exception->getMessage()
    ]);
    exit;
});

// =============================================
// SETUP
// =============================================
require __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Models\ProfileDb;
use Controllers\ProfileController;
use Middleware\AuthMiddleware;
use Config\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

// Manual autoload fix for middleware
if (!class_exists('Middleware\AuthMiddleware')) {
    require_once __DIR__ . '/../middleware/AuthMiddleware.php';
}

// =============================================
// CORS HEADERS
// =============================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =============================================
// ROUTING INITIALISATION
// =============================================
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/api#', '', $path);

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$request = [
    'headers' => getallheaders(),
    'method' => $method,
    'path' => $path,
    'query' => $_GET,
    'body' => $input
];

// JWT secret & database
$jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this';
$db = Database::getInstance()->getConnection();
$authMiddleware = new AuthMiddleware($jwtSecret, $db);

$model = new ProfileDb();
$controller = new ProfileController($model);

// =============================================
// HELPER FUNCTIONS (from backend branch, adapted)
// =============================================
function db(): PDO {
    return Database::getInstance()->getConnection();
}

function json_response(array $body, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function request_body(): array {
    $raw = file_get_contents('php://input') ?: '';
    $json = $raw !== '' ? json_decode($raw, true) : null;
    return is_array($json) ? $json : ($_POST ?: []);
}

function split_full_name(string $name): array {
    $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $first = $parts[0] ?? '';
    $last = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    return [$first, $last];
}

function user_public(array $row): array {
    $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    if ($name === '') {
        $name = $row['email'] ?? 'Unknown User';
    }
    return [
        'id'          => $row['user_id'],
        'user_id'     => $row['user_id'],
        'name'        => $name,
        'first_name'  => $row['first_name'] ?? '',
        'last_name'   => $row['last_name'] ?? '',
        'email'       => $row['email'] ?? '',
        'employeeId'  => $row['employee_id'] ?? '',
        'employee_id' => $row['employee_id'] ?? '',
        'role'        => ucfirst((string) ($row['role'] ?? 'staff')),
        'role_key'    => strtolower((string) ($row['role'] ?? 'staff')),
        'status'      => ((int) ($row['is_active'] ?? 0)) === 1 ? 'Active' : 'Inactive',
        'is_active'   => (int) ($row['is_active'] ?? 0),
        'img'         => $row['img'] ?? null,
    ];
}

function find_user_identifier(string $identifier): ?array {
    $stmt = db()->prepare('SELECT * FROM users WHERE user_id = :id OR employee_id = :id LIMIT 1');
    $stmt->execute([':id' => $identifier]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function attendance_public(array $row): array {
    $staffName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: 'Unknown Staff';
    return [
        'id'          => $row['id'],
        'user_id'     => $row['user_id'] ?? null,
        'employee_id' => $row['employee_id'] ?? null,
        'staff'       => $staffName,
        'staff_name'  => $staffName,
        'type'        => ucfirst($row['event_type'] ?? ''),
        'event_type'  => $row['event_type'] ?? '',
        'timestamp'   => $row['event_time'] ?? '',
        'event_time'  => $row['event_time'] ?? '',
        'device'      => $row['device_info'] ?? '',
        'device_info' => $row['device_info'] ?? '',
        'location'    => $row['location'] ?? '',
        'sync'        => $row['sync_status'] ?? 'pending',
        'sync_status' => $row['sync_status'] ?? 'pending',
        'created_at'  => $row['created_at'] ?? null,
    ];
}

function fetch_attendance(array $filters = []): array {
    $where = [];
    $params = [];
    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
        $where[] = 'DATE(al.event_time) BETWEEN :start_date AND :end_date';
        $params[':start_date'] = $filters['start_date'];
        $params[':end_date'] = $filters['end_date'];
    }
    if (!empty($filters['q'])) {
        $where[] = '(u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q OR u.employee_id LIKE :q OR al.location LIKE :q)';
        $params[':q'] = '%' . $filters['q'] . '%';
    }
    $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $sql = "SELECT al.*, u.first_name, u.last_name, u.employee_id, u.email
            FROM attendance_logs al
            LEFT JOIN users u ON u.user_id = al.user_id
            {$sqlWhere}
            ORDER BY al.event_time DESC";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return array_map('attendance_public', $stmt->fetchAll() ?: []);
}

// =============================================
// YOUR EXISTING PROFILE ROUTES (KEPT EXACTLY)
// =============================================

// POST - Forgot password (public)
if ($method === 'POST' && $path === '/forgot-password') {
    $controller->forgotPassword($input);
    exit;
}

// POST - Reset password (public)
if ($method === 'POST' && $path === '/reset-password') {
    $controller->resetPasswordWithToken($input);
    exit;
}

// PATCH - Admin reset password (admin only)
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)/reset-password$#', $path, $matches)) {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->adminResetPassword($matches[1]);
    exit;
}

// TEST ROUTE
if ($method === 'GET' && $path === '/test') {
    echo json_encode(['message' => 'Test route works!']);
    exit;
}

// POST - Login
if ($method === 'POST' && $path === '/login') {
    $controller->loginProfile($input);
    exit;
}

// GET - Get current user's own profile
if ($method === 'GET' && $path === '/user/profile') {
    $authResult = $authMiddleware->requireLogin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->getCurrentUserProfile($request);
    exit;
}

// GET all users (admin only)
if ($method === 'GET' && $path === '/admin/users') {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->adminGettingAllUsers();
    exit;
}

// PATCH - Soft delete user
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)/deactivate$#', $path, $matches)) {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->softDeleteUser($matches[1]);
    exit;
}

// PATCH - Activate user
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)/activate$#', $path, $matches)) {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->activateUser($matches[1]);
    exit;
}

// GET user by employee_id (admin only)
if ($method === 'GET' && preg_match('#^/admin/users/([^/]+)$#', $path, $matches)) {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->getProfileById($matches[1]);
    exit;
}

// PATCH - Update own profile (staff/admin self-update)
if ($method === 'PATCH' && $path === '/user/profile') {
    $authResult = $authMiddleware->requireLogin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $employee_id = $request['user']['employee_id'] ?? null;
    if (!$employee_id) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User not authenticated']);
        exit;
    }
    $allowedFields = ['first_name', 'last_name', 'email'];
    $updates = array_intersect_key($input, array_flip($allowedFields));
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No valid fields to update']);
        exit;
    }
    $controller->adminUpdatingUser($employee_id, $updates);
    exit;
}

// PATCH - Update user (admin only)
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)$#', $path, $matches)) {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->adminUpdatingUser($matches[1], $input);
    exit;
}

// POST - Create user (admin only)
if ($method === 'POST' && $path === '/admin/users') {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->adminCreatingUser($input);
    exit;
}

// PATCH - Reset password (admin) – kept for backward compatibility
if ($method === 'PATCH' && preg_match('#^/admin/users/([^/]+)/reset-password$#', $path, $matches)) {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->resetPassword($matches[1]);
    exit;
}

// PATCH - Update own password (requires auth)
if ($method === 'PATCH' && $path === '/user/update-password') {
    $authResult = $authMiddleware->requireLogin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $employee_id = $request['user']['employee_id'] ?? null;
    if (!$employee_id) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    $controller->updatePassword($employee_id, $input);
    exit;
}

// POST - Clear cache (requires auth)
if ($method === 'POST' && $path === '/user/cache/clear') {
    $authResult = $authMiddleware->requireLogin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $controller->clearCache([], $request['user'] ?? null);
    exit;
}

// =============================================
// NEW ROUTES FROM BACKEND BRANCH (ATTENDANCE, LEAVE, STATS, SHEETS)
// =============================================

// ---- Attendance ----
if ($method === 'GET' && $path === '/admin/attendance') {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $data = fetch_attendance([
        'q' => $_GET['q'] ?? null,
        'start_date' => $_GET['start_date'] ?? null,
        'end_date' => $_GET['end_date'] ?? null
    ]);
    json_response(['success' => true, 'data' => $data, 'meta' => ['count' => count($data)]]);
}

if ($method === 'POST' && $path === '/admin/attendance') {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    // route_create_attendance logic
    $body = request_body();
    $user = find_user_identifier($body['employee_id'] ?? '');
    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found.'], 404);
    }
    $eventTime = $body['event_time'] ?? date('Y-m-d H:i:s');
    $eventType = $body['event_type'] ?? 'in';
    $stmt = db()->prepare('INSERT INTO attendance_logs (id, user_id, event_type, event_time, check_in_method, sync_status, location, device_info)
                           VALUES (:id, :user_id, :event_type, :event_time, :method, :sync_status, :location, :device_info)');
    $id = bin2hex(random_bytes(16));
    $stmt->execute([
        ':id' => $id,
        ':user_id' => $user['user_id'],
        ':event_type' => $eventType,
        ':event_time' => $eventTime,
        ':method' => $body['check_in_method'] ?? 'manual',
        ':sync_status' => $body['sync_status'] ?? 'pending',
        ':location' => $body['location'] ?? 'Main Entrance',
        ':device_info' => $body['device_info'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? null),
    ]);
    $record = fetch_attendance(['event_ids' => [$id]])[0] ?? null;
    json_response(['success' => true, 'message' => 'Attendance log created.', 'data' => $record], 201);
}

if (($method === 'PUT' || $method === 'PATCH') && preg_match('#^/admin/attendance/([^/]+)$#', $path, $m)) {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    // route_update_attendance logic (simplified)
    $id = $m[1];
    $body = request_body();
    $updates = [];
    $params = [':id' => $id];
    foreach (['event_type', 'event_time', 'location', 'device_info', 'sync_status'] as $field) {
        if (isset($body[$field])) {
            $updates[] = "$field = :$field";
            $params[":$field"] = $body[$field];
        }
    }
    if (empty($updates)) {
        json_response(['success' => false, 'message' => 'No fields to update.'], 400);
    }
    db()->prepare('UPDATE attendance_logs SET ' . implode(', ', $updates) . ' WHERE id = :id')->execute($params);
    json_response(['success' => true, 'message' => 'Attendance updated.']);
}

if ($method === 'DELETE' && preg_match('#^/admin/attendance/([^/]+)$#', $path, $m)) {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    db()->prepare('DELETE FROM attendance_logs WHERE id = :id')->execute([':id' => $m[1]]);
    json_response(['success' => true, 'message' => 'Attendance deleted.']);
}

// ---- Dashboard Stats ----
if ($method === 'GET' && in_array($path, ['/admin/stats', '/admin/dashboard/stats'])) {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    $stats = [
        'currentlyOnsite' => (int) db()->query("SELECT COUNT(*) FROM (SELECT user_id FROM attendance_logs WHERE event_type = 'in' GROUP BY user_id) t")->fetchColumn(),
        'totalStaffToday' => (int) db()->query("SELECT COUNT(DISTINCT user_id) FROM attendance_logs WHERE event_type = 'in' AND DATE(event_time) = CURDATE()")->fetchColumn(),
        'pendingSync' => (int) db()->query("SELECT COUNT(*) FROM attendance_logs WHERE sync_status = 'pending'")->fetchColumn(),
        'totalEvents' => (int) db()->query("SELECT COUNT(*) FROM attendance_logs WHERE DATE(event_time) = CURDATE()")->fetchColumn(),
    ];
    json_response(['success' => true, 'data' => $stats]);
}

// ---- Google Sheets (simplified) ----
if ($method === 'GET' && $path === '/admin/sheets/status') {
    $authResult = $authMiddleware->requireAdmin($request);
    if ($authResult !== null) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }
    // Placeholder: return not connected until you implement GoogleSheetsService
    json_response(['success' => true, 'connected' => false, 'message' => 'Google Sheets service not yet configured.']);
}

// ---- Leave Requests (include external files) ----
if ($path === '/leave-request' || $path === '/leave-requests' || str_starts_with($path, '/admin/leave-requests')) {
    // Include leave dependencies
    require_once __DIR__ . '/../services/GoogleSheetsService.php'; // if needed
    require_once __DIR__ . '/../types/LeaveInterface.php';
    require_once __DIR__ . '/../utils/LeaveValidator.php';
    require_once __DIR__ . '/../models/LeaveDb.php';
    require_once __DIR__ . '/../controllers/LeaveController.php';
    require_once __DIR__ . '/../routes/LeaveRoutes.php';

    $leaveModel = new LeaveController(new LeaveDbModel(new LeaveDb(db())), $authMiddleware);
    handleLeaveRoutes($leaveModel, $request);
    exit;
}

// =============================================
// 404 NOT FOUND
// =============================================
http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => 'Route not found: ' . $method . ' ' . $path
]);
exit;