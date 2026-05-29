<?php
 
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
 
require_once __DIR__ . '/../../src/types/LeaveInterface.php';
require_once __DIR__ . '/../../src/utils/LeaveValidator.php';
require_once __DIR__ . '/../../src/models/LeaveDb.php';
require_once __DIR__ . '/../../src/controllers/LeaveController.php';
require_once __DIR__ . '/../../src/routes/LeaveRoutes.php';
 
class LeaveRoutesTest extends TestCase {
 
    /** @var LeaveController&MockObject */
    private $mockController;
 
    protected function setUp(): void {
        $this->mockController = $this->createMock(LeaveController::class);
    }
 
    private function simulateRequest(string $method, string $uri, array $body = [], array $query = []): array {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI']    = $uri;
        $_GET                      = $query;
 
        ob_start();
        handleLeaveRoutes($this->mockController);
        $output = ob_get_clean();
 
        return json_decode($output ?: '{}', true) ?? [];
    }
 
    // POST submit leave
    public function test_post_leaves_calls_submit_leave(): void {
        $this->mockController->expects($this->once())->method('submitLeave');
        $this->simulateRequest('POST', '/api/leaves');
    }
 
    // GET calendar
    public function test_get_calendar_calls_get_calendar(): void {
        $this->mockController->expects($this->once())->method('getCalendar');
        $this->simulateRequest('GET', '/api/leaves/getCalendar');
    }
 
    // GET leave list
    public function test_get_leave_calls_get_leave(): void {
        $this->mockController->expects($this->once())->method('getLeave');
        $this->simulateRequest('GET', '/api/leaves/getLeave');
    }
 
    // PATCH leave status
    public function test_patch_status_calls_update_leave_status(): void {
        $this->mockController->expects($this->once())->method('updateLeaveStatus');
        $this->simulateRequest('PATCH', '/api/leaves/leave-uuid-5678/status');
    }
 
    // PATCH update request
    public function test_patch_update_request_calls_update_leave(): void {
        $this->mockController->expects($this->once())->method('updateLeave');
        $this->simulateRequest('PATCH', '/api/leaves/leave-uuid-5678/updateRequest');
    }
 
    // unknown routes
    public function test_unknown_route_returns_404(): void {
        $this->mockController->expects($this->never())->method('submitLeave');
 
        ob_start();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/api/leaves/unknown';
        handleLeaveRoutes($this->mockController);
        ob_get_clean();
 
        $this->assertEquals(404, http_response_code());
    }
 
    // route must extract the UUID from the URL and pass it as the second argument
    public function test_patch_status_passes_correct_leave_id(): void {
        $this->mockController
            ->expects($this->once())
            ->method('updateLeaveStatus')
            ->with($this->anything(), 'leave-uuid-5678', $this->anything());
 
        $this->simulateRequest('PATCH', '/api/leaves/leave-uuid-5678/status');
    }
 
    // same check for updateLeave route
    public function test_patch_update_request_passes_correct_leave_id(): void {
        $this->mockController
            ->expects($this->once())
            ->method('updateLeave')
            ->with($this->anything(), 'leave-uuid-5678', $this->anything());
 
        $this->simulateRequest('PATCH', '/api/leaves/leave-uuid-5678/updateRequest');
    }
 
    // route must forward month and year from $_GET into getCalendar()
    public function test_get_calendar_passes_query_params(): void {
        $this->mockController
            ->expects($this->once())
            ->method('getCalendar')
            ->with(
                $this->anything(),
                $this->callback(fn($q) => ($q['month'] ?? null) === '6' && ($q['year'] ?? null) === '2026')
            );
 
        $this->simulateRequest('GET', '/api/leaves/getCalendar', [], ['month' => '6', 'year' => '2026']);
    }
}