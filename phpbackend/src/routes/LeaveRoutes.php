<?php

function handleLeaveRoutes(LeaveController $controller, array $request = []): void
{
    $method = $request['method'] ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $uri = $request['uri'] ?? (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
    $auth = $GLOBALS['auth'] ?? [];
    $body = $request['body'] ?? leaveReadJsonBody();
    $query = $request['query'] ?? ($_GET ?? []);

    //submit requests from users 
    if ($method === 'POST' && $uri === '/api/leave-request') {
        error_log('BODY: ' . print_r($body, true));
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

    if ($method === 'PUT' && preg_match('#^/api/admin/leave-requests/([^/]+)/updateRequest$#', $uri, $matches) === 1) {
        $controller->updateLeave($auth, $matches[1], $body);
        return;
    }

    http_response_code(404);
    echo json_encode(['message' => 'Not Found'], JSON_UNESCAPED_SLASHES);
}