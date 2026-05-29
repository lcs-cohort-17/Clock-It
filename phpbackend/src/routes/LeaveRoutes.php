<?php

function handleLeaveRoutes(LeaveController $controller): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $auth = $GLOBALS['auth'] ?? [];
    $body = leaveReadJsonBody();
    $query = $_GET ?? [];

    //submitLeave
    if ($method === 'POST' && $uri === '/api/leaves') {
        $controller->submitLeave($auth, $body);
        return;
    }
    
    //getCalendar
    if ($method === 'GET' && $uri === '/api/leaves/getCalendar') {
        $controller->getCalendar($auth, $query);
        return;
    }

    //getLeave
    if ($method === 'GET' && $uri === '/api/leaves/getLeave') {
        $controller->getLeave($auth, $query);
        return;
    }

    //updateStatus
    if ($method === 'PATCH' && preg_match('#^/api/leaves/([^/]+)/status$#', $uri, $matches) === 1) {
        $controller->updateLeaveStatus($auth, $matches[1], $body);
        return;
    }

    //updateRequest
    if ($method === 'PATCH' && preg_match('#^/api/leaves/([^/]+)/updateRequest$#', $uri, $matches) === 1) {
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
