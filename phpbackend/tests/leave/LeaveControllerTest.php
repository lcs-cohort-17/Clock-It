<?php
 
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Middleware\AuthMiddleware;
 
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/types/LeaveInterface.php';
require_once __DIR__ . '/../../src/utils/LeaveValidator.php';
require_once __DIR__ . '/../../src/models/LeaveDb.php';
require_once __DIR__ . '/../../src/controllers/LeaveController.php';
 
class LeaveControllerTest extends TestCase {
 
    /** @var LeaveRequestModel&MockObject */
    private $mockModel;
    private LeaveController $controller;
    private AuthMiddleware $authMiddleware;
 
    private function futureDate(int $days): string {
        return date('Y-m-d', strtotime("+{$days} days"));
    }

    private function futureDateTime(int $days, string $time): string {
        return date('Y-m-d', strtotime("+{$days} days")) . ' ' . $time;
    }
 
    protected function setUp(): void {
        $this->mockModel    = $this->createMock(LeaveRequestModel::class);
        $this->authMiddleware = new AuthMiddleware('test_secret_key');
        $this->controller   = new LeaveController($this->mockModel, $this->authMiddleware);
    }
 
    // Mirrors: it('returns 201 when request succeeds') in leave.controller.test.ts
    public function test_submit_leave_returns_201(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'user-1', 'is_active' => 1]);
 
        $this->mockModel->method('insert')
            ->willReturn(['id' => 'leave-1', 'status' => 'pending']);
 
        $auth = ['userId' => 'user-1', 'role' => 'staff'];
        $body = [
            'type' => 'annual',
            'start_date'   => $this->futureDate(30),
            'end_date'     => $this->futureDate(35),
            'reason'       => 'Vacation',
        ];
 
        ob_start();
        $this->controller->submitLeave($auth, $body);
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('pending', $response['data']['status']);
    }
 
    // Empty auth array simulates a request with no token / no req.auth
    public function test_submit_leave_returns_401_when_no_auth(): void {
        ob_start();
        $this->controller->submitLeave([], []);
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertEquals('Unauthorized', $response['message']);
    }
 
    // sick leave follows the same happy path as annual
    public function test_submit_leave_returns_201_for_sick_leave(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'user-1', 'is_active' => 1]);
 
        $this->mockModel->method('insert')
            ->willReturn(['id' => 'leave-2', 'status' => 'pending']);
 
        ob_start();
        $this->controller->submitLeave(
            ['userId' => 'user-1', 'role' => 'staff'],
            [
                'type' => 'sick',
                'start_date'   => $this->futureDate(5),
                'end_date'     => $this->futureDate(7),
                'reason'       => 'Flu',
            ]
        );
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('pending', $response['data']['status']);
    }

    // ticket asks for date/time support, so we keep one test that sends datetime payloads
    public function test_submit_leave_returns_201_for_datetime_payload(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'user-1', 'is_active' => 1]);

        $this->mockModel->method('insert')
            ->willReturn([
                'id' => 'leave-3',
                'status' => 'pending',
                'start_date' => $this->futureDateTime(1, '09:00'),
                'end_date' => $this->futureDateTime(1, '17:00'),
            ]);

        ob_start();
        $this->controller->submitLeave(
            ['userId' => 'user-1', 'role' => 'staff'],
            [
                'type'   => 'sick',
                'start_date' => $this->futureDateTime(1, '09:00'),
                'end_date'   => $this->futureDateTime(1, '17:00'),
                'reason'         => 'Doctor appointment',
            ]
        );
        $response = json_decode(ob_get_clean(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertEquals($this->futureDateTime(1, '09:00'), $response['data']['start_date']);
    }
 
    // findActiveUser returns null, so insert() must never be called
    public function test_submit_leave_fails_when_profile_not_found(): void {
        $this->mockModel->method('findActiveUser')->willReturn(null);
        $this->mockModel->expects($this->never())->method('insert');
 
        ob_start();
        $this->controller->submitLeave(
            ['userId' => 'ghost-user', 'role' => 'staff'],
            [
                'type' => 'annual',
                'start_date'   => $this->futureDate(30),
                'end_date'     => $this->futureDate(35),
                'reason'       => 'Vacation',
            ]
        );
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayNotHasKey('data', $response);
    }
 
    // profile exists but is_active === 0 — controller blocks before insert()
    // the is_active check lives in the controller, not the model
    public function test_submit_leave_fails_when_user_is_inactive(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'user-inactive', 'is_active' => 0]);
        $this->mockModel->expects($this->never())->method('insert');
 
        ob_start();
        $this->controller->submitLeave(
            ['userId' => 'user-inactive', 'role' => 'staff'],
            [
                'type' => 'annual',
                'start_date'   => $this->futureDate(30),
                'end_date'     => $this->futureDate(35),
                'reason'       => 'Holiday',
            ]
        );
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayNotHasKey('data', $response);
    }
 
    // Mirrors: it('allows admin approval') in leave.controller.test.ts
    // Admin role + valid leave id + valid status = 200 response
    public function test_update_status_allows_admin_approval(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'user-1', 'role' => 'admin', 'is_active' => 1]);

        $this->mockModel->method('findById')
            ->willReturn(['id' => 'leave-1']);
 
        $this->mockModel->method('updateStatus')
            ->willReturn(['id' => 'leave-1', 'status' => 'approved']);
 
        ob_start();
        $this->controller->updateLeaveStatus(
            ['userId' => 'user-1', 'role' => 'admin'],
            'leave-1',
            ['status' => 'approved']
        );
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('approved', $response['data']['status']);
    }
 
    // Mirrors the 403 Forbidden scenario — non-admin trying to update a status
    // The controller checks role BEFORE hitting the database
    // so the mock model should never be called in this case
    public function test_update_status_forbidden_for_non_admin(): void {
        ob_start();
        $this->controller->updateLeaveStatus(
            ['userId' => 'user-1', 'role' => 'staff'],
            'leave-1',
            ['status' => 'approved']
        );
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertEquals('Forbidden', $response['message']);
    }

    public function test_update_status_forbidden_when_admin_token_does_not_match_database_role(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'user-1', 'role' => 'staff', 'is_active' => 1]);

        $this->mockModel->expects($this->never())->method('updateStatus');

        ob_start();
        $this->controller->updateLeaveStatus(
            ['userId' => 'user-1', 'role' => 'admin'],
            'leave-1',
            ['status' => 'approved']
        );
        $response = json_decode(ob_get_clean(), true);

        $this->assertEquals('Forbidden', $response['message']);
    }
  
    // no auth on updateLeaveStatus must return 401 not 403
    public function test_update_status_returns_401_when_no_auth(): void {
        ob_start();
        $this->controller->updateLeaveStatus([], 'leave-1', ['status' => 'approved']);
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertEquals('Unauthorized', $response['message']);
    }
 
    // Mirrors the 404 scenario — admin sends a valid request but the leave id does not exist
    // findById returns null → controller responds with 404
    public function test_update_status_returns_404_when_not_found(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'user-1', 'role' => 'admin', 'is_active' => 1]);

        $this->mockModel->method('findById')->willReturn(null);
 
        ob_start();
        $this->controller->updateLeaveStatus(
            ['userId' => 'user-1', 'role' => 'admin'],
            'bad-id',
            ['status' => 'approved']
        );
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertEquals('Leave request not found', $response['message']);
    }
 
    // Tests the 400 validation path — invalid status value sent by admin
    // Validator catches 'accepted' before findById is ever called
    public function test_update_status_returns_400_for_invalid_status(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'user-1', 'role' => 'admin', 'is_active' => 1]);

        ob_start();
        $this->controller->updateLeaveStatus(
            ['userId' => 'user-1', 'role' => 'admin'],
            'leave-1',
            ['status' => 'accepted']
        );
        $response = json_decode(ob_get_clean(), true);

        $this->assertEquals('Validation failed', $response['message']);
        $this->assertArrayHasKey('status', $response['errors']);
    }

    // database errors should return 500 instead of crashing the request
    public function test_submit_leave_returns_500_when_model_throws(): void {
        $this->mockModel->method('findActiveUser')
            ->willThrowException(new RuntimeException('DB failed'));

        ob_start();
        $this->controller->submitLeave(
            ['userId' => 'user-1', 'role' => 'staff'],
            [
                'type' => 'annual',
                'start_date'   => $this->futureDate(30),
                'end_date'     => $this->futureDate(35),
                'reason'       => 'Vacation',
            ]
        );
        $response = json_decode(ob_get_clean(), true);

        $this->assertEquals(500, http_response_code());
        $this->assertEquals('Internal Server Error', $response['message']);
    }

    // same 500 handling for admin status updates
    public function test_update_status_returns_500_when_model_throws(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'user-1', 'role' => 'admin', 'is_active' => 1]);

        $this->mockModel->method('findById')
            ->willThrowException(new RuntimeException('DB failed'));

        ob_start();
        $this->controller->updateLeaveStatus(
            ['userId' => 'user-1', 'role' => 'admin'],
            'leave-1',
            ['status' => 'approved']
        );
        $response = json_decode(ob_get_clean(), true);

        $this->assertEquals(500, http_response_code());
        $this->assertEquals('Internal Server Error', $response['message']);
    }
 
    // Tests the calendar returns 200 with grouped data by date
    public function test_get_calendar_returns_200(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'user-1', 'role' => 'admin', 'is_active' => 1]);

        $this->mockModel->method('fetchCalendar')
            ->willReturn([
                ['id' => '1', 'start_date' => '2026-06-01', 'status' => 'approved'],
                ['id' => '2', 'start_date' => '2026-06-01', 'status' => 'pending'],
            ]);

        ob_start();
        $this->controller->getCalendar(
            ['userId' => 'user-1', 'role' => 'admin'],
            ['month' => '6', 'year' => '2026']
        );
        $response = json_decode(ob_get_clean(), true);

        $this->assertArrayHasKey('2026-06-01', $response);
        $this->assertCount(2, $response['2026-06-01']);
    }

    // explicit grouping check so we know the controller returns date buckets
    public function test_get_calendar_groups_records_by_date(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'user-1', 'role' => 'admin', 'is_active' => 1]);

        $this->mockModel->method('fetchCalendar')
            ->willReturn([
                ['id' => '1', 'start_date' => '2026-06-01', 'status' => 'approved'],
                ['id' => '2', 'start_date' => '2026-06-02', 'status' => 'pending'],
            ]);

        ob_start();
        $this->controller->getCalendar(
            ['userId' => 'user-1', 'role' => 'admin'],
            ['month' => '6', 'year' => '2026']
        );
        $response = json_decode(ob_get_clean(), true);

        $this->assertArrayHasKey('2026-06-01', $response);
        $this->assertArrayHasKey('2026-06-02', $response);
    }
 
    // no auth on getCalendar must return 401
    public function test_get_calendar_returns_401_when_no_auth(): void {
        ob_start();
        $this->controller->getCalendar([], []);
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertEquals('Unauthorized', $response['message']);
    }
 
    // authenticated staff user requests their own leave list
    public function test_get_leave_returns_200_with_data(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'user-1', 'is_active' => 1]);

        $this->mockModel->method('getLeave')
            ->willReturn([
                ['id' => 'leave-1', 'user_id' => 'user-1', 'status' => 'approved'],
                ['id' => 'leave-2', 'user_id' => 'user-1', 'status' => 'pending'],
            ]);
 
        ob_start();
        $this->controller->getLeave(['userId' => 'user-1', 'role' => 'staff'], []);
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertCount(2, $response);
    }
 
    // no auth on getLeave must return 401
    public function test_get_leave_returns_401_when_no_auth(): void {
        ob_start();
        $this->controller->getLeave([], []);
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertEquals('Unauthorized', $response['message']);
    }
 
    // admin updates dates on an existing leave, updated row returned
    public function test_update_leave_returns_200_on_success(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'admin-1', 'role' => 'admin', 'is_active' => 1]);

        $this->mockModel->method('findById')
            ->willReturn(['id' => 'leave-1', 'status' => 'approved']);
 
        $this->mockModel->method('updateLeave')
            ->willReturn([
                'id'         => 'leave-1',
                'start_date' => '2026-07-01',
                'end_date'   => '2026-07-10',
                'status'     => 'approved',
            ]);
 
        ob_start();
        $this->controller->updateLeave(
            ['userId' => 'admin-1', 'role' => 'admin'],
            'leave-1',
            ['start_date' => '2026-07-01', 'end_date' => '2026-07-10']
        );
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('2026-07-10', $response['data']['end_date']);
    }

    // updateLeave should be admin-only just like the status update endpoint
    public function test_update_leave_returns_403_for_non_admin(): void {
        ob_start();
        $this->controller->updateLeave(
            ['userId' => 'staff-1', 'role' => 'staff'],
            'leave-1',
            ['start_date' => '2026-07-01', 'end_date' => '2026-07-10']
        );
        $response = json_decode(ob_get_clean(), true);

        $this->assertEquals('Forbidden', $response['message']);
    }
 
    // leave ID not found on updateLeave → 404
    public function test_update_leave_returns_404_when_not_found(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'admin-1', 'role' => 'admin', 'is_active' => 1]);

        $this->mockModel->method('findById')->willReturn(null);
 
        ob_start();
        $this->controller->updateLeave(
            ['userId' => 'admin-1', 'role' => 'admin'],
            'bad-leave-id',
            ['start_date' => '2026-07-01', 'end_date' => '2026-07-10']
        );
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertEquals('Leave request not found', $response['message']);
    }
 
    // invalid payload on updateLeave (end before start) → 400 from validator
    public function test_update_leave_returns_400_on_validation_failure(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'admin-1', 'role' => 'admin', 'is_active' => 1]);

        ob_start();
        $this->controller->updateLeave(
            ['userId' => 'admin-1', 'role' => 'admin'],
            'leave-1',
            ['start_date' => '2026-07-10', 'end_date' => '2026-07-01']
        );
        $response = json_decode(ob_get_clean(), true);
 
        $this->assertEquals('Validation failed', $response['message']);
        $this->assertArrayHasKey('end_date', $response['errors']);
    }

    // database errors in updateLeave should also return 500
    public function test_update_leave_returns_500_when_model_throws(): void {
        $this->mockModel->method('findActiveUser')
            ->willReturn(['user_id' => 'admin-1', 'role' => 'admin', 'is_active' => 1]);

        $this->mockModel->method('findById')
            ->willThrowException(new RuntimeException('DB failed'));

        ob_start();
        $this->controller->updateLeave(
            ['userId' => 'admin-1', 'role' => 'admin'],
            'leave-1',
            ['start_date' => '2026-07-10', 'end_date' => '2026-07-11']
        );
        $response = json_decode(ob_get_clean(), true);

        $this->assertEquals(500, http_response_code());
        $this->assertEquals('Internal Server Error', $response['message']);
    }
}
 
