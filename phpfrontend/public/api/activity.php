<?php

declare(strict_types=1);

use ClockIt\Data\AttendanceRepository;

require dirname(__DIR__, 2) . '/phpfrontend/src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$repository = new AttendanceRepository();
$activity = isset($_GET['empty']) ? [] : array_slice($repository->recentActivity(), 0, 10);

echo json_encode([
    'data' => $activity,
    'meta' => [
        'count' => count($activity),
        'limit' => 10,
        'source' => 'mock',
        'generatedAt' => date(DATE_ATOM),
    ],
], JSON_THROW_ON_ERROR);
