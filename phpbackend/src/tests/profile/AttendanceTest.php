<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/QrToken.php';
require_once __DIR__ . '/../src/Models/Attendance.php';
require_once __DIR__ . '/../src/Repositories/QrTokenRepository.php';
require_once __DIR__ . '/../src/Repositories/AttendanceRepository.php';
require_once __DIR__ . '/../src/Services/AttendanceService.php';

class AttendanceTest {
    private $service;

    public function __construct() {
        $this->service = new AttendanceService();
    }

    public function runAllTests() {
        echo "Starting Attendance System Tests...\n\n";
        
        $userId = 1;
        $user = new User($userId);

        $this->testValidClock($user);
        $this->testInvalidQrToken($user);
        $this->testDuplicateClockIn($user);

        echo "\nTests Completed.\n";
    }

    private function testValidClock($user) {
        echo "Test 1: Valid Clock... ";
        $result = $this->service->clock($user, "test-qr-12345", "Thunder Client", "Durban Office");
        echo (isset($result['success']) ? "SUCCESS" : "FAILED") . "\n";
    }

    private function testInvalidQrToken($user) {
        echo "Test 2: Invalid QR Token... ";
        $result = $this->service->clock($user, "invalid-token");
        echo (isset($result['error']) ? "PASSED" : "FAILED") . "\n";
    }

    private function testDuplicateClockIn($user) {
        echo "Test 3: Duplicate Clock-in... ";
        $this->service->clock($user, "test-qr-12345", "Test", "Test");
        $result = $this->service->clock($user, "test-qr-67890", "Test", "Test");
        echo (isset($result['error']) ? "PASSED" : "FAILED") . "\n";
    }
}

$test = new AttendanceTest();
$test->runAllTests();