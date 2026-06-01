<?php
require_once 'db.php';
checkAdminRole();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Use index-friendly date range for today
    $todayStart = date('Y-m-d 00:00:00');
    $tomorrowStart = date('Y-m-d 00:00:00', strtotime('tomorrow'));

    // 1. Currently Onsite: Count users whose absolute most recent record is an 'in'
    $onsiteQuery = "SELECT COUNT(*) as count FROM attendance a_latest
                    WHERE a_latest.timestamp = (
                        SELECT MAX(timestamp) FROM attendance 
                        WHERE user_id = a_latest.user_id
                    )
                    AND a_latest.type = 'in'";
    $stmt = $pdo->prepare($onsiteQuery);
    $stmt->execute();
    $currentlyOnsite = $stmt->fetch()['count'];

    // 2. Total Clocked In Today
    $clockedInQuery = "SELECT COUNT(DISTINCT user_id) as count FROM attendance WHERE type = 'in' AND timestamp >= :todayStart AND timestamp < :tomorrowStart";
    $stmt = $pdo->prepare($clockedInQuery);
    $stmt->execute([':todayStart' => $todayStart, ':tomorrowStart' => $tomorrowStart]);
    $totalClockedInToday = $stmt->fetch()['count'];

    // 3. Total Events Today
    $eventsQuery = "SELECT COUNT(*) as count FROM attendance WHERE timestamp >= :todayStart AND timestamp < :tomorrowStart";
    $stmt = $pdo->prepare($eventsQuery);
    $stmt->execute([':todayStart' => $todayStart, ':tomorrowStart' => $tomorrowStart]);
    $totalEventsToday = $stmt->fetch()['count'];

    echo json_encode([
        'success' => true,
        'data' => [
            'currentlyOnsite' => (int)$currentlyOnsite,
            'totalClockedInToday' => (int)$totalClockedInToday,
            'pendingSync' => 0, // As per requirement, always 0
            'totalEventsToday' => (int)$totalEventsToday
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}