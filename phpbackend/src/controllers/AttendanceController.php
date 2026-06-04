<?php

require_once __DIR__ . '/../services/AttendanceService.php';
require_once __DIR__ . '/../models/User.php';

class AttendanceController {
    private $service;

    public function __construct() {
        $this->service = new AttendanceService();
    }

    public function handle() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $user = new User($_SESSION['user_id']);

        $input = json_decode(file_get_contents('php://input'), true) ?: [];

        $result = $this->service->clock(
            $user,
            $input['qr_token'] ?? null,
            $input['device'] ?? $input['device_info'] ?? null,
            $input['location'] ?? null
        );

        echo json_encode($result);
    }
}