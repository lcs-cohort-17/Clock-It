<?php
 
function handleSettingsRoutes(SettingsController $controller, array $request = []): void
{
    $method = $request['method'] ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $uri    = $request['uri']    ?? (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
    $auth   = $GLOBALS['auth']   ?? [];
    $body   = $request['body']   ?? settingsReadJsonBody();
 
    // GET /api/admin/settings — fetch current settings
    if ($method === 'GET' && $uri === '/api/admin/settings') {
        $controller->getSettings($auth);
        return;
    }
 
    // PUT /api/admin/settings — update one or both settings
    if ($method === 'PUT' && $uri === '/api/admin/settings') {
        $controller->updateSettings($auth, $body);
        return;
    }
 
    // POST /api/admin/data-retention/purge — purge old attendance records
    if ($method === 'POST' && $uri === '/api/admin/data-retention/purge') {
        $controller->purge($auth, $body);
        return;
    }
 
    http_response_code(404);
    echo json_encode(['message' => 'Not Found'], JSON_UNESCAPED_SLASHES);
}
 
function settingsReadJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
 
    $decoded = json_decode($raw, true);
 
    return is_array($decoded) ? $decoded : [];
}