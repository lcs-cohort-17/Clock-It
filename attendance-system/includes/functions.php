<?php
/**
 * Helper functions for the attendance system
 */

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generate a random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M d, Y') {
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Calculate working hours between two times
 */
function calculateWorkingHours($checkIn, $checkOut) {
    if ($checkIn === '--:--' || $checkOut === '--:--') {
        return 0;
    }
    
    $in = strtotime($checkIn);
    $out = strtotime($checkOut);
    $hours = ($out - $in) / 3600;
    
    return round($hours, 1);
}

/**
 * Get status badge class
 */
function getStatusBadgeClass($status) {
    $classes = [
        'Present' => 'bg-green-100 text-green-800',
        'Absent' => 'bg-red-100 text-red-800',
        'Late' => 'bg-yellow-100 text-yellow-800',
        'Half Day' => 'bg-blue-100 text-blue-800',
        'Holiday' => 'bg-purple-100 text-purple-800'
    ];
    
    return isset($classes[$status]) ? $classes[$status] : 'bg-gray-100 text-gray-800';
}

/**
 * Get status color for charts
 */
function getStatusColor($status) {
    $colors = [
        'Present' => '#10b981',
        'Absent' => '#ef4444',
        'Late' => '#f59e0b',
        'Half Day' => '#6366f1',
        'Holiday' => '#8b5cf6'
    ];
    
    return isset($colors[$status]) ? $colors[$status] : '#6b7280';
}

/**
 * Format time for display
 */
function formatTime($time) {
    if ($time === '--:--' || empty($time)) {
        return '—';
    }
    return $time;
}

/**
 * Truncate string to specified length
 */
function truncateString($string, $length = 50, $suffix = '...') {
    if (strlen($string) <= $length) {
        return $string;
    }
    return substr($string, 0, $length) . $suffix;
}

/**
 * Generate pagination links
 */
function generatePagination($currentPage, $totalPages, $url) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<div class="flex justify-center items-center gap-2">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<a href="' . $url . '&page=' . ($currentPage - 1) . '" class="px-3 py-1 border rounded hover:bg-gray-50">Previous</a>';
    } else {
        $html .= '<button class="px-3 py-1 border rounded opacity-50" disabled>Previous</button>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $html .= '<a href="' . $url . '&page=1" class="px-3 py-1 border rounded hover:bg-gray-50">1</a>';
        if ($start > 2) {
            $html .= '<span class="px-2">...</span>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $html .= '<span class="px-3 py-1 border rounded bg-blue-600 text-white">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $url . '&page=' . $i . '" class="px-3 py-1 border rounded hover:bg-gray-50">' . $i . '</a>';
        }
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<span class="px-2">...</span>';
        }
        $html .= '<a href="' . $url . '&page=' . $totalPages . '" class="px-3 py-1 border rounded hover:bg-gray-50">' . $totalPages . '</a>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $url . '&page=' . ($currentPage + 1) . '" class="px-3 py-1 border rounded hover:bg-gray-50">Next</a>';
    } else {
        $html .= '<button class="px-3 py-1 border rounded opacity-50" disabled>Next</button>';
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Log activity for audit trail
 */
function logActivity($userId, $action, $details = null) {
    $logFile = __DIR__ . '/../logs/activity.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] User: $userId | Action: $action | Details: " . ($details ?? 'N/A') . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Require authentication
 */
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Get month days for calendar
 */
function getMonthDays($year, $month) {
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

/**
 * Get first day of month index (0 = Sunday, 1 = Monday, etc.)
 */
function getFirstDayOfMonth($year, $month) {
    return date('w', strtotime("$year-$month-01"));
}

/**
 * Export data to CSV
 */
function exportToCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
    }
    
    // Add data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}

/**
 * Send email notification
 */
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . APP_NAME . " <noreply@attendancesystem.com>" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}
?>