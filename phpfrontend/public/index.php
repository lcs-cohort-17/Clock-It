<?php

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($request) {

    case '/':
        require __DIR__ . '/../src/views/login.php';
        break;

    case '/password':
        require __DIR__ . '/../src/views/staff/password/password.php';
        break;

    case '/dashboard':
        require __DIR__ . '/../src/views/staff/dashboard.php';
        break;

    default:
        require __DIR__ . '/../src/views/404.php';
        break;
}