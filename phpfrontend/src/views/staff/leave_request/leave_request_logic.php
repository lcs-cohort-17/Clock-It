<?php
// Minimal leave request logic stub
function getMockLeaveRequests(): array {
    return [
        ['id' => 201, 'employee_name' => 'Demo Staff', 'type' => 'Annual Leave', 'start_date' => '2026-06-01', 'end_date' => '2026-06-03', 'reason' => 'Personal', 'status' => 'Pending']
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Accept POSTed JSON or form and pretend to save
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Request saved (demo)']);
    exit;
}
