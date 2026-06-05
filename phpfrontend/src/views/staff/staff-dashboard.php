<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Data/data.php';

$currentUser = is_array($user ?? null) ? $user : current_user();
$loggedInEmployeeId = $currentUser['employeeId'] ?? null;
$attendanceHistory = [];

if (is_string($loggedInEmployeeId) && $loggedInEmployeeId !== '') {
    $attendanceHistory = array_values(array_filter(
        $mockAttendanceData,
        static fn (array $record): bool => ($record['employeeId'] ?? null) === $loggedInEmployeeId
    ));
}

// Query real session records from the SQLite database
try {
    require_once dirname(__DIR__, 4) . '/phpbackend/src/config/Database.php';
    $db = \Config\Database::getInstance()->getConnection();
    
    // Find the user's user_id from their employee_id or email
    $userQuery = $db->prepare("SELECT user_id, first_name, last_name, employee_id, role FROM users WHERE employee_id = :empId OR email = :email");
    $userQuery->execute([
        ':empId' => $currentUser['employeeId'] ?? '',
        ':email' => $currentUser['email'] ?? ''
    ]);
    $dbUser = $userQuery->fetch(\PDO::FETCH_ASSOC);
    
    if ($dbUser) {
        $profileId = $dbUser['user_id'];
        $fullName = trim(($dbUser['first_name'] ?? '') . ' ' . ($dbUser['last_name'] ?? '')) ?: $dbUser['email'];
        
        $sessionQuery = $db->prepare("
            SELECT * 
            FROM sessions 
            WHERE profile_id = :profileId 
            ORDER BY clock_in_time DESC
        ");
        $sessionQuery->execute([':profileId' => $profileId]);
        $dbSessions = $sessionQuery->fetchAll(\PDO::FETCH_ASSOC);
        
        $dbAttendance = [];
        foreach ($dbSessions as $session) {
            $clockIn = new \DateTime($session['clock_in_time']);
            $clockOut = !empty($session['clock_out_time']) ? new \DateTime($session['clock_out_time']) : null;
            
            $dateStr = $clockIn->format('Y-m-d');
            $inTime = $clockIn->format('H:i');
            $outTime = $clockOut ? $clockOut->format('H:i') : '--:--';
            
            // Calculate working hours
            $hours = 0.0;
            if ($clockOut) {
                $diff = $clockIn->diff($clockOut);
                $hours = round($diff->h + ($diff->i / 60) + ($diff->s / 3600), 1);
            }
            
            // Determine status
            $status = 'Present';
            if ((int)$clockIn->format('H') >= 9 && (int)$clockIn->format('i') > 0) {
                $status = 'Late';
            } elseif ($clockOut && $hours < 5.0) {
                $status = 'Half Day';
            }
            
            $dbAttendance[] = [
                'id' => 'DB-ATT-' . $session['id'],
                'employeeName' => $fullName,
                'employeeId' => $dbUser['employee_id'],
                'date' => $dateStr,
                'checkInTime' => $inTime,
                'checkOutTime' => $outTime,
                'status' => $status,
                'workingHours' => $hours,
                'department' => 'Staff'
            ];
        }
        
        $attendanceHistory = array_merge($dbAttendance, $attendanceHistory);
    }
} catch (\Exception $e) {
    error_log("Failed to load real sessions for dashboard: " . $e->getMessage());
}

ob_start();
?>

<script>
    window.ATTENDANCE_DATA = {
        history: <?= json_encode($attendanceHistory, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        currentEmployeeId: <?= json_encode($loggedInEmployeeId) ?>,
        employeeName: <?= json_encode($currentUser['name'] ?? '') ?>
    };
</script>

<div class="app-shell">
    <?php require __DIR__ . '/../partials/staff_sidebar.php'; ?>

    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content">
            <?php require __DIR__ . '/../partials/DashboardGrid.php'; ?>
        </main>
    </div>
</div>

<?php
$content = ob_get_clean();

require __DIR__ . '/../layouts/app.php';
