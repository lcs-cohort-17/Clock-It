<?php
// Main file to handle incoming requests and route them to the appropriate controllers.

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/GoogleSheetsModel.php';
require_once __DIR__ . '/../services/GoogleSheetsService.php';
require_once __DIR__ . '/../controllers/GoogleSheetsController.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

header('Content-Type: application/json');

if ($method === 'GET' && $path === '/api/admin/sheets/status') {
    $controller = new GoogleSheetsController();

    echo json_encode($controller->status());
    return;
}

if ($method === 'POST' && $path === '/api/admin/sheets/export') {
    $controller = new GoogleSheetsController();
    $rawBody = file_get_contents('php://input');
    $params = $_POST;

    if ($rawBody !== false && trim($rawBody) !== '') {
        $jsonParams = json_decode($rawBody, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonParams)) {
            $params = $jsonParams;
        }
    }

    try {
        echo json_encode($controller->export($params));
    } catch (InvalidArgumentException $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    } catch (Throwable $e) {
        http_response_code(500);
        error_log('Google Sheets export failed: ' . $e->getMessage());
        echo json_encode(['error' => 'Export failed']);
    }

    return;
}

ob_start();
require_once __DIR__ . '/../routes/Admin.php';
$adminOutput = ob_get_clean();

if ($adminOutput !== '') {
    echo $adminOutput;
    return;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
