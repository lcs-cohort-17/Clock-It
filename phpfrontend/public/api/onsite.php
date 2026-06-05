<?php

declare(strict_types=1);

use ClockIt\Data\AttendanceRepository;

require dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$db = \Config\Database::getInstance()->getConnection();
$stmt = $db->query("
    SELECT u.user_id, u.first_name, u.last_name, u.employee_id, u.role, s.clock_in_time
    FROM sessions s
    JOIN users u ON u.user_id = s.profile_id
    WHERE s.clock_out_time IS NULL
");
$onsite = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

$staff = [];
foreach ($onsite as $row) {
    $staff[] = [
        'name' => ($row['first_name'] ?? '') . ($row['last_name'] ? ' ' . $row['last_name'] : ''),
        'role' => $row['role'] ?? 'staff',
        'signedInAt' => date('H:i', strtotime($row['clock_in_time'])),
        'employeeId' => $row['employee_id'] ?? ''
    ];
}

echo json_encode([
    'data' => $staff,
    'meta' => [
        'count' => count($staff),
        'source' => 'live',
        'generatedAt' => date(DATE_ATOM),
    ],
], JSON_THROW_ON_ERROR);
