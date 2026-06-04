<?php
require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/src/config/Google.php';

$client = new Google\Client();
$client->setApplicationName($config['application_name']);
$client->setScopes([
    Google\Service\Sheets::SPREADSHEETS,
    Google\Service\Drive::DRIVE,
]);
$client->setAuthConfig($config['credentials_path']);

try {
    $token = $client->fetchAccessTokenWithAssertion();
    echo "Token fetched successfully\n";

    $service = new Google\Service\Sheets($client);

    try {
        $response = $service->spreadsheets->get('1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms');
        echo "Read access works\n";
    } catch (Exception $e) {
        echo "Read failed: " . $e->getMessage() . "\n";
    }
    $spreadsheet = new Google\Service\Sheets\Spreadsheet([
        'properties' => ['title' => 'Test Sheet']
    ]);
    $result = $service->spreadsheets->create($spreadsheet);
    echo "SUCCESS: https://docs.google.com/spreadsheets/d/" . $result->spreadsheetId . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$client->setSubject('jojoeydenver@gmail.com');