<?php

declare(strict_types=1);

session_start();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function view(string $view, array $data = []): void
{
    extract($data);

    require __DIR__ . '/../src/views/' . $view . '.php';
}

switch ($path) {

    case '/':
        view('login', [
            'title' => 'Login | Clock-It'
        ]);
        break;

    case '/admin-dashboard/qr-generator':
        view('admin/qr_generator', [
            'title' => 'QR Generator | Clock-It'
        ]);
        break;

    case '/scan-qr':
        view('scanQRPage', [
            'title' => 'Scan QR | Clock-It'
        ]);
        break;

    default:
        http_response_code(404);

        view('404', [
            'title' => '404 Not Found'
        ]);
}