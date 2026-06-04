<?php

declare(strict_types=1);

function handleLeaveRoutes(LeaveController $controller, array $request = []): void
{
    $method = strtoupper((string) ($request['method'] ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET')));
    $uri    = (string) ($request['uri'] ?? (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'));
    $auth   = $GLOBALS['auth'] ?? [];
    $body   = is_array($request['body'] ?? null) ? $request['body'] : leaveReadJsonBodyFallback();
    $query  = is_array($request['query'] ?? null) ? $request['query'] : ($_GET ?? []);

    // Staff/user creates a leave request.
    // Supports both names because the merged code used singular, while REST-style calls often use plural.
    if ($method === 'POST' && in_array($uri, ['/api/leave-request', '/api/leave-requests'], true)) {
        $controller->submitLeave($auth, $body);
        return;
    }

    // User calendar/list endpoint. Admin receives all requests; staff receives only their own.
    if ($method === 'GET' && $uri === '/api/leave-requests') {
        $controller->getCalendar($auth, $query);
        return;
    }

    // Admin list endpoint.
    if ($method === 'GET' && $uri === '/api/admin/leave-requests') {
        $controller->getLeave($auth, $query);
        return;
    }

    // Admin status update. PATCH and PUT are both accepted to make Thunder Client/testing easier.
    if (in_array($method, ['PATCH', 'PUT'], true) && preg_match('#^/api/admin/leave-requests/([^/]+)$#', $uri, $matches) === 1) {
        $controller->updateLeaveStatus($auth, urldecode($matches[1]), $body);
        return;
    }

    // Admin edits request details.
    if (in_array($method, ['PATCH', 'PUT'], true) && preg_match('#^/api/admin/leave-requests/([^/]+)/updateRequest$#', $uri, $matches) === 1) {
        $controller->updateLeave($auth, urldecode($matches[1]), $body);
        return;
    }

    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Leave API route not found.',
        'path'    => $uri,
        'method'  => $method,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function leaveReadJsonBodyFallback(): array
{
    $rawBody = file_get_contents('php://input');
    if ($rawBody === false || trim($rawBody) === '') {
        return [];
    }

    $decoded = json_decode($rawBody, true);
    return is_array($decoded) ? $decoded : [];
}