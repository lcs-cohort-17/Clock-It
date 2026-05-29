<?php
 
use PHPUnit\Framework\TestCase;
 
require_once __DIR__ . '/../../src/types/LeaveInterface.php';
require_once __DIR__ . '/../../src/models/LeaveDb.php';
 
class LeaveModelTest extends TestCase {
 
    private LeaveDbModel $model;
    private $mockConn;
 
    protected function setUp(): void {
        $this->mockConn = $this->createMock(LeaveDb::class);
        $this->model    = new LeaveDbModel($this->mockConn);
    }

    private function futureDateTime(int $daysAhead, string $time): string {
        return date('Y-m-d', strtotime("+{$daysAhead} days")) . ' ' . $time;
    }
 
    // insert submission
    public function test_insert_returns_array(): void {
        $mockRow = [
            'id'           => 'leave-uuid-1',
            'profile_id'   => 'user-uuid-1',
            'request_type' => 'annual',
            'status'       => 'pending',
        ];
 
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn($mockRow);
 
        $this->mockConn->method('prepare')->willReturn($mockStmt);
 
        $result = $this->model->insert('user-uuid-1', [
            'request_type' => 'annual',
            'start_date'   => '2026-06-01',
            'end_date'     => '2026-06-05',
            'reason'       => 'Vacation',
        ]);
 
        $this->assertIsArray($result);
        $this->assertEquals('pending', $result['status']);
    }

    // datetime payloads should also be forwarded to the database layer
    public function test_insert_with_datetime_fields(): void {
        $mockRow = [
            'id'              => 'leave-uuid-2',
            'profile_id'      => 'user-uuid-1',
            'request_type'    => 'sick',
            'status'          => 'pending',
            'start_date'      => $this->futureDateTime(1, '09:00'),
            'end_date'        => $this->futureDateTime(1, '17:00'),
        ];

        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')
            ->with($this->callback(function (array $params): bool {
                if (array_key_exists('id', $params) && count($params) === 1) {
                    return true;
                }

                return ($params['start_date'] ?? null) !== null
                    && ($params['end_date'] ?? null) !== null
                    && str_ends_with((string) $params['start_date'], '09:00')
                    && str_ends_with((string) $params['end_date'], '17:00');
            }))
            ->willReturn(true);
        $mockStmt->method('fetch')->willReturn($mockRow);

        $this->mockConn->method('prepare')->willReturn($mockStmt);

        $result = $this->model->insert('user-uuid-1', [
            'request_type' => 'sick',
            'start_date'    => $this->futureDateTime(1, '09:00'),
            'end_date'      => $this->futureDateTime(1, '17:00'),
            'reason'        => 'Doctor appointment',
        ]);

        $this->assertEquals($this->futureDateTime(1, '09:00'), $result['start_date']);
        $this->assertEquals($this->futureDateTime(1, '17:00'), $result['end_date']);
    }
 
    // find leave by id
    public function test_find_leave_by_id(): void {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn(false);
 
        $this->mockConn->method('prepare')->willReturn($mockStmt);
 
        $result = $this->model->findById('nonexistent_id');
        $this->assertNull($result);
    }
 
    // update request status
    public function test_update_status(): void {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn([
            'id'     => 'leave-1',
            'status' => 'approved',
        ]);
 
        $this->mockConn->method('prepare')->willReturn($mockStmt);
 
        $result = $this->model->updateStatus('leave-1', 'approved');
        $this->assertEquals('approved', $result['status']);
    }
 
    // fetch calendar
    public function test_fetch_calendar_for_admin(): void {
        $mockData = [[
            'id'         => '1',
            'status'     => 'approved',
            'start_date' => '2026-06-01',
        ]];
 
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchAll')->willReturn($mockData);
 
        $this->mockConn->method('prepare')->willReturn($mockStmt);
 
        $result = $this->model->fetchCalendar('user-uuid-1', 'admin');
        $this->assertEquals($mockData, $result);
    }
 
    // findActiveProfile — active user returns their profile row
    // controller calls this before every insert; null here aborts the submission
    public function test_find_active_profile_returns_profile(): void {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn(['id' => 'user-uuid-1', 'is_active' => 1]);
 
        $this->mockConn->method('prepare')->willReturn($mockStmt);
 
        $result = $this->model->findActiveProfile('user-uuid-1');
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['is_active']);
    }
 
    // findActiveProfile — user does not exist, model returns null
    public function test_find_active_profile_returns_null_when_not_found(): void {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn(false);
 
        $this->mockConn->method('prepare')->willReturn($mockStmt);
 
        $result = $this->model->findActiveProfile('ghost-user');
        $this->assertNull($result);
    }
 
    // findActiveProfile — is_active === 0 row is returned as-is by the model
    // the controller is responsible for checking is_active before calling insert()
    public function test_find_active_profile_returns_row_for_inactive_user(): void {
        $inactiveRow = ['id' => 'user-uuid-2', 'is_active' => 0];
 
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn($inactiveRow);
 
        $this->mockConn->method('prepare')->willReturn($mockStmt);
 
        $result = $this->model->findActiveProfile('user-uuid-2');
        $this->assertIsArray($result);
        $this->assertEquals(0, $result['is_active']);
    }
 
    // getLeave — staff user receives their own leave rows
    public function test_get_leave_returns_array_for_staff(): void {
        $rows = [
            ['id' => 'leave-1', 'profile_id' => 'user-uuid-1', 'status' => 'pending'],
            ['id' => 'leave-2', 'profile_id' => 'user-uuid-1', 'status' => 'approved'],
        ];
 
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchAll')->willReturn($rows);
 
        $this->mockConn->method('prepare')->willReturn($mockStmt);
 
        $result = $this->model->getLeave('user-uuid-1', 'staff');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('user-uuid-1', $result[0]['profile_id']);
    }
 
    // getLeave — no records found, model must return [] not null/false
    public function test_get_leave_returns_empty_array_when_no_records(): void {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchAll')->willReturn([]);
 
        $this->mockConn->method('prepare')->willReturn($mockStmt);
 
        $result = $this->model->getLeave('user-uuid-no-leaves', 'staff');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
 
    // updateLeave — admin extends an existing request, updated row is returned
    public function test_update_leave_returns_updated_row(): void {
        $updatedRow = [
            'id'         => 'leave-1',
            'start_date' => '2026-07-01',
            'end_date'   => '2026-07-10',
            'status'     => 'approved',
        ];
 
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn($updatedRow);
 
        $this->mockConn->method('prepare')->willReturn($mockStmt);
 
        $result = $this->model->updateLeave('leave-1', [
            'start_date' => '2026-07-01',
            'end_date'   => '2026-07-10',
        ]);
 
        $this->assertIsArray($result);
        $this->assertEquals('2026-07-10', $result['end_date']);
    }
 
    // updateLeave — leave ID does not exist, model returns null → controller sends 404
    public function test_update_leave_returns_null_when_not_found(): void {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn(false);
 
        $this->mockConn->method('prepare')->willReturn($mockStmt);
 
        $result = $this->model->updateLeave('nonexistent-leave', [
            'start_date' => '2026-07-01',
            'end_date'   => '2026-07-05',
        ]);
 
        $this->assertNull($result);
    }
}
 
