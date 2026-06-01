<?php
// router.php - handles all /api/* requests for the settings page

$requestUri = $_SERVER['REQUEST_URI'];
if (strpos($requestUri, '/api/') !== 0) {
    // Not an API request – serve the original file or 404
    return false; // let PHP built-in server serve the requested file
}

header('Content-Type: application/json');

$path = parse_url($requestUri, PHP_URL_PATH);

$settingsFile = __DIR__ . '/settings_mock.json';

function getSettings($file) {
    if (!file_exists($file)) {
        // Default values
        return ['session_timeout' => 30, 'data_retention_days' => 90];
    }
    return json_decode(file_get_contents($file), true);
}

function saveSettings($file, $data) {
    file_put_contents($file, json_encode($data));
}

if ($path === '/api/admin/settings') {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        $settings = getSettings($settingsFile);
        echo json_encode($settings);
        exit;
    }
    
    if ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['session_timeout']) || !isset($input['data_retention_days'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing session_timeout or data_retention_days']);
            exit;
        }
        $timeout = (int)$input['session_timeout'];
        $retention = (int)$input['data_retention_days'];
        if ($timeout <= 0 || $retention <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Values must be positive integers']);
            exit;
        }
        saveSettings($settingsFile, ['session_timeout' => $timeout, 'data_retention_days' => $retention]);
        echo json_encode(['success' => true, 'message' => 'Settings saved']);
        exit;
    }
    
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if ($path === '/api/admin/data-retention/purge') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    
    $settings = getSettings($settingsFile);
    $retentionDays = $settings['data_retention_days'];
    file_put_contents(__DIR__ . '/purge_log.txt', date('Y-m-d H:i:s') . " - Purged records older than $retentionDays days\n", FILE_APPEND);
    
    echo json_encode(['success' => true, 'message' => "Purged records older than $retentionDays days"]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'API endpoint not found']);
exit;