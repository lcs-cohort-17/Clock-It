<?php

declare(strict_types=1);

use App\Config\Database;
use App\Models\EmailAlertDb;
use App\Services\EmailAlertService;

require __DIR__ . '/../vendor/autoload.php';

try {
    $db = new EmailAlertDb(Database::getConnection());
    $service = new EmailAlertService($db);
    $result = $service->runDailyAlerts();

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit($result['success'] ? 0 : 1);
} catch (Throwable $e) {
    fwrite(STDERR, 'Daily alert cron failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
