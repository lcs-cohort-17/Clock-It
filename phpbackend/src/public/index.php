<?php

declare(strict_types=1);

$autoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/GoogleSheetsService.php';
// require_once __DIR__ . '/api/backend_proxy.php';

use Config\Database;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use Tuupola\Middleware\CorsMiddleware;

// =============================================
// ERROR HANDLING (PHP equivalent)
// =============================================
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Change to 1 in development

// Global exception handler
set_exception_handler(function ($exception) {
    error_log('UNCAUGHT EXCEPTION: ' . $exception->getMessage());
    error_log('Stack: ' . $exception->getTraceAsString());

    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Internal Server Error',
        'message' => $exception->getMessage(),
    ], JSON_UNESCAPED_SLASHES);
});

// Global error handler for warnings/notices (similar to unhandledRejection)
set_error_handler(function ($severity, $message, $file, $line) {
    error_log("ERROR [$severity]: $message in $file on line $line");
});

// =============================================
// SETUP
// =============================================
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../types/LeaveInterface.php';
require_once __DIR__ . '/../utils/LeaveValidator.php';
require_once __DIR__ . '/../models/LeaveDb.php';
require_once __DIR__ . '/../controllers/LeaveController.php';
require_once __DIR__ . '/../routes/LeaveRoutes.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

// The Slim app object is not used directly in this legacy entry point,
// but the structure is preserved for the merge-friendly format.
// $app = AppFactory::create();

// CORS
function sendCorsHeaders(): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
}

// Parse JSON body equivalent
function leaveReadJsonBody(): array
{
    $rawBody = file_get_contents('php://input');
    if ($rawBody === false || trim($rawBody) === '') {
        return [];
    }

    $decoded = json_decode($rawBody, true);

    return is_array($decoded) ? $decoded : [];
}

function buildLeaveRequest(): array
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];

    return [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        'uri' => parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/',
        'headers' => array_change_key_case($headers, CASE_LOWER),
        'body' => leaveReadJsonBody(),
        'query' => $_GET ?? [],
    ];
}

function jsonResponse(array $payload, int $status): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendCorsHeaders();
    http_response_code(204);
    exit;
}

function db(): PDO
{
    return Database::getInstance()->getConnection();
}

