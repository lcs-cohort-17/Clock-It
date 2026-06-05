<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $publicFile = realpath(__DIR__ . $requestedPath);

    if (
        $requestedPath !== '/'
        && $publicFile !== false
        && str_starts_with($publicFile, __DIR__ . DIRECTORY_SEPARATOR)
        && is_file($publicFile)
    ) {
        return false;
    }
}

require __DIR__ . '/../src/bootstrap.php';

session_start();

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$basePath = PHP_SAPI === 'cli-server' || $scriptDir === '' || $scriptDir === '.'
    ? ''
    : $scriptDir;

if ($basePath !== '' && str_starts_with($path, $basePath)) {
    $path = substr($path, strlen($basePath)) ?: '/';
}

if ($path === '/index.php') {
    $path = '/';
}

if (str_starts_with($path, '/index.php/')) {
    $path = substr($path, strlen('/index.php')) ?: '/';
}

function app_url(string $path = '/'): string
{
    global $basePath;
    return $basePath . '/' . ltrim($path, '/');
}

function route_url(string $path = '/'): string
{
    return app_url($path);
}

function asset_url(string $assetPath = '/'): string
{
    return app_url(ltrim($assetPath, '/'));
}

function view(string $view, array $data = []): void
{
    global $basePath;
    $data = ['basePath' => $basePath] + $data;
    extract($data);

    // FIXED PATH
    require __DIR__ . '/../src/views/' . $view . '.php';
}

function redirect_to(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

/*
|--------------------------------------------------------------------------
| DEMO USERS
|--------------------------------------------------------------------------
*/

$staffUser = [
    'id' => 'staff-001',
    'name' => 'Sarah Mthembu',
    'email' => 'sarah@clockit.app',
    'employeeId' => 'EMP001',
    'role' => 'staff',
];

$adminUser = [
    'id' => 'admin-001',
    'name' => 'Priya Singh',
    'email' => 'admin@clockit.app',
    'employeeId' => 'A-001',
    'role' => 'admin',
];

$stats = [
    'currentlyOnsite' => 2,
    'totalStaffToday' => 15,
    'pendingSync' => 0,
    'totalEvents' => 42,
];

$loginUsers = require dirname(__DIR__) . '/src/Data/LoginMockUsers.php';

function login_user_by_email(array $users, string $email, string $password): ?array
{
    $normalizedEmail = strtolower(trim($email));

    foreach ($users as $user) {
        if (
            strtolower((string) ($user['email'] ?? '')) === $normalizedEmail
            && (string) ($user['password'] ?? '') === $password
        ) {
            return $user;
        }
    }

    return null;
}

function login_user_by_employee_id(array $users, string $employeeId): ?array
{
    $normalizedEmployeeId = strtolower(trim($employeeId));

    foreach ($users as $user) {
        if (strtolower((string) ($user['employeeId'] ?? '')) === $normalizedEmployeeId) {
            return $user;
        }
    }

    return null;
}

function login_json_response(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_THROW_ON_ERROR);
    exit;
}

function login_request_data(): array
{
    $body = file_get_contents('php://input');
    $json = $body !== false && $body !== '' ? json_decode($body, true) : null;

    return is_array($json) ? $json : $_POST;
}

function dashboard_path_for(array $user): string
{
    return strtolower((string) ($user['role'] ?? 'staff')) === 'admin'
        ? '/admin-dashboard'
        : '/staff-dashboard';
}

function current_user_or(array $fallback, ?string $role = null): array
{
    $currentUser = $_SESSION['current_user'] ?? null;

    if (!is_array($currentUser)) {
        return $fallback;
    }

    if ($role !== null && strtolower((string) ($currentUser['role'] ?? '')) !== strtolower($role)) {
        return $fallback;
    }

    return $currentUser;
}

function admin_settings_file(): string
{
    return dirname(__DIR__) . '/storage/settings_mock.json';
}

function admin_settings_read(): array
{
    $file = admin_settings_file();

    if (!file_exists($file)) {
        return [
            'session_timeout' => 30,
            'data_retention_days' => 90,
        ];
    }

    $settings = json_decode((string) file_get_contents($file), true);

    return is_array($settings) ? $settings : [
        'session_timeout' => 30,
        'data_retention_days' => 90,
    ];
}

