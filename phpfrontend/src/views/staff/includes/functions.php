<?php
/**
 * Helper functions for the attendance system
 */

function getStatusBadgeClass($status) {
    $classes = [
        'Present' => 'bg-emerald-100 text-emerald-800',
        'Absent' => 'bg-red-100 text-red-800',
        'Late' => 'bg-amber-100 text-amber-800',
        'Half Day' => 'bg-indigo-100 text-indigo-800',
        'Holiday' => 'bg-purple-100 text-purple-800'
    ];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}

function getStatusColor($status) {
    $colors = [
        'Present' => '#10b981',
        'Absent' => '#ef4444',
        'Late' => '#f59e0b',
        'Half Day' => '#6366f1',
        'Holiday' => '#8b5cf6'
    ];
    return $colors[$status] ?? '#6b7280';
}

function formatDate($dateStr, $format = 'M d, Y') {
    $timestamp = strtotime($dateStr);
    return date($format, $timestamp);
}

function formatTime($time) {
    if ($time === '--:--' || empty($time)) {
        return '—';
    }
    return $time;
}

function getEmployeeList() {
    return [
        ['id' => 'EMP001', 'name' => 'Shaheed Karlie', 'dept' => 'Engineering', 'avatar' => 'SK'],
        ['id' => 'EMP002', 'name' => 'Imaan Cummings', 'dept' => 'Sales', 'avatar' => 'IC'],
        ['id' => 'EMP003', 'name' => 'Will Mxbanisi', 'dept' => 'Marketing', 'avatar' => 'WM'],
        ['id' => 'EMP004', 'name' => 'Joshua Jacobs', 'dept' => 'Finance', 'avatar' => 'JJ'],
        ['id' => 'EMP005', 'name' => 'Mujahid Ariefdien', 'dept' => 'Engineering', 'avatar' => 'MA'],
        ['id' => 'EMP006', 'name' => 'Nina Lewis', 'dept' => 'Sales', 'avatar' => 'NL'],
        ['id' => 'EMP007', 'name' => 'Ebrahim Easton', 'dept' => 'Marketing', 'avatar' => 'EE'],
        ['id' => 'EMP008', 'name' => 'Taaraa Haron', 'dept' => 'Accounting', 'avatar' => 'TH'],
        ['id' => 'EMP009', 'name' => 'Natheefah Rayners', 'dept' => 'Engineering', 'avatar' => 'NR'],
        ['id' => 'EMP010', 'name' => 'Keanu Visagie', 'dept' => 'Sales', 'avatar' => 'KV'],
        ['id' => 'EMP011', 'name' => 'Qaasim Davids', 'dept' => 'Marketing', 'avatar' => 'QD'],
        ['id' => 'EMP012', 'name' => 'Aiden Damon', 'dept' => 'Accounting', 'avatar' => 'AD']
    ];
}

// Get logged in employee
function getLoggedInEmployee() {
    if (isset($_SESSION['logged_in_employee_id'])) {
        $employees = getEmployeeList();
        foreach ($employees as $emp) {
            if ($emp['id'] === $_SESSION['logged_in_employee_id']) {
                return $emp;
            }
        }
    }
    return null;
}

// Set logged in employee
function setLoggedInEmployee($employeeId) {
    $_SESSION['logged_in_employee_id'] = $employeeId;
}

// Clear logged in employee
function logoutEmployee() {
    unset($_SESSION['logged_in_employee_id']);
    session_destroy();
}
?>