function json_response(array $body, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function request_body(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $json = $raw !== '' ? json_decode($raw, true) : null;
    if (is_array($json)) {
        return $json;
    }

    return $_POST ?: [];
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

    if ($scriptName !== '' && str_starts_with($path, $scriptName)) {
        $path = substr($path, strlen($scriptName)) ?: '/';
    }

    if ($path === '/index.php') {
        return '/';
    }

    if (str_starts_with($path, '/index.php/')) {
        return substr($path, strlen('/index.php')) ?: '/';
    }

    return rtrim($path, '/') ?: '/';
}

function uuid_v4(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function normalize_role(string $role): string
{
    $role = strtolower(trim($role));
    return in_array($role, ['staff', 'manager', 'admin'], true) ? $role : 'staff';
}

function display_role(string $role): string
{
    return match (strtolower($role)) {
        'admin' => 'Admin',
        'manager' => 'Manager',
        default => 'Staff',
    };
}

function split_full_name(string $name): array
{
    $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $first = $parts[0] ?? '';
    $last = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    return [$first, $last];
}

function user_public(array $row): array
{
    $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    if ($name === '') {
        $name = $row['email'] ?? 'Unknown User';
    }

    return [
        'id' => $row['user_id'],
        'user_id' => $row['user_id'],
        'name' => $name,
        'first_name' => $row['first_name'] ?? '',
        'last_name' => $row['last_name'] ?? '',
        'email' => $row['email'] ?? '',
        'employeeId' => $row['employee_id'] ?? '',
        'employee_id' => $row['employee_id'] ?? '',
        'role' => display_role((string) ($row['role'] ?? 'staff')),
        'role_key' => strtolower((string) ($row['role'] ?? 'staff')),
        'status' => ((int) ($row['is_active'] ?? 0)) === 1 ? 'Active' : 'Inactive',
        'is_active' => (int) ($row['is_active'] ?? 0),
        'img' => $row['img'] ?? null,
    ];
}

function find_user_identifier(string $identifier): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE user_id = :id OR employee_id = :id LIMIT 1');
    $stmt->execute([':id' => $identifier]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function next_employee_id(string $role): string
{
    $prefix = strtolower($role) === 'admin' ? 'A-' : 'S-';
    $base = strtolower($role) === 'admin' ? 1 : 101;

    $stmt = db()->prepare('SELECT employee_id FROM users WHERE employee_id LIKE :prefix ORDER BY employee_id DESC LIMIT 1');
    $stmt->execute([':prefix' => $prefix . '%']);
    $last = (string) ($stmt->fetchColumn() ?: '');

    if (preg_match('/^(?:A|S)-(\d+)$/i', $last, $m)) {
        $next = max($base, ((int) $m[1]) + 1);
    } else {
        $next = $base;
    }

    return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
}

function format_event_type(string $eventType): string
{
    return match (strtolower($eventType)) {
        'in', 'clock_in' => 'Clock In',
        'out', 'clock_out' => 'Clock Out',
        default => ucwords(str_replace('_', ' ', $eventType)),
    };
}

function format_sync_status(string $syncStatus): string
{
    return match (strtolower($syncStatus)) {
        'synced' => 'Synced',
        'failed' => 'Failed',
        default => 'Pending',
    };
}

function attendance_public(array $row): array
{
    $staffName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: 'Unknown Staff';
    $eventType = (string) ($row['event_type'] ?? '');
    $syncStatus = (string) ($row['sync_status'] ?? 'pending');

    return [
        'id' => $row['id'],
        'user_id' => $row['user_id'] ?? null,
        'employee_id' => $row['employee_id'] ?? null,
        'staff' => $staffName,
        'staff_name' => $staffName,
        'type' => format_event_type($eventType),
        'event_type' => $eventType,
        'timestamp' => $row['event_time'] ?? '',
        'event_time' => $row['event_time'] ?? '',
        'device' => $row['device_info'] ?? '',
        'device_info' => $row['device_info'] ?? '',
        'location' => $row['location'] ?? '',
        'sync' => format_sync_status($syncStatus),
        'sync_status' => $syncStatus,
        'created_at' => $row['created_at'] ?? null,
    ];
}

function fetch_attendance(array $filters = []): array
{
    $where = [];
    $params = [];

    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
        $where[] = 'DATE(al.event_time) BETWEEN :start_date AND :end_date';
        $params[':start_date'] = $filters['start_date'];
        $params[':end_date'] = $filters['end_date'];
    }

    if (!empty($filters['event_ids']) && is_array($filters['event_ids'])) {
        $ids = array_values(array_filter($filters['event_ids'], static fn($id) => is_scalar($id) && trim((string) $id) !== ''));
        if ($ids) {
            $placeholders = [];
            foreach ($ids as $i => $id) {
                $key = ':id' . $i;
                $placeholders[] = $key;
                $params[$key] = (string) $id;
            }
            $where[] = 'al.id IN (' . implode(',', $placeholders) . ')';
        }
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
            ORDER BY al.event_time DESC, al.created_at DESC";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return array_map('attendance_public', $stmt->fetchAll() ?: []);
}

function require_bearer_token(): void
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    // Accept any non-empty bearer token — the frontend issues one on login.
    // Swap this for JWT validation if you add firebase/php-jwt later.
    if (!preg_match('/^Bearer\s+\S+$/i', $header)) {
        json_response(['success' => false, 'message' => 'Unauthorised.'], 401);
    }
}

function route_login(): never
{
    $body = request_body();
    $loginMethod = (string) ($body['loginMethod'] ?? 'email');
    $password = (string) ($body['password'] ?? '');

    if ($loginMethod === 'employeeId') {
        $identifier = trim((string) ($body['employeeId'] ?? $body['employee_id'] ?? ''));
        $stmt = db()->prepare('SELECT * FROM users WHERE employee_id = :identifier LIMIT 1');
    } else {
        $identifier = strtolower(trim((string) ($body['email'] ?? '')));
        $stmt = db()->prepare('SELECT * FROM users WHERE LOWER(email) = :identifier LIMIT 1');
    }

    if ($identifier === '' || $password === '') {
        json_response(['success' => false, 'message' => 'Email/employee ID and password are required.'], 400);
    }

    $stmt->execute([':identifier' => $identifier]);
    $user = $stmt->fetch();

    $validPassword = false;
    if ($user) {
        $stored = (string) ($user['password'] ?? '');
        $validPassword = password_verify($password, $stored) || hash_equals($stored, $password);
    }

    if (!$user || !$validPassword || (int) ($user['is_active'] ?? 0) !== 1) {
        json_response(['success' => false, 'message' => 'Invalid credentials or inactive account.'], 401);
    }

    $public = user_public($user);
    json_response([
        'success' => true,
        'message' => 'Login successful.',
        'role' => $public['role_key'],
        'user' => $public,
        'token' => base64_encode(random_bytes(32)),
    ]);
}

function route_get_users(): never
{
    $q = trim((string) ($_GET['q'] ?? ''));
    $where = '';
    $params = [];

    if ($q !== '') {
        $where = 'WHERE first_name LIKE :q OR last_name LIKE :q OR email LIKE :q OR employee_id LIKE :q';
        $params[':q'] = '%' . $q . '%';
    }

    $stmt = db()->prepare("SELECT * FROM users {$where} ORDER BY is_active DESC, first_name ASC, last_name ASC");
    $stmt->execute($params);
    $users = array_map('user_public', $stmt->fetchAll() ?: []);

    json_response(['success' => true, 'data' => $users, 'meta' => ['count' => count($users)]]);
}

function route_create_user(): never
{
    $body = request_body();
    [$firstName, $lastName] = split_full_name((string) ($body['name'] ?? ''));
    $firstName = trim((string) ($body['first_name'] ?? $firstName));
    $lastName = trim((string) ($body['last_name'] ?? $lastName));
    $email = strtolower(trim((string) ($body['email'] ?? '')));
    $role = normalize_role((string) ($body['role'] ?? 'staff'));
    $employeeId = trim((string) ($body['employee_id'] ?? $body['employeeId'] ?? '')) ?: next_employee_id($role);
    $plainPassword = (string) ($body['password'] ?? '');

    if ($firstName === '' || $email === '') {
        json_response(['success' => false, 'message' => 'First name/name and email are required.'], 400);
    }

    if ($plainPassword === '') {
        $plainPassword = substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(12))), 0, 10);
    }

    $stmt = db()->prepare('INSERT INTO users (user_id, first_name, last_name, employee_id, role, is_active, email, password, img)
                           VALUES (:user_id, :first_name, :last_name, :employee_id, :role, 1, :email, :password, :img)');
    $stmt->execute([
        ':user_id' => uuid_v4(),
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':employee_id' => $employeeId,
        ':role' => $role,
        ':email' => $email,
        ':password' => password_hash($plainPassword, PASSWORD_DEFAULT),
        ':img' => $body['img'] ?? null,
    ]);

    $created = find_user_identifier($employeeId);
    json_response([
        'success' => true,
        'message' => 'User created.',
        'data' => user_public($created ?: []),
        'generated_password' => $plainPassword,
    ], 201);
}

