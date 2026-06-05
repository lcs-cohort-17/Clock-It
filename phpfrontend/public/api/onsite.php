<?php

declare(strict_types=1);

use ClockIt\Data\AttendanceRepository;

require dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$repository = new AttendanceRepository();
$staff = isset($_GET['empty']) ? [] : $repository->onsiteStaff();

echo json_encode([
    'data' => $staff,
    'meta' => [
        'count' => count($staff),
        'source' => 'mock',
        'generatedAt' => date(DATE_ATOM),
    ],
], JSON_THROW_ON_ERROR);
