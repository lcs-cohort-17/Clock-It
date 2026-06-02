<?php

function handleLeaveRoutes(LeaveController $controller, array $request = []): void
{
    $method = $request['method'] ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $uri = $request['uri'] ?? (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
    $auth = $GLOBALS['auth'] ?? [];
    $body = $request['body'] ?? leaveReadJsonBody();
    $query = $request['query'] ?? ($_GET ?? []);

    // DEBUG
    error_log("Method: $method, URI: $uri");

    //submitLeave -for users 
    if ($method === 'POST' && $uri === '/api/leave-request') {
        $controller->submitLeave($auth, $body);
        return;
    }
    
    //get leave-requests-users receive their data for calendar
    if ($method === 'GET' && $uri === '/api/leave-requests') {
        $controller->getCalendar($auth, $query);
        return;
    }

    //getLeave-admin receives requests (admin list)
    if ($method === 'GET' && $uri === '/api/admin/leave-requests') {
        $controller->getLeave($auth, $query);
        return;
    }

    //updateStatus - allows admin to update status for a specific request
    if ($method === 'PUT' && preg_match('#^/api/admin/leave-requests/([^/]+)$#', $uri, $matches) === 1) {
        $controller->updateLeaveStatus($auth, $matches[1], $body);
        return;
    }

    //updateRequest - admin edits the leave request body
    if ($method === 'PUT' && preg_match('#^/api/admin/leave-requests/([^/]+)/updateRequest$#', $uri, $matches) === 1) {
        $controller->updateLeave($auth, $matches[1], $body);
        return;
    }

    //error path
    http_response_code(404);
    echo json_encode(['message' => 'Not Found'], JSON_UNESCAPED_SLASHES);
}

function leaveReadJsonBody(): array
{
    $rawBody = file_get_contents('php://input');
    if ($rawBody === false || trim($rawBody) === '') {
        return [];
    }

    $decoded = json_decode($rawBody, true);

    return is_array($decoded) ? $decoded : [];
}