function route_update_user(?string $identifier = null): never
{
    $body = request_body();
    $identifier = $identifier ?: (string) ($body['id'] ?? $body['employee_id'] ?? $body['employeeId'] ?? '');
    $user = $identifier !== '' ? find_user_identifier($identifier) : null;

    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found.'], 404);
    }

    [$firstName, $lastName] = split_full_name((string) ($body['name'] ?? ''));
    $updates = [];
    $params = [':id' => $user['user_id']];

    $allowed = [
        'first_name' => trim((string) ($body['first_name'] ?? $firstName)),
        'last_name' => trim((string) ($body['last_name'] ?? $lastName)),
        'email' => strtolower(trim((string) ($body['email'] ?? ''))),
        'role' => isset($body['role']) ? normalize_role((string) $body['role']) : '',
        'employee_id' => trim((string) ($body['employee_id'] ?? $body['employeeId'] ?? '')),
        'img' => isset($body['img']) ? (string) $body['img'] : null,
    ];

    foreach ($allowed as $column => $value) {
        if ($value === '' || $value === null) {
            continue;
        }
        $updates[] = "{$column} = :{$column}";
        $params[':' . $column] = $value;
    }

    if (!$updates) {
        json_response(['success' => false, 'message' => 'No valid user fields supplied.'], 400);
    }

    $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE user_id = :id';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    $updated = find_user_identifier((string) $user['user_id']);
    json_response(['success' => true, 'message' => 'User updated.', 'data' => user_public($updated ?: [])]);
}

function route_toggle_user(): never
{
    $body = request_body();
    $identifier = (string) ($body['id'] ?? $body['employee_id'] ?? $body['employeeId'] ?? '');
    $user = $identifier !== '' ? find_user_identifier($identifier) : null;

    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found.'], 404);
    }

    $newStatus = ((int) $user['is_active']) === 1 ? 0 : 1;
    $stmt = db()->prepare('UPDATE users SET is_active = :status WHERE user_id = :id');
    $stmt->execute([':status' => $newStatus, ':id' => $user['user_id']]);

    $updated = find_user_identifier((string) $user['user_id']);
    json_response(['success' => true, 'message' => 'User status updated.', 'data' => user_public($updated ?: [])]);
}

function route_reset_password(): never
{
    $body = request_body();
    $identifier = (string) ($body['id'] ?? $body['employee_id'] ?? $body['employeeId'] ?? '');
    $user = $identifier !== '' ? find_user_identifier($identifier) : null;

    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found.'], 404);
    }

    $plainPassword = substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(12))), 0, 10);
    $stmt = db()->prepare('UPDATE users SET password = :password WHERE user_id = :id');
    $stmt->execute([':password' => password_hash($plainPassword, PASSWORD_DEFAULT), ':id' => $user['user_id']]);

    json_response(['success' => true, 'message' => 'Password reset.', 'generated_password' => $plainPassword]);
}

function resolve_attendance_user(array $body): ?array
{
    $identifier = trim((string) ($body['user_id'] ?? $body['employee_id'] ?? $body['employeeId'] ?? ''));
    if ($identifier !== '') {
        return find_user_identifier($identifier);
    }

    $email = strtolower(trim((string) ($body['email'] ?? '')));
    if ($email !== '') {
        $stmt = db()->prepare('SELECT * FROM users WHERE LOWER(email) = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    $staffName = trim((string) ($body['staff_name'] ?? $body['staff'] ?? ''));
    if ($staffName !== '') {
        [$first, $last] = split_full_name($staffName);
        $stmt = db()->prepare('SELECT * FROM users WHERE first_name = :first AND last_name = :last LIMIT 1');
        $stmt->execute([':first' => $first, ':last' => $last]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    return null;
}

function normalize_event_type(string $eventType): string
{
    $eventType = strtolower(trim(str_replace(' ', '_', $eventType)));
    return match ($eventType) {
        'clock_in', 'in' => 'in',
        'clock_out', 'out' => 'out',
        default => 'in',
    };
}

function route_create_attendance(): never
{
    $body = request_body();
    $user = resolve_attendance_user($body);

    if (!$user) {
        json_response(['success' => false, 'message' => 'Valid user_id, employee_id, email, or staff_name is required.'], 400);
    }

    $eventTime = trim((string) ($body['event_time'] ?? $body['timestamp'] ?? '')) ?: date('Y-m-d H:i:s');
    $eventType = normalize_event_type((string) ($body['event_type'] ?? $body['type'] ?? 'in'));
    $syncStatus = in_array(($body['sync_status'] ?? 'pending'), ['synced', 'pending', 'failed'], true) ? $body['sync_status'] : 'pending';

    $stmt = db()->prepare('INSERT INTO attendance_logs (id, user_id, event_type, event_time, check_in_method, sync_status, location, device_info)
                           VALUES (:id, :user_id, :event_type, :event_time, :method, :sync_status, :location, :device_info)');
    $id = uuid_v4();
    $stmt->execute([
        ':id' => $id,
        ':user_id' => $user['user_id'],
        ':event_type' => $eventType,
        ':event_time' => $eventTime,
        ':method' => $body['check_in_method'] ?? 'manual',
        ':sync_status' => $syncStatus,
        ':location' => $body['location'] ?? 'Main Entrance',
        ':device_info' => $body['device_info'] ?? $body['device'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? null),
    ]);

    json_response(['success' => true, 'message' => 'Attendance log created.', 'data' => fetch_attendance(['event_ids' => [$id]])[0] ?? null], 201);
}

function route_update_attendance(string $id): never
{
    $body = request_body();

    $stmt = db()->prepare('SELECT * FROM attendance_logs WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);

    if (!$stmt->fetch()) {
        json_response([
            'success' => false,
            'message' => 'Attendance log not found.',
        ], 404);
    }

    $updates = [];
    $params = [':id' => $id];
    $changedDataFields = false;

    if (isset($body['event_type']) || isset($body['type'])) {
        $updates[] = 'event_type = :event_type';
        $params[':event_type'] = normalize_event_type((string) ($body['event_type'] ?? $body['type']));
        $changedDataFields = true;
    }

    if (isset($body['event_time']) || isset($body['timestamp'])) {
        $updates[] = 'event_time = :event_time';
        $params[':event_time'] = (string) ($body['event_time'] ?? $body['timestamp']);
        $changedDataFields = true;
    }

    if (isset($body['location'])) {
        $updates[] = 'location = :location';
        $params[':location'] = (string) $body['location'];
        $changedDataFields = true;
    }

    if (isset($body['device_info']) || isset($body['device'])) {
        $updates[] = 'device_info = :device_info';
        $params[':device_info'] = (string) ($body['device_info'] ?? $body['device']);
        $changedDataFields = true;
    }

    if (
        isset($body['user_id']) ||
        isset($body['employee_id']) ||
        isset($body['employeeId']) ||
        isset($body['email']) ||
        isset($body['staff_name']) ||
        isset($body['staff'])
    ) {
        $user = resolve_attendance_user($body);

        if (!$user) {
            json_response([
                'success' => false,
                'message' => 'Replacement user not found.',
            ], 400);
        }

        $updates[] = 'user_id = :user_id';
        $params[':user_id'] = $user['user_id'];
        $changedDataFields = true;
    }

    if (isset($body['sync_status'])) {
        $updates[] = 'sync_status = :sync_status';
        $params[':sync_status'] = in_array($body['sync_status'], ['synced', 'pending', 'failed'], true)
            ? $body['sync_status']
            : 'pending';
    } elseif ($changedDataFields) {
        /*
         * Any frontend edit means the database is now newer than Google Sheets.
         * Mark it pending so it can be exported/pushed again.
         */
        $updates[] = 'sync_status = :sync_status';
        $params[':sync_status'] = 'pending';
    }

    if (!$updates) {
        json_response([
            'success' => false,
            'message' => 'No valid attendance fields supplied.',
        ], 400);
    }

    $sql = 'UPDATE attendance_logs SET ' . implode(', ', $updates) . ' WHERE id = :id';
    db()->prepare($sql)->execute($params);

    json_response([
        'success' => true,
        'message' => 'Attendance log updated.',
        'data' => fetch_attendance(['event_ids' => [$id]])[0] ?? null,
    ]);
}

function route_delete_attendance(string $id): never
{
    $stmt = db()->prepare('DELETE FROM attendance_logs WHERE id = :id');
    $stmt->execute([':id' => $id]);
    json_response(['success' => true, 'message' => 'Attendance log deleted.', 'deleted' => $stmt->rowCount()]);
}

function route_stats(): never
{
    $stats = [
        'currentlyOnsite' => 0,
        'totalStaffToday' => 0,
        'pendingSync' => 0,
        'totalEvents' => 0,
    ];

    $stats['currentlyOnsite'] = (int) db()->query("SELECT COUNT(*) FROM (
        SELECT al.user_id, al.event_type
        FROM attendance_logs al
        INNER JOIN (SELECT user_id, MAX(event_time) AS max_time FROM attendance_logs GROUP BY user_id) latest
          ON latest.user_id = al.user_id AND latest.max_time = al.event_time
        WHERE al.event_type = 'in'
    ) onsite")->fetchColumn();

    $stats['totalStaffToday'] = (int) db()->query("SELECT COUNT(DISTINCT user_id) FROM attendance_logs WHERE event_type = 'in' AND DATE(event_time) = CURDATE()")->fetchColumn();
    $stats['pendingSync'] = (int) db()->query("SELECT COUNT(*) FROM attendance_logs WHERE sync_status = 'pending'")->fetchColumn();
    $stats['totalEvents'] = (int) db()->query("SELECT COUNT(*) FROM attendance_logs WHERE DATE(event_time) = CURDATE()")->fetchColumn();

    json_response(['success' => true, 'data' => $stats] + $stats);
}

function route_recent_activity(): never
{
    $stmt = db()->query("SELECT al.id, al.event_type, al.event_time, u.first_name, u.last_name
                         FROM attendance_logs al
                         LEFT JOIN users u ON u.user_id = al.user_id
                         ORDER BY al.event_time DESC
                         LIMIT 10");
    $data = [];
    foreach ($stmt->fetchAll() ?: [] as $row) {
        $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: 'Unknown Staff';
        $data[] = [
            'id' => $row['id'],
            'name' => $name,
            'action' => format_event_type((string) $row['event_type']),
            'timestamp' => $row['event_time'],
        ];
    }

    json_response(['success' => true, 'data' => $data]);
}

function route_onsite(): never
{
    $stmt = db()->query("SELECT al.id, al.event_time, al.location, u.user_id, u.first_name, u.last_name, u.role
                         FROM attendance_logs al
                         INNER JOIN (SELECT user_id, MAX(event_time) AS max_time FROM attendance_logs GROUP BY user_id) latest
                           ON latest.user_id = al.user_id AND latest.max_time = al.event_time
                         LEFT JOIN users u ON u.user_id = al.user_id
                         WHERE al.event_type = 'in'
                         ORDER BY al.event_time DESC");
    $data = [];
    foreach ($stmt->fetchAll() ?: [] as $row) {
        $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: 'Unknown Staff';
        $data[] = [
            'id' => $row['user_id'] ?? $row['id'],
            'name' => $name,
            'role' => display_role((string) ($row['role'] ?? 'staff')),
            'signed_in_at' => $row['event_time'],
            'location' => $row['location'] ?? '',
        ];
    }

    json_response(['success' => true, 'data' => $data]);
}

function rows_for_sheet(array $attendance): array
{
    $rows = [[
        'Log ID',
        'Employee ID',
        'Staff Name',
        'Event Type',
        'Event Time',
        'Location',
        'Device',
        'Sync Status'
    ]];

    foreach ($attendance as $row) {
        $rows[] = [
            $row['id'] ?? '',
            $row['employee_id'] ?? '',
            $row['staff_name'] ?? $row['staff'] ?? '',
            $row['event_type'] ?? '',
            $row['event_time'] ?? $row['timestamp'] ?? '',
            $row['location'] ?? '',
            $row['device_info'] ?? $row['device'] ?? '',
            $row['sync_status'] ?? 'synced',
        ];
    }

    return $rows;
}

function header_map(array $header): array
{
    $map = [];
    foreach ($header as $index => $name) {
        $key = strtolower(trim((string) $name));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        $map[$key] = $index;
    }
    return $map;
}

function row_value(array $row, array $map, array $keys, string $default = ''): string
{
    foreach ($keys as $key) {
        if (isset($map[$key]) && array_key_exists($map[$key], $row)) {
            return trim((string) $row[$map[$key]]);
        }
    }
    return $default;
}

function sheet_sync_status(string $value): string
{
    $value = strtolower(trim($value));
    return in_array($value, ['synced', 'pending', 'failed'], true) ? $value : 'synced';
}

function sheet_id_is_safe(string $id): bool
{
    return (bool) preg_match('/^[a-f0-9-]{36}$/i', trim($id));
}

function normalize_sheet_datetime(string $value): string
{
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    // Keep normal MySQL DATETIME values as-is.
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/', $value)) {
        return strlen($value) === 16 ? $value . ':00' : $value;
    }

    // Convert ISO values like 2026-06-04T08:03:49 into MySQL DATETIME.
    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $value)) {
        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : $value;
    }

    // Convert common Google Sheets date strings if PHP can parse them.
    $timestamp = strtotime($value);
    return $timestamp ? date('Y-m-d H:i:s', $timestamp) : $value;
}

function import_rows_to_db(array $rows): array
{
    if (count($rows) < 2) {
        return [
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];
    }

    $map = header_map($rows[0]);

    $imported = 0;
    $updated = 0;
    $skipped = 0;
    $errors = [];

    for ($i = 1; $i < count($rows); $i++) {
        $rowNumber = $i + 1;
        $row = $rows[$i];

        if (!array_filter($row, static fn($cell) => trim((string) $cell) !== '')) {
            continue;
        }

        $sheetLogId = row_value($row, $map, ['log_id', 'id', 'attendance_id', 'record_id']);
        $employeeId = row_value($row, $map, ['employee_id', 'employeeid', 'employee']);
        $email = strtolower(row_value($row, $map, ['email', 'email_address']));
        $staffName = row_value($row, $map, ['staff_name', 'name', 'full_name']);
        $eventType = normalize_event_type(row_value($row, $map, ['event_type', 'type', 'action'], 'in'));
        $eventTime = normalize_sheet_datetime(row_value($row, $map, ['event_time', 'timestamp', 'time', 'date_time']));
        $location = row_value($row, $map, ['location'], 'Main Entrance');
        $device = row_value($row, $map, ['device', 'device_info']);
        $syncStatus = sheet_sync_status(row_value($row, $map, ['sync_status', 'sync'], 'synced'));

        if ($eventTime === '') {
            $skipped++;
            $errors[] = "Row {$rowNumber}: missing event time.";
            continue;
        }

        /*
         * 1. Best match: Log ID from exported Google Sheet.
         * This prevents duplicates when Event Time is edited in Google Sheets.
         */
        $existingById = null;

        if ($sheetLogId !== '') {
            $stmt = db()->prepare('SELECT * FROM attendance_logs WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $sheetLogId]);
            $existingById = $stmt->fetch() ?: null;
        }

        /*
         * Resolve user only when possible.
         * If Log ID exists, we can update the log even if user columns are missing.
         */
        $user = null;

        if ($employeeId !== '') {
            $user = find_user_identifier($employeeId);
        }

        if (!$user && $email !== '') {
            $stmt = db()->prepare('SELECT * FROM users WHERE LOWER(email) = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch() ?: null;
        }

        /*
         * Auto-create user only when the sheet gives enough safe identity data.
         * Exported sheets usually do not include email, so this mostly helps manual imports.
         */
        if (!$user && !$existingById && $employeeId !== '' && $email !== '') {
            [$first, $last] = split_full_name($staffName ?: $email);
            $newUserId = uuid_v4();

            db()->prepare('
                INSERT INTO users (
                    user_id,
                    first_name,
                    last_name,
                    employee_id,
                    role,
                    is_active,
                    email,
                    password
                )
                VALUES (
                    :id,
                    :first,
                    :last,
                    :employee,
                    :role,
                    1,
                    :email,
                    :password
                )
            ')->execute([
                ':id' => $newUserId,
                ':first' => $first ?: 'Sheet',
                ':last' => $last,
                ':employee' => $employeeId,
                ':role' => 'staff',
                ':email' => $email,
                ':password' => password_hash(
                    substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(12))), 0, 10),
                    PASSWORD_DEFAULT
                ),
            ]);

            $user = find_user_identifier($employeeId);
        }

        /*
         * If Log ID already exists in the database, update that exact row.
         * This is the important part. This stops the duplicate-row nonsense.
         */
        if ($existingById) {
            $sql = '
                UPDATE attendance_logs
                SET
                    event_type = :event_type,
                    event_time = :event_time,
                    location = :location,
                    device_info = :device,
                    sync_status = :sync
            ';

            $params = [
                ':event_type' => $eventType,
                ':event_time' => $eventTime,
                ':location' => $location,
                ':device' => $device,
                ':sync' => $syncStatus,
                ':id' => $existingById['id'],
            ];

            /*
             * Only update user_id if the sheet identifies a valid user.
             * Otherwise keep the existing user_id.
             */
            if ($user) {
                $sql .= ', user_id = :user_id';
                $params[':user_id'] = $user['user_id'];
            }

            $sql .= ' WHERE id = :id';

            db()->prepare($sql)->execute($params);

            $updated++;
            continue;
        }

        /*
         * If there is no Log ID match, we need a valid user before inserting/updating.
         */
        if (!$user) {
            $skipped++;
            $errors[] = "Row {$rowNumber}: no matching user. Add Employee ID + Email to auto-create, or keep a valid Log ID from an existing database row.";
            continue;
        }

        /*
         * 2. Fallback match: old behaviour.
         * Useful for manually-created Google Sheet rows that do not have Log ID.
         */
        $existing = db()->prepare('
            SELECT id
            FROM attendance_logs
            WHERE user_id = :user_id
              AND event_type = :event_type
              AND event_time = :event_time
            LIMIT 1
        ');

        $existing->execute([
            ':user_id' => $user['user_id'],
            ':event_type' => $eventType,
            ':event_time' => $eventTime,
        ]);

        $existingId = $existing->fetchColumn();

        if ($existingId) {
            db()->prepare('
                UPDATE attendance_logs
                SET
                    location = :location,
                    device_info = :device,
                    sync_status = :sync
                WHERE id = :id
            ')->execute([
                ':location' => $location,
                ':device' => $device,
                ':sync' => $syncStatus,
                ':id' => $existingId,
            ]);

            $updated++;
            continue;
        }

        /*
         * 3. New row.
         * Preserve the Google Sheet Log ID only if it looks like your UUID format.
         */
        $newAttendanceId = sheet_id_is_safe($sheetLogId) ? $sheetLogId : uuid_v4();

        db()->prepare('
            INSERT INTO attendance_logs (
                id,
                user_id,
                event_type,
                event_time,
                check_in_method,
                sync_status,
                location,
                device_info
            )
            VALUES (
                :id,
                :user_id,
                :event_type,
                :event_time,
                :method,
                :sync,
                :location,
                :device
            )
        ')->execute([
            ':id' => $newAttendanceId,
            ':user_id' => $user['user_id'],
            ':event_type' => $eventType,
            ':event_time' => $eventTime,
            ':method' => 'manual',
            ':sync' => $syncStatus,
            ':location' => $location,
            ':device' => $device,
        ]);

        $imported++;
    }

    return [
        'imported' => $imported,
        'updated' => $updated,
        'skipped' => $skipped,
        'errors' => $errors,
    ];
}

function route_sheets_status(): never
{
    $service = new GoogleSheetsService();
    json_response([
        'success' => true,
        'connected' => $service->isConnected(),
        'sheet_id' => $service->spreadsheetId(),
        'sheet_url' => $service->url(),
        'message' => $service->isConnected()
            ? 'Service account is ready. Make sure the spreadsheet is shared with the service-account email.'
            : 'Google Sheets is not connected. Check composer install, credentials.json, and spreadsheet sharing.',
    ]);
}

function route_sheets_export(): never
{
    $body = request_body();
    $filters = [
        'start_date' => $body['start_date'] ?? null,
        'end_date' => $body['end_date'] ?? null,
        'event_ids' => is_array($body['event_ids'] ?? null) ? $body['event_ids'] : [],
    ];
    $attendance = fetch_attendance($filters);

    if (!$attendance) {
        json_response(['success' => false, 'message' => 'No attendance logs found for export.'], 200);
    }

    $service = new GoogleSheetsService();
    if (!$service->isConnected()) {
        json_response(['success' => false, 'message' => 'Google Sheets service account is not connected.'], 500);
    }

    if ($service->spreadsheetId() === '') {
        $service->createSpreadsheet('Clock-It Attendance Export ' . date('Y-m-d H:i:s'));
    }

    if ($service->spreadsheetId() === '' || !$service->replaceRows(rows_for_sheet($attendance), 'A1')) {
        json_response(['success' => false, 'message' => 'Failed to write attendance logs to Google Sheets.'], 500);
    }

    $ids = array_column($attendance, 'id');
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        db()->prepare("UPDATE attendance_logs SET sync_status = 'synced' WHERE id IN ({$placeholders})")->execute($ids);
    }

    json_response([
        'success' => true,
        'message' => 'Attendance exported successfully.',
        'sheet_url' => $service->url(),
        'sheet_id' => $service->spreadsheetId(),
        'records_exported' => count($attendance),
    ]);
}

function route_sheets_import(): never
{
    $body = request_body();
    $range = trim((string) ($body['range'] ?? 'A1:Z')) ?: 'A1:Z';
    $service = new GoogleSheetsService();

    if (!$service->isConnected()) {
        json_response(['success' => false, 'message' => 'Google Sheets service account is not connected.'], 500);
    }

    $rows = $service->readRows($range);
    $result = import_rows_to_db($rows);

    json_response([
        'success' => true,
        'message' => 'Google Sheet import finished.',
        'records_imported' => $result['imported'],
        'records_updated' => $result['updated'],
        'records_skipped' => $result['skipped'],
        'errors' => $result['errors'],
    ]);
}

function route_sheets_push_pending(): never
{
    $stmt = db()->query("SELECT al.*, u.first_name, u.last_name, u.employee_id
                         FROM attendance_logs al
                         LEFT JOIN users u ON u.user_id = al.user_id
                         WHERE al.sync_status = 'pending'
                         ORDER BY al.event_time ASC");
    $attendance = array_map('attendance_public', $stmt->fetchAll() ?: []);

    if (!$attendance) {
        json_response(['success' => true, 'message' => 'No pending attendance records to push.', 'records_pushed' => 0]);
    }

    $service = new GoogleSheetsService();
    if (!$service->isConnected()) {
        json_response(['success' => false, 'message' => 'Google Sheets service account is not connected.'], 500);
    }

    $rows = rows_for_sheet($attendance);
    array_shift($rows);

    if (!$service->appendRows($rows, 'A1')) {
        json_response(['success' => false, 'message' => 'Failed to append pending records to Google Sheets.'], 500);
    }

    $ids = array_column($attendance, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    db()->prepare("UPDATE attendance_logs SET sync_status = 'synced' WHERE id IN ({$placeholders})")->execute($ids);

    json_response(['success' => true, 'message' => 'Pending attendance pushed to Google Sheets.', 'records_pushed' => count($attendance), 'sheet_url' => $service->url()]);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = current_path();

try {
    if ($method === 'GET' && $path === '/health') {
        json_response(['success' => true, 'message' => 'Clock-It backend is running.']);
    }

    if ($method === 'POST' && $path === '/api/login') {
        route_login();
    }

    require_bearer_token();

    if ($method === 'GET' && $path === '/api/admin/users') {
        route_get_users();
    }
    if ($method === 'POST' && $path === '/api/admin/users') {
        route_create_user();
    }
    if (in_array($method, ['PUT', 'PATCH'], true) && preg_match('#^/api/admin/users/([^/]+)$#', $path, $m)) {
        route_update_user(urldecode($m[1]));
    }
    if ($method === 'POST' && $path === '/api/admin/users/update') {
        route_update_user();
    }
    if ($method === 'POST' && $path === '/api/admin/users/toggle-status') {
        route_toggle_user();
    }
    if ($method === 'POST' && $path === '/api/admin/users/reset-password') {
        route_reset_password();
    }

    if ($method === 'GET' && $path === '/api/admin/attendance') {
        $filters = [
            'q' => $_GET['q'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
        ];
        $data = fetch_attendance($filters);
        json_response(['success' => true, 'data' => $data, 'meta' => ['count' => count($data)]]);
    }
    if ($method === 'POST' && $path === '/api/admin/attendance') {
        route_create_attendance();
    }
    if (in_array($method, ['PUT', 'PATCH'], true) && preg_match('#^/api/admin/attendance/([^/]+)$#', $path, $m)) {
        route_update_attendance(urldecode($m[1]));
    }
    if ($method === 'DELETE' && preg_match('#^/api/admin/attendance/([^/]+)$#', $path, $m)) {
        route_delete_attendance(urldecode($m[1]));
    }

    if ($method === 'GET' && in_array($path, ['/api/admin/stats', '/api/admin/dashboard/stats'], true)) {
        route_stats();
    }
    if ($method === 'GET' && $path === '/api/admin/recent-activity') {
        route_recent_activity();
    }
    if ($method === 'GET' && $path === '/api/admin/onsite') {
        route_onsite();
    }

    if ($method === 'GET' && in_array($path, ['/api/admin/sheets/status', '/api/admin/sheets/settings'], true)) {
        route_sheets_status();
    }
    if ($method === 'POST' && $path === '/api/admin/sheets/export') {
        route_sheets_export();
    }
    if ($method === 'POST' && in_array($path, ['/api/admin/sheets/import', '/api/admin/sheets/sync'], true)) {
        route_sheets_import();
    }
    if ($method === 'POST' && $path === '/api/admin/sheets/push') {
        route_sheets_push_pending();
    }
    if ($method === 'POST' && $path === '/api/admin/sheets/connect') {
        route_sheets_status();
    }
    if ($method === 'POST' && $path === '/api/admin/sheets/disconnect') {
        json_response(['success' => true, 'message' => 'Service account auth is server-side. Remove or change credentials.json to disconnect.']);
    }

    json_response(['success' => false, 'message' => 'API route not found.', 'path' => $path], 404);
} catch (Throwable $e) {
    error_log('[Clock-It API] ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Server error.', 'error' => $e->getMessage()], 500);
}
// =============================================
// HARDCODED AUTH MIDDLEWARE (like in your Node code)
// =============================================
// This app uses AuthMiddleware to decode JWT tokens and attach auth info.
// The hardcoded middleware section is preserved here for merge-form compatibility.

// =============================================
// DEBUG LOGGING MIDDLEWARE
// =============================================
$request = buildLeaveRequest();
error_log(sprintf('[DEBUG] %s %s', $request['method'], $request['uri']));

try {
    sendCorsHeaders();

    $pdo = Database::getInstance()->getConnection();
    $model = new LeaveDbModel(new LeaveDb($pdo));
    $jwtSecret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: '';

    if ($jwtSecret === '') {
        jsonResponse([
            'success' => false,
            'error' => 'JWT secret is not configured',
        ], 500);
        return;
    }

    $middleware = new \App\Middleware\AuthMiddleware($jwtSecret);
    $controller = new LeaveController($model, $middleware);

    $response = $middleware->handle($request, function (array $request) use ($controller): array {
        $GLOBALS['auth'] = $request['user'] ?? [];

        ob_start();
        handleLeaveRoutes($controller, $request);
        $body = ob_get_clean();

        return [
            'status' => http_response_code() ?: 200,
            'body' => $body,
        ];
    });

    if ($response['status'] === 404) {
        jsonResponse([
            'success' => false,
            'error' => 'Route not found: ' . $request['method'] . ' ' . $request['uri'],
        ], 404);
        return;
    }

    http_response_code($response['status']);
    header('Content-Type: application/json');
    echo is_array($response['body'])
        ? json_encode($response['body'], JSON_UNESCAPED_SLASHES)
        : $response['body'];
} catch (Throwable $exception) {
    jsonResponse([
        'success' => false,
        'error' => 'Internal Server Error',
        'message' => $exception->getMessage(),
    ], 500);
}




// echo "Server running on http://localhost:4321 (start with: php -S localhost:4321 router.php)\n";
