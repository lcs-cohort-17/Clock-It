<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/data.php';

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : null;

// Handle different API actions
switch ($method) {
    case 'GET':
        if ($action === 'dashboard') {
            getDashboardData();
        } elseif ($action === 'stats') {
            getStatistics();
        } elseif ($action === 'export') {
            exportData();
        } else {
            getAttendanceData();
        }
        break;
        
    case 'POST':
        createAttendanceRecord();
        break;
        
    case 'PUT':
        updateAttendanceRecord();
        break;
        
    case 'DELETE':
        deleteAttendanceRecord();
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

/**
 * Get all attendance data with optional filters
 */
function getAttendanceData() {
    global $mockAttendanceData;
    
    $data = $mockAttendanceData;
    
    // Apply filters if provided
    if (isset($_GET['status']) && $_GET['status'] !== 'All') {
        $status = $_GET['status'];
        $data = array_filter($data, function($record) use ($status) {
            return $record['status'] === $status;
        });
    }
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = strtolower($_GET['search']);
        $data = array_filter($data, function($record) use ($search) {
            return strpos(strtolower($record['employeeName']), $search) !== false ||
                   strpos(strtolower($record['employeeId']), $search) !== false ||
                   strpos(strtolower($record['department']), $search) !== false;
        });
    }
    
    if (isset($_GET['timeframe']) && $_GET['timeframe'] !== 'all') {
        $timeframe = $_GET['timeframe'];
        $now = new DateTime();
        
        $data = array_filter($data, function($record) use ($timeframe, $now) {
            $recordDate = new DateTime($record['date']);
            
            if ($timeframe === 'week') {
                $weekStart = clone $now;
                $weekStart->modify('monday this week');
                $weekEnd = clone $weekStart;
                $weekEnd->modify('sunday this week');
                return $recordDate >= $weekStart && $recordDate <= $weekEnd;
            } elseif ($timeframe === 'month') {
                return $recordDate->format('Y-m') === $now->format('Y-m');
            }
            
            return true;
        });
    }
    
    // Sort by date (most recent first)
    usort($data, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    // Return as array values to reindex
    echo json_encode(array_values($data));
}

/**
 * Get dashboard statistics
 */
function getDashboardData() {
    global $mockAttendanceData;
    
    // Get unique employees
    $employees = [];
    foreach ($mockAttendanceData as $record) {
        $employees[$record['employeeId']] = $record['employeeName'];
    }
    
    // Get today's date
    $today = date('Y-m-d');
    $todayRecords = array_filter($mockAttendanceData, function($record) use ($today) {
        return $record['date'] === $today;
    });
    
    $presentToday = count(array_filter($todayRecords, function($r) {
        return $r['status'] === 'Present';
    }));
    
    $lateToday = count(array_filter($todayRecords, function($r) {
        return $r['status'] === 'Late';
    }));
    
    $absentToday = count(array_filter($todayRecords, function($r) {
        return $r['status'] === 'Absent';
    }));
    
    // Get recent records (last 10)
    $recentRecords = array_slice($mockAttendanceData, 0, 10);
    
    echo json_encode([
        'stats' => [
            'totalEmployees' => count($employees),
            'presentToday' => $presentToday,
            'lateToday' => $lateToday,
            'absentToday' => $absentToday
        ],
        'recentRecords' => $recentRecords
    ]);
}

/**
 * Get statistics for charts
 */
function getStatistics() {
    global $mockAttendanceData;
    
    $statusCounts = [
        'Present' => 0,
        'Absent' => 0,
        'Late' => 0,
        'Half Day' => 0,
        'Holiday' => 0
    ];
    
    foreach ($mockAttendanceData as $record) {
        $statusCounts[$record['status']]++;
    }
    
    // Get monthly trends (last 6 months)
    $monthlyTrends = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $monthRecords = array_filter($mockAttendanceData, function($record) use ($month) {
            return substr($record['date'], 0, 7) === $month;
        });
        
        $monthlyTrends[$month] = [
            'present' => count(array_filter($monthRecords, function($r) { return $r['status'] === 'Present'; })),
            'absent' => count(array_filter($monthRecords, function($r) { return $r['status'] === 'Absent'; })),
            'late' => count(array_filter($monthRecords, function($r) { return $r['status'] === 'Late'; }))
        ];
    }
    
    echo json_encode([
        'statusCounts' => $statusCounts,
        'monthlyTrends' => $monthlyTrends,
        'totalRecords' => count($mockAttendanceData)
    ]);
}

/**
 * Export data to CSV
 */
function exportData() {
    global $mockAttendanceData;
    
    $filename = 'attendance_export_' . date('Y-m-d_His');
    exportToCSV($mockAttendanceData, $filename);
}

/**
 * Create new attendance record
 */
function createAttendanceRecord() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input data']);
        return;
    }
    
    // Validate required fields
    $required = ['employeeName', 'employeeId', 'date', 'status'];
    foreach ($required as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            return;
        }
    }
    
    // Create new record
    $newRecord = [
        'id' => 'ATT-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
        'employeeName' => $input['employeeName'],
        'employeeId' => $input['employeeId'],
        'date' => $input['date'],
        'checkInTime' => $input['checkInTime'] ?? '--:--',
        'checkOutTime' => $input['checkOutTime'] ?? '--:--',
        'status' => $input['status'],
        'workingHours' => $input['workingHours'] ?? 0,
        'department' => $input['department'] ?? 'General'
    ];
    
    // Log activity
    logActivity($_SESSION['user_id'] ?? 1, 'CREATE_ATTENDANCE', "Created record for {$newRecord['employeeName']}");
    
    http_response_code(201);
    echo json_encode(['success' => true, 'record' => $newRecord]);
}

/**
 * Update attendance record
 */
function updateAttendanceRecord() {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'] ?? null;
    
    if (!$id || !$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request']);
        return;
    }
    
    // Log activity
    logActivity($_SESSION['user_id'] ?? 1, 'UPDATE_ATTENDANCE', "Updated record ID: $id");
    
    echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
}

/**
 * Delete attendance record
 */
function deleteAttendanceRecord() {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Record ID required']);
        return;
    }
    
    // Log activity
    logActivity($_SESSION['user_id'] ?? 1, 'DELETE_ATTENDANCE', "Deleted record ID: $id");
    
    echo json_encode(['success' => true, 'message' => 'Record deleted successfully']);
}
?>