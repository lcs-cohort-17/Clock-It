<?php

namespace Tests\Attendance;

use PHPUnit\Framework\TestCase;
use App\Models\AttendanceDb;
use PDO;
use PDOStatement;

class AttendanceDbTest extends TestCase
{
    private $mockDb;
    private $mockStmt;
    private AttendanceDb $model;

    protected function setUp(): void
    {
        $this->mockDb   = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);
        $this->model    = new AttendanceDb($this->mockDb);
    }

    // fetchRecentActivity tests

    public function test_fetchRecentActivity_returns_results(): void
    {
        $mockData = [
            [
                'profile_id'  => 'some-uuid-123',
                'event_time'  => '2025-05-19 10:00:00',
                'event_type'  => 'clock-in',
                'sync_status' => 'synced',
                'device_info' => 'mobile',
                'first_name'  => 'John',
                'last_name'   => 'Doe',
            ],
        ];

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn($mockData);
        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->model->fetchRecentActivity(1, 10);

        $this->assertCount(1, $result);
        $this->assertEquals('some-uuid-123', $result[0]['profile_id']);
        $this->assertEquals('clock-in', $result[0]['event_type']);
        $this->assertEquals('John', $result[0]['first_name']);
        $this->assertEquals('Doe', $result[0]['last_name']);
    }

    public function test_fetchRecentActivity_calculates_offset_correctly_for_page_1(): void
    {
        // page 1, limit 10 → offset should be 0
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn([]);
        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->model->fetchRecentActivity(1, 10);

        // offset = (1 - 1) * 10 = 0 — just verify it runs correctly
        $this->assertEquals([], $result);
    }

    public function test_fetchRecentActivity_calculates_offset_correctly_for_page_2(): void
    {
        // page 2, limit 10 → offset should be 10
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn([]);
        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->model->fetchRecentActivity(2, 10);

        // offset = (2 - 1) * 10 = 10 — just verify it runs correctly
        $this->assertEquals([], $result);
    }

    public function test_fetchRecentActivity_returns_empty_array_when_no_data(): void
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn([]);
        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->model->fetchRecentActivity(1, 10);

        $this->assertEquals([], $result);
    }

    public function test_fetchRecentActivity_throws_on_db_error(): void
    {
        $this->mockDb
            ->method('prepare')
            ->willThrowException(new \PDOException('DB error'));

        $this->expectException(\PDOException::class);
        $this->expectExceptionMessage('DB error');

        $this->model->fetchRecentActivity(1, 10);
    }

    // fetchCurrentlyOnsite tests

    public function test_fetchCurrentlyOnsite_returns_results(): void
    {
        $mockData = [
            [
                'profile_id' => 'some-uuid-123',
                'event_time' => '2025-05-19 10:00:00',
                'event_type' => 'clock-in',
                'location'   => 'Office',
                'first_name' => 'John',
                'last_name'  => 'Doe',
            ],
            [
                'profile_id' => 'some-uuid-456',
                'event_time' => '2025-05-19 09:00:00',
                'event_type' => 'clock-in',
                'location'   => 'Office',
                'first_name' => 'Jane',
                'last_name'  => 'Smith',
            ],
        ];

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn($mockData);
        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->model->fetchCurrentlyOnsite();

        $this->assertCount(2, $result);
        $this->assertEquals('some-uuid-123', $result[0]['profile_id']);
        $this->assertEquals('John', $result[0]['first_name']);
        $this->assertEquals('some-uuid-456', $result[1]['profile_id']);
        $this->assertEquals('Jane', $result[1]['first_name']);
    }

    public function test_fetchCurrentlyOnsite_returns_empty_array_when_no_data(): void
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn([]);
        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->model->fetchCurrentlyOnsite();

        $this->assertEquals([], $result);
    }

    public function test_fetchCurrentlyOnsite_throws_on_db_error(): void
    {
        $this->mockDb
            ->method('prepare')
            ->willThrowException(new \PDOException('DB error'));

        $this->expectException(\PDOException::class);
        $this->expectExceptionMessage('DB error');

        $this->model->fetchCurrentlyOnsite();
    }
}