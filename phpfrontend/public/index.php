<?php
declare(strict_types=1);

$sessionPath = dirname(__DIR__) . '/storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

if (is_dir($sessionPath) && is_writable($sessionPath)) {
    session_save_path($sessionPath);
}

session_start();

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$baseUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$baseUrl = $baseUrl === '' ? '' : $baseUrl;

if (str_starts_with($requestPath, $scriptName)) {
    $path = substr($requestPath, strlen($scriptName)) ?: '/';
} elseif ($baseUrl !== '' && str_starts_with($requestPath, $baseUrl)) {
    $path = substr($requestPath, strlen($baseUrl)) ?: '/';
} else {
    $path = $requestPath;
}

$path = '/' . trim($path, '/');
$path = $path === '/' ? '/' : rtrim($path, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$brand = [
    'name' => 'Clock-It',
    'tagline' => 'Workforce intelligence',
    'support' => 'support@clock-it.local',
];

$today = date('Y-m-d');
$nowIso = date('c');

$users = [
    [
        'id' => 'usr-admin-001',
        'employeeId' => 'ADM-001',
        'name' => 'Ayanda Maseko',
        'email' => 'ayanda.maseko@clock-it.local',
        'department' => 'People Operations',
        'jobTitle' => 'HR Systems Lead',
        'role' => 'Admin',
        'accountRole' => 'admin',
        'status' => 'Active',
        'phone' => '+27 82 410 0198',
        'location' => 'Johannesburg HQ',
        'startDate' => '2021-04-12',
        'manager' => 'Executive Office',
        'avatar' => 'AM',
    ],
    [
        'id' => 'usr-staff-108',
        'employeeId' => 'EMP-108',
        'name' => 'Tentsaolo Khoza',
        'email' => 'tentsaolo.khoza@clock-it.local',
        'department' => 'People Operations',
        'jobTitle' => 'HR Coordinator',
        'role' => 'Staff',
        'accountRole' => 'staff',
        'status' => 'Active',
        'phone' => '+27 71 248 7704',
        'location' => 'Johannesburg HQ',
        'startDate' => '2023-01-16',
        'manager' => 'Ayanda Maseko',
        'avatar' => 'TK',
    ],
    [
        'id' => 'usr-staff-102',
        'employeeId' => 'EMP-102',
        'name' => 'Naledi Radebe',
        'email' => 'naledi.radebe@clock-it.local',
        'department' => 'Finance',
        'jobTitle' => 'Payroll Analyst',
        'role' => 'Staff',
        'accountRole' => 'staff',
        'status' => 'Active',
        'phone' => '+27 82 901 2841',
        'location' => 'Cape Town Studio',
        'startDate' => '2022-07-01',
        'manager' => 'Musa Nkosi',
        'avatar' => 'NR',
    ],
    [
        'id' => 'usr-staff-103',
        'employeeId' => 'EMP-103',
        'name' => 'Musa Nkosi',
        'email' => 'musa.nkosi@clock-it.local',
        'department' => 'Engineering',
        'jobTitle' => 'Platform Engineer',
        'role' => 'Staff',
        'accountRole' => 'staff',
        'status' => 'Active',
        'phone' => '+27 73 552 4401',
        'location' => 'Johannesburg HQ',
        'startDate' => '2020-11-23',
        'manager' => 'Ayanda Maseko',
        'avatar' => 'MN',
    ],
    [
        'id' => 'usr-staff-104',
        'employeeId' => 'EMP-104',
        'name' => 'Priya Naidoo',
        'email' => 'priya.naidoo@clock-it.local',
        'department' => 'Customer Success',
        'jobTitle' => 'Success Manager',
        'role' => 'Staff',
        'accountRole' => 'staff',
        'status' => 'Active',
        'phone' => '+27 76 018 9840',
        'location' => 'Durban Hub',
        'startDate' => '2021-08-09',
        'manager' => 'Thabo Dube',
        'avatar' => 'PN',
    ],
    [
        'id' => 'usr-staff-105',
        'employeeId' => 'EMP-105',
        'name' => 'Thabo Dube',
        'email' => 'thabo.dube@clock-it.local',
        'department' => 'Sales',
        'jobTitle' => 'Regional Sales Lead',
        'role' => 'Staff',
        'accountRole' => 'staff',
        'status' => 'Disabled',
        'phone' => '+27 83 477 2170',
        'location' => 'Pretoria Office',
        'startDate' => '2019-05-20',
        'manager' => 'Ayanda Maseko',
        'avatar' => 'TD',
    ],
];

$attendanceRecords = [
    [
        'id' => 'ATT-260602-001',
        'employeeId' => 'EMP-108',
        'employeeName' => 'Tentsaolo Khoza',
        'department' => 'People Operations',
        'role' => 'Staff',
        'type' => 'Global Clock In',
        'timestamp' => $today . 'T08:05:00+02:00',
        'device' => 'Mobile',
        'qrUsed' => 'QR-GCI-260602-A1',
        'status' => 'Clock Out Pending',
        'syncStatus' => 'Synced',
        'createdAt' => $today . 'T08:05:09+02:00',
        'updatedAt' => $today . 'T08:05:09+02:00',
        'notes' => 'Atrium QR accepted. Clock out is still pending.',
        'auditTrail' => [
            ['at' => $today . 'T08:05:09+02:00', 'actor' => 'System', 'event' => 'Clock in captured from mobile QR scan.'],
        ],
    ],
    [
        'id' => 'ATT-260602-002',
        'employeeId' => 'EMP-102',
        'employeeName' => 'Naledi Radebe',
        'department' => 'Finance',
        'role' => 'Staff',
        'type' => 'Global Clock In',
        'timestamp' => $today . 'T07:52:00+02:00',
        'device' => 'Desktop',
        'qrUsed' => 'QR-GCI-260602-A1',
        'status' => 'Verified',
        'syncStatus' => 'Synced',
        'createdAt' => $today . 'T07:52:12+02:00',
        'updatedAt' => $today . 'T07:52:12+02:00',
        'notes' => 'Finance floor turnstile.',
        'auditTrail' => [
            ['at' => $today . 'T07:52:12+02:00', 'actor' => 'System', 'event' => 'Clock in verified.'],
        ],
    ],
    [
        'id' => 'ATT-260602-003',
        'employeeId' => 'EMP-102',
        'employeeName' => 'Naledi Radebe',
        'department' => 'Finance',
        'role' => 'Staff',
        'type' => 'Global Clock Out',
        'timestamp' => $today . 'T16:32:00+02:00',
        'device' => 'Desktop',
        'qrUsed' => 'QR-GCO-260602-B1',
        'status' => 'Verified',
        'syncStatus' => 'Synced',
        'createdAt' => $today . 'T16:32:07+02:00',
        'updatedAt' => $today . 'T16:32:07+02:00',
        'notes' => 'Standard clock out.',
        'auditTrail' => [
            ['at' => $today . 'T16:32:07+02:00', 'actor' => 'System', 'event' => 'Clock out verified.'],
        ],
    ],
    [
        'id' => 'ATT-260602-004',
        'employeeId' => 'EMP-103',
        'employeeName' => 'Musa Nkosi',
        'department' => 'Engineering',
        'role' => 'Staff',
        'type' => 'Global Clock In',
        'timestamp' => $today . 'T08:11:00+02:00',
        'device' => 'Tablet',
        'qrUsed' => 'QR-GCI-260602-A1',
        'status' => 'Verified',
        'syncStatus' => 'Synced',
        'createdAt' => $today . 'T08:11:22+02:00',
        'updatedAt' => $today . 'T08:11:22+02:00',
        'notes' => 'Engineering lab kiosk.',
        'auditTrail' => [
            ['at' => $today . 'T08:11:22+02:00', 'actor' => 'System', 'event' => 'Clock in verified.'],
        ],
    ],
    [
        'id' => 'ATT-260602-005',
        'employeeId' => 'EMP-103',
        'employeeName' => 'Musa Nkosi',
        'department' => 'Engineering',
        'role' => 'Staff',
        'type' => 'Global Clock Out',
        'timestamp' => $today . 'T15:48:00+02:00',
        'device' => 'Tablet',
        'qrUsed' => 'QR-GCO-260602-B1',
        'status' => 'Pending Review',
        'syncStatus' => 'Pending',
        'createdAt' => $today . 'T15:48:19+02:00',
        'updatedAt' => $today . 'T15:48:19+02:00',
        'notes' => 'Offline kiosk submitted after network recovery.',
        'auditTrail' => [
            ['at' => $today . 'T15:48:19+02:00', 'actor' => 'System', 'event' => 'Clock out captured while device was offline.'],
        ],
    ],
    [
        'id' => 'ATT-260602-006',
        'employeeId' => 'EMP-104',
        'employeeName' => 'Priya Naidoo',
        'department' => 'Customer Success',
        'role' => 'Staff',
        'type' => 'Global Clock In',
        'timestamp' => $today . 'T09:04:00+02:00',
        'device' => 'Mobile',
        'qrUsed' => 'QR-GCI-260602-A1',
        'status' => 'Verified',
        'syncStatus' => 'Synced',
        'createdAt' => $today . 'T09:04:05+02:00',
        'updatedAt' => $today . 'T09:04:05+02:00',
        'notes' => 'Durban reception scan.',
        'auditTrail' => [
            ['at' => $today . 'T09:04:05+02:00', 'actor' => 'System', 'event' => 'Clock in verified.'],
        ],
    ],
    [
        'id' => 'ATT-260601-001',
        'employeeId' => 'EMP-108',
        'employeeName' => 'Tentsaolo Khoza',
        'department' => 'People Operations',
        'role' => 'Staff',
        'type' => 'Global Clock In',
        'timestamp' => date('Y-m-d', strtotime($today . ' -1 day')) . 'T08:02:00+02:00',
        'device' => 'Mobile',
        'qrUsed' => 'QR-GCI-260601-A1',
        'status' => 'Verified',
        'syncStatus' => 'Synced',
        'createdAt' => date('Y-m-d', strtotime($today . ' -1 day')) . 'T08:02:04+02:00',
        'updatedAt' => date('Y-m-d', strtotime($today . ' -1 day')) . 'T08:02:04+02:00',
        'notes' => 'Routine start.',
        'auditTrail' => [
            ['at' => date('Y-m-d', strtotime($today . ' -1 day')) . 'T08:02:04+02:00', 'actor' => 'System', 'event' => 'Clock in verified.'],
        ],
    ],
    [
        'id' => 'ATT-260601-002',
        'employeeId' => 'EMP-108',
        'employeeName' => 'Tentsaolo Khoza',
        'department' => 'People Operations',
        'role' => 'Staff',
        'type' => 'Global Clock Out',
        'timestamp' => date('Y-m-d', strtotime($today . ' -1 day')) . 'T16:44:00+02:00',
        'device' => 'Mobile',
        'qrUsed' => 'QR-GCO-260601-B1',
        'status' => 'Verified',
        'syncStatus' => 'Synced',
        'createdAt' => date('Y-m-d', strtotime($today . ' -1 day')) . 'T16:44:10+02:00',
        'updatedAt' => date('Y-m-d', strtotime($today . ' -1 day')) . 'T16:44:10+02:00',
        'notes' => 'Routine finish.',
        'auditTrail' => [
            ['at' => date('Y-m-d', strtotime($today . ' -1 day')) . 'T16:44:10+02:00', 'actor' => 'System', 'event' => 'Clock out verified.'],
        ],
    ],
];

$qrTokens = [
    'clockIn' => [
        'type' => 'Global Clock In',
        'token' => 'GCI-' . date('ymd') . '-AX9K',
        'status' => 'Active',
        'generatedAt' => $today . 'T06:00:00+02:00',
        'expiresAt' => $today . 'T23:59:59+02:00',
        'createdBy' => 'Ayanda Maseko',
        'usageCount' => 4,
    ],
    'clockOut' => [
        'type' => 'Global Clock Out',
        'token' => 'GCO-' . date('ymd') . '-Q4M2',
        'status' => 'Active',
        'generatedAt' => $today . 'T06:00:00+02:00',
        'expiresAt' => $today . 'T23:59:59+02:00',
        'createdBy' => 'Ayanda Maseko',
        'usageCount' => 2,
    ],
];

$calendarData = [
    'holidays' => [
        ['date' => $today, 'name' => 'Operations Review Day'],
        ['date' => date('Y-m-d', strtotime($today . ' +14 days')), 'name' => 'Youth Day Observed'],
    ],
    'leave' => [
        ['employeeId' => 'EMP-104', 'date' => date('Y-m-d', strtotime($today . ' +3 days')), 'type' => 'Annual Leave'],
        ['employeeId' => 'EMP-108', 'date' => date('Y-m-d', strtotime($today . ' +8 days')), 'type' => 'Personal Leave'],
    ],
    'schedule' => [
        ['employeeId' => 'EMP-108', 'date' => $today, 'shift' => '08:00 - 17:00', 'location' => 'Johannesburg HQ'],
        ['employeeId' => 'EMP-108', 'date' => date('Y-m-d', strtotime($today . ' +1 day')), 'shift' => '08:00 - 17:00', 'location' => 'Johannesburg HQ'],
        ['employeeId' => 'EMP-102', 'date' => $today, 'shift' => '07:30 - 16:30', 'location' => 'Cape Town Studio'],
        ['employeeId' => 'EMP-103', 'date' => $today, 'shift' => '08:00 - 16:00', 'location' => 'Johannesburg HQ'],
    ],
];

$settings = [
    'company' => [
        'name' => 'Clock-It Labs',
        'timezone' => 'Africa/Johannesburg',
        'weekStart' => 'Monday',
        'primaryLocation' => 'Johannesburg HQ',
    ],
    'attendanceRules' => [
        'graceMinutes' => 10,
        'autoClockOutHours' => 12,
        'requireQr' => true,
        'reviewOfflineScans' => true,
    ],
    'session' => [
        'idleTimeout' => 30,
        'rememberDeviceDays' => 14,
        'enforceMfaPlaceholder' => false,
    ],
    'retention' => [
        'attendanceMonths' => 36,
        'auditMonths' => 60,
        'exportWindowDays' => 31,
    ],
    'integrations' => [
        ['name' => 'Payroll Bridge', 'status' => 'Planned'],
        ['name' => 'Slack Alerts', 'status' => 'Planned'],
        ['name' => 'Biometric Devices', 'status' => 'Planned'],
    ],
];

function route_url(string $path = '/'): string
{
    $path = '/' . trim($path, '/');
    $path = $path === '/' ? '/' : $path;

    return ($GLOBALS['baseUrl'] ?? '') . '/index.php' . ($path === '/' ? '' : $path);
}

function asset_url(string $path): string
{
    return ($GLOBALS['baseUrl'] ?? '') . '/assets/' . ltrim($path, '/');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect_to(string $route): never
{
    header('Location: ' . route_url($route));
    exit;
}

function login_as(array $user): void
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['accountRole'];
}

function find_user_by_role(array $users, string $role): array
{
    foreach ($users as $user) {
        if (($user['accountRole'] ?? '') === $role) {
            return $user;
        }
    }

    return $users[0];
}

function ui_icon(string $name, string $class = ''): string
{
    $icons = [
        'grid' => '<path d="M4.75 4.75h6.5v6.5h-6.5z"></path><path d="M14.75 4.75h4.5v6.5h-4.5z"></path><path d="M4.75 14.75h4.5v4.5h-4.5z"></path><path d="M12.75 14.75h6.5v4.5h-6.5z"></path>',
        'users' => '<path d="M16 19.25v-1.5a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v1.5"></path><circle cx="10" cy="8" r="3.25"></circle><path d="M20 19.25v-1.2a2.5 2.5 0 0 0-1.7-2.37"></path><path d="M16.75 5.1a3.1 3.1 0 0 1 0 5.8"></path>',
        'clock' => '<circle cx="12" cy="12" r="8.25"></circle><path d="M12 7.75v4.6l3.2 1.9"></path>',
        'qr' => '<path d="M4.75 4.75h5.5v5.5h-5.5z"></path><path d="M14.25 4.75h5.5v5.5h-5.5z"></path><path d="M4.75 14.25h5.5v5.5h-5.5z"></path><path d="M14.25 14.25h1.5v1.5h-1.5z"></path><path d="M18.25 14.25h1.5v4.75h-4.75"></path><path d="M14.25 18.25h1.5"></path>',
        'calendar' => '<path d="M6.75 3.75v3"></path><path d="M17.25 3.75v3"></path><path d="M4.75 8.25h14.5"></path><path d="M5.75 5.25h12.5a1.5 1.5 0 0 1 1.5 1.5v12a1.5 1.5 0 0 1-1.5 1.5H5.75a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5z"></path>',
        'settings' => '<path d="M12 8.25a3.75 3.75 0 1 1 0 7.5 3.75 3.75 0 0 1 0-7.5z"></path><path d="M19.4 13.55a7.6 7.6 0 0 0 0-3.1l1.6-1.22-1.75-3.04-1.9.77a7.7 7.7 0 0 0-2.68-1.55L14.4 3.5H10.9l-.27 1.91a7.7 7.7 0 0 0-2.68 1.55l-1.9-.77-1.75 3.04 1.6 1.22a7.6 7.6 0 0 0 0 3.1l-1.6 1.22 1.75 3.04 1.9-.77a7.7 7.7 0 0 0 2.68 1.55l.27 1.91h3.5l.27-1.91a7.7 7.7 0 0 0 2.68-1.55l1.9.77 1.75-3.04z"></path>',
        'search' => '<circle cx="10.75" cy="10.75" r="5.75"></circle><path d="m15.5 15.5 4 4"></path>',
        'download' => '<path d="M12 4.75v10"></path><path d="m8.5 11.75 3.5 3.5 3.5-3.5"></path><path d="M5.25 18.25h13.5"></path>',
        'shield' => '<path d="M12 3.75 18.75 6v5.45c0 4.08-2.64 6.92-6.75 8.8-4.11-1.88-6.75-4.72-6.75-8.8V6z"></path><path d="m9.25 12.2 1.9 1.9 3.85-4.2"></path>',
        'logout' => '<path d="M9.25 4.75H6.5a1.75 1.75 0 0 0-1.75 1.75v11a1.75 1.75 0 0 0 1.75 1.75h2.75"></path><path d="M14.25 8.25 18.75 12l-4.5 3.75"></path><path d="M18.5 12H9.75"></path>',
        'menu' => '<path d="M4.75 6.75h14.5"></path><path d="M4.75 12h14.5"></path><path d="M4.75 17.25h14.5"></path>',
        'bell' => '<path d="M17.25 9.8a5.25 5.25 0 0 0-10.5 0c0 6-2.25 6.25-2.25 6.25h15s-2.25-.25-2.25-6.25z"></path><path d="M10 19.25a2.2 2.2 0 0 0 4 0"></path>',
        'plus' => '<path d="M12 5.25v13.5"></path><path d="M5.25 12h13.5"></path>',
        'edit' => '<path d="M5.25 15.75v3h3L18.2 8.8l-3-3z"></path><path d="m13.75 7.25 3 3"></path>',
        'check' => '<path d="m5.75 12.75 4 4 8.5-9.5"></path>',
        'x' => '<path d="m6.75 6.75 10.5 10.5"></path><path d="m17.25 6.75-10.5 10.5"></path>',
        'eye' => '<path d="M3.75 12s3-5.25 8.25-5.25S20.25 12 20.25 12s-3 5.25-8.25 5.25S3.75 12 3.75 12z"></path><circle cx="12" cy="12" r="2.25"></circle>',
        'spark' => '<path d="M12 3.75 13.7 9l5.55 1.25-5.55 1.25L12 17.25 10.3 11.5 4.75 10.25 10.3 9z"></path><path d="M18.25 15.75 19 18l2.25.75L19 19.5l-.75 2.25-.75-2.25-2.25-.75 2.25-.75z"></path>',
        'phone' => '<path d="M8.25 4.75h7.5a1.5 1.5 0 0 1 1.5 1.5v11.5a1.5 1.5 0 0 1-1.5 1.5h-7.5a1.5 1.5 0 0 1-1.5-1.5V6.25a1.5 1.5 0 0 1 1.5-1.5z"></path><path d="M10.75 16.75h4.5"></path>',
        'mail' => '<path d="M4.75 6.75h14.5v10.5H4.75z"></path><path d="m5.25 7.25 6.75 5.5 6.75-5.5"></path>',
        'location' => '<path d="M18.25 10.25c0 5-6.25 10-6.25 10s-6.25-5-6.25-10a6.25 6.25 0 1 1 12.5 0z"></path><circle cx="12" cy="10.25" r="2.25"></circle>',
    ];

    $body = $icons[$name] ?? $icons['spark'];
    $classes = trim('ui-icon ' . $class);

    return '<svg class="' . e($classes) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
}

function nav_items(string $role): array
{
    if ($role === 'admin') {
        return [
            ['path' => '/admin/dashboard', 'label' => 'Dashboard', 'icon' => 'grid'],
            ['path' => '/admin/qr', 'label' => 'QR Management', 'icon' => 'qr'],
            ['path' => '/admin/attendance', 'label' => 'Attendance Logs', 'icon' => 'clock'],
            ['path' => '/admin/users', 'label' => 'Users', 'icon' => 'users'],
            ['path' => '/admin/calendar', 'label' => 'Calendar', 'icon' => 'calendar'],
            ['path' => '/admin/settings', 'label' => 'Settings', 'icon' => 'settings'],
        ];
    }

    return [
        ['path' => '/staff/dashboard', 'label' => 'Dashboard', 'icon' => 'grid'],
        ['path' => '/staff/scan-qr', 'label' => 'Scan QR', 'icon' => 'qr'],
        ['path' => '/staff/history', 'label' => 'History', 'icon' => 'clock'],
        ['path' => '/staff/calendar', 'label' => 'Calendar', 'icon' => 'calendar'],
        ['path' => '/staff/profile', 'label' => 'Profile', 'icon' => 'users'],
    ];
}

function render_view(string $view, array $data = []): void
{
    global $brand, $path, $baseUrl, $users, $attendanceRecords, $qrTokens, $calendarData, $settings, $today, $nowIso;

    $currentPath = $path;
    $currentUser = $data['user'] ?? null;
    $clientState = [
        'brand' => $brand,
        'baseUrl' => $baseUrl,
        'currentPath' => $currentPath,
        'today' => $today,
        'now' => $nowIso,
        'currentUser' => $currentUser,
        'users' => $users,
        'attendanceRecords' => $attendanceRecords,
        'qrTokens' => $qrTokens,
        'calendar' => $calendarData,
        'settings' => $settings,
    ];

    extract($data);
    ob_start();
    require __DIR__ . '/../src/views/' . $view . '.php';
    $content = ob_get_clean();

    require __DIR__ . '/../src/views/layouts/app.php';
}

$adminUser = find_user_by_role($users, 'admin');
$staffUser = $users[1];

if ($path === '/login' && $method === 'POST') {
    $identifier = strtolower(trim((string) ($_POST['identifier'] ?? '')));
    $role = str_contains($identifier, 'admin') || str_contains($identifier, 'ayanda') ? 'admin' : 'staff';
    login_as($role === 'admin' ? $adminUser : $staffUser);
    redirect_to($role === 'admin' ? '/admin/dashboard' : '/staff/dashboard');
}

if ($path === '/logout') {
    session_unset();
    redirect_to('/login');
}

switch ($path) {
    case '/':
    case '/login':
        render_view('login', [
            'title' => 'Sign in | Clock-It',
            'showShell' => false,
            'bodyClass' => 'auth-body',
        ]);
        break;

    case '/admin':
    case '/admin-dashboard':
    case '/admin/dashboard.php':
        redirect_to('/admin/dashboard');
        break;

    case '/admin/dashboard':
        login_as($adminUser);
        render_view('admin/dashboard', [
            'title' => 'Admin Dashboard | Clock-It',
            'pageTitle' => 'Command Center',
            'pageEyebrow' => 'Admin workspace',
            'user' => $adminUser,
        ]);
        break;

    case '/admin/qr':
    case '/admin-dashboard/qr-generator':
    case '/admin/qr.php':
        login_as($adminUser);
        render_view('admin/qr', [
            'title' => 'QR Management | Clock-It',
            'pageTitle' => 'QR Management',
            'pageEyebrow' => 'Global clock flows',
            'user' => $adminUser,
        ]);
        break;

    case '/admin/attendance':
    case '/admin-dashboard/attendance':
    case '/admin/attendance.php':
        login_as($adminUser);
        render_view('admin/attendance', [
            'title' => 'Attendance Logs | Clock-It',
            'pageTitle' => 'Attendance Logs',
            'pageEyebrow' => 'Review and audit',
            'user' => $adminUser,
        ]);
        break;

    case '/admin/users':
    case '/admin-dashboard/users':
    case '/admin/users.php':
        login_as($adminUser);
        render_view('admin/users', [
            'title' => 'User Management | Clock-It',
            'pageTitle' => 'User Management',
            'pageEyebrow' => 'Directory and access',
            'user' => $adminUser,
        ]);
        break;

    case '/admin/calendar':
    case '/admin/calendar.php':
        login_as($adminUser);
        render_view('admin/calendar', [
            'title' => 'Calendar | Clock-It',
            'pageTitle' => 'Workforce Calendar',
            'pageEyebrow' => 'Attendance, leave, holidays',
            'user' => $adminUser,
        ]);
        break;

    case '/admin/settings':
    case '/admin-dashboard/settings':
    case '/admin/settings.php':
        login_as($adminUser);
        render_view('admin/settings', [
            'title' => 'Settings | Clock-It',
            'pageTitle' => 'Settings',
            'pageEyebrow' => 'Policies and integrations',
            'user' => $adminUser,
        ]);
        break;

    case '/staff':
    case '/staff-dashboard':
    case '/dashboard.php':
        redirect_to('/staff/dashboard');
        break;

    case '/staff/dashboard':
        login_as($staffUser);
        render_view('staff/dashboard', [
            'title' => 'Staff Dashboard | Clock-It',
            'pageTitle' => 'My Day',
            'pageEyebrow' => 'Attendance snapshot',
            'user' => $staffUser,
        ]);
        break;

    case '/staff/scan-qr':
    case '/scan-qr':
    case '/scan-qr.php':
        login_as($staffUser);
        render_view('staff/scan-qr', [
            'title' => 'Scan QR | Clock-It',
            'pageTitle' => 'Scan QR',
            'pageEyebrow' => 'Clock in and out',
            'user' => $staffUser,
        ]);
        break;

    case '/staff/history':
    case '/history':
    case '/history.php':
        login_as($staffUser);
        render_view('staff/history', [
            'title' => 'Attendance History | Clock-It',
            'pageTitle' => 'Attendance History',
            'pageEyebrow' => 'Personal records',
            'user' => $staffUser,
        ]);
        break;

    case '/staff/calendar':
    case '/calendar':
    case '/calendar.php':
        login_as($staffUser);
        render_view('staff/calendar', [
            'title' => 'Calendar | Clock-It',
            'pageTitle' => 'My Calendar',
            'pageEyebrow' => 'Schedule and markers',
            'user' => $staffUser,
        ]);
        break;

    case '/staff/profile':
    case '/profile':
    case '/profile.php':
        login_as($staffUser);
        render_view('staff/profile', [
            'title' => 'Profile | Clock-It',
            'pageTitle' => 'Profile',
            'pageEyebrow' => 'Employee account',
            'user' => $staffUser,
        ]);
        break;

    default:
        http_response_code(404);
        render_view('404', [
            'title' => 'Not Found | Clock-It',
            'showShell' => false,
            'bodyClass' => 'auth-body',
        ]);
        break;
}