function admin_settings_write(array $settings): void
{
    file_put_contents(
        admin_settings_file(),
        json_encode($settings, JSON_THROW_ON_ERROR)
    );
}

/*
|--------------------------------------------------------------------------
| ROUTES
|--------------------------------------------------------------------------
*/

switch ($path) {

    case '/':
        redirect_to('/login');
        break;

    case '/login':
        $title = 'Login | Clock-It';
        view('login', compact('title', 'loginUsers'));
        break;

    case '/logout':
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $cookieParams = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $cookieParams['path'],
                'domain' => $cookieParams['domain'],
                'secure' => $cookieParams['secure'],
                'httponly' => $cookieParams['httponly'],
                'samesite' => $cookieParams['samesite'],
            ]);
        }

        session_destroy();
        redirect_to('/login');
        break;

    case '/api/login':
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            login_json_response(['message' => 'Method not allowed.'], 405);
        }

        $payload = login_request_data();
        $loginMethod = (string) ($payload['loginMethod'] ?? 'email');
        $loginUser = $loginMethod === 'employeeId'
            ? login_user_by_employee_id($loginUsers, (string) ($payload['employeeId'] ?? ''))
            : login_user_by_email(
                $loginUsers,
                (string) ($payload['email'] ?? ''),
                (string) ($payload['password'] ?? '')
            );

        if ($loginUser === null) {
            login_json_response([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $_SESSION['current_user'] = $loginUser;

        login_json_response([
            'success' => true,
            'role' => $loginUser['role'],
            'redirect' => app_url(dashboard_path_for($loginUser)),
        ]);
        break;

    case '/api/forgot-password':
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            login_json_response(['message' => 'Method not allowed.'], 405);
        }

        login_json_response(['success' => true]);
        break;

    case '/api/social-login':
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            login_json_response(['message' => 'Method not allowed.'], 405);
        }

        $payload = login_request_data();
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $loginUser = null;

        foreach ($loginUsers as $candidate) {
            if (strtolower((string) ($candidate['email'] ?? '')) === $email) {
                $loginUser = $candidate;
                break;
            }
        }

        if ($loginUser === null) {
            login_json_response([
                'success' => false,
                'message' => 'No matching account found for that social login.',
            ], 401);
        }

        $_SESSION['current_user'] = $loginUser;

        login_json_response([
            'success' => true,
            'role' => $loginUser['role'],
            'redirect' => app_url(dashboard_path_for($loginUser)),
        ]);
        break;

    case '/api/qr-code':
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            login_json_response(['error' => 'Method not allowed.'], 405);
        }

        $payload = login_request_data();
        $text = trim((string) ($payload['text'] ?? ''));

        if ($text === '') {
            login_json_response(['error' => 'QR text is required.'], 422);
        }

        login_json_response([
            'imageUrl' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . rawurlencode($text),
        ]);
        break;

    case '/api/admin/settings':
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'GET') {
            login_json_response(admin_settings_read());
        }

        if ($method !== 'PUT') {
            login_json_response(['error' => 'Method not allowed.'], 405);
        }

        $payload = login_request_data();
        $sessionTimeout = (int) ($payload['session_timeout'] ?? 0);
        $retentionDays = (int) ($payload['data_retention_days'] ?? 0);

        if ($sessionTimeout <= 0 || $retentionDays <= 0) {
            login_json_response(['error' => 'Values must be positive integers.'], 400);
        }

        admin_settings_write([
            'session_timeout' => $sessionTimeout,
            'data_retention_days' => $retentionDays,
        ]);

        login_json_response([
            'success' => true,
            'message' => 'Settings saved.',
        ]);
        break;

    case '/api/admin/data-retention/purge':
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            login_json_response(['error' => 'Method not allowed.'], 405);
        }

        $settings = admin_settings_read();
        $retentionDays = (int) ($settings['data_retention_days'] ?? 90);
        $purgeLog = dirname(__DIR__) . '/storage/purge_log.txt';
        file_put_contents(
            $purgeLog,
            date('Y-m-d H:i:s') . " - Purged records older than {$retentionDays} days\n",
            FILE_APPEND
        );

        login_json_response([
            'success' => true,
            'message' => "Purged records older than {$retentionDays} days.",
        ]);
        break;

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    case '/admin-dashboard/users':
    require_once __DIR__ . '/../src/helpers/user-helper.php';
    // ❌ REMOVE THIS LINE: require_once __DIR__ . '/../src/controllers/UserController.php';
    
    $title = 'User Management';
    $user = get_logged_in_user();
    $isAdminDashboard = true;
    
    // Fetch real users from API
    $apiUsers = fetch_users_from_api();
    $allUsers = array_map('transform_user', $apiUsers);
    
    // Search filtering
    $query = $_GET['q'] ?? '';
    if ($query !== '') {
        $queryLower = strtolower($query);
        $filtered = array_filter($allUsers, function($u) use ($queryLower) {
            return str_contains(strtolower($u['name']), $queryLower)
                || str_contains(strtolower($u['email']), $queryLower)
                || str_contains(strtolower($u['employeeId']), $queryLower);
        });
    } else {
        $filtered = $allUsers;
    }

    view('admin/usermanagement', compact(
        'title',
        'user',
        'stats',
        'isAdminDashboard',
        'filtered',
        'query'
    ));
    break;

    case '/admin-dashboard/users/clear-password':
        unset($_SESSION['generated_password']);
        redirect_to('/admin-dashboard/users');
        break;

    case '/admin-dashboard':
        $title = 'Admin Dashboard';
        $user = current_user_or($adminUser, 'admin');
        $isAdminDashboard = true;

        view('admin/admin-dashboard', compact(
            'title',
            'user',
            'stats',
            'isAdminDashboard'
        ));
        break;

    case '/admin-dashboard/attendance':
        $title = 'Attendance Logs';
        $user = current_user_or($adminUser, 'admin');
        $isAdminDashboard = true;

        view('admin/attendance_log', compact(
            'title',
            'user',
            'stats',
            'isAdminDashboard'
        ));
        break;

    case '/admin-dashboard/settings':
        $title = 'Admin Settings';
        $user = current_user_or($adminUser, 'admin');
        $isAdminDashboard = true;

        view('admin/admin_settings', compact(
            'title',
            'user',
            'isAdminDashboard'
        ));
        break;

    case '/admin-dashboard/qr-generator':
        $title = 'QR Generator';
        $user = current_user_or($adminUser, 'admin');
        $isAdminDashboard = true;

        view('admin/qr_code_generator', compact(
            'title',
            'user',
            'stats',
            'isAdminDashboard'
        ));
        break;

    // case '/admin-dashboard/testing':
    //     $title = 'Testing';
    //     $user = $adminUser;

    //     view('admin/testing', compact(
    //         'title',
    //         'user'
    //     ));
    //     break;

    /*
    |--------------------------------------------------------------------------
    | STAFF
    |--------------------------------------------------------------------------
    */

    case '/staff-dashboard':
        $title = 'Staff Dashboard';
        $user = current_user_or($staffUser, 'staff');
        $isAdminDashboard = false;

        view('staff/staff-dashboard', compact(
            'title',
            'user',
            'isAdminDashboard'
        ));
        break;

    case '/scan-qr':
        $title = 'Scan QR';
        $user = current_user_or($staffUser, 'staff');
        $isAdminDashboard = false;

        view('staff/scanqrpage', compact(
            'title',
            'user',
            'isAdminDashboard'
        ));
        break;

    case '/history':
        $title = 'Attendance History';
        $user = current_user_or($staffUser, 'staff');
        $isAdminDashboard = false;

        view('staff/AttendanceHistory', compact(
            'title',
            'user',
            'isAdminDashboard'
        ));
        break;

    case '/profile':
        $title = 'Profile';
        $user = current_user_or($staffUser, 'staff');
        $isAdminDashboard = false;

        view('staff/profile', compact(
            'title',
            'user',
            'isAdminDashboard'
        ));
        break;

    /*
    |--------------------------------------------------------------------------
    | 404
    |--------------------------------------------------------------------------
    */
    case '/api/admin/users/reset-password':
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        login_json_response(['error' => 'Method not allowed'], 405);
    }

    $password = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8);

    login_json_response([
        'success' => true,
        'password' => $password
    ]);


    default:
        http_response_code(404);
        $title = '404 Not Found';
        view('404', compact('title'));
        break;
}