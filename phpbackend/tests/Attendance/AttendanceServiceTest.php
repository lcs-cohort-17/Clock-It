<?php

namespace Tests\Attendance;

use PHPUnit\Framework\TestCase;
use App\Services\AttendanceService;

class AttendanceServiceTest extends TestCase
{
    private AttendanceService $service;

    protected function setUp(): void
    {
        $this->service = new AttendanceService();
    }

    // formatRecentActivity tests

    public function test_formatRecentActivity_formats_raw_records_into_correct_shape(): void
    {
        $input = [
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

        $result = $this->service->formatRecentActivity($input);

        $this->assertEquals([
            'profile_id'  => 'some-uuid-123',
            'event_time'  => '2025-05-19 10:00:00',
            'event_type'  => 'clock-in',
            'sync_status' => 'synced',
            'device_info' => 'mobile',
            'staff'       => 'John Doe',
        ], $result[0]);
    }

    public function test_formatRecentActivity_combines_first_and_last_name_into_staff(): void
    {
        $input = [
            [
                'profile_id'  => 'some-uuid-123',
                'event_time'  => '2025-05-19 10:00:00',
                'event_type'  => 'clock-in',
                'sync_status' => 'synced',
                'device_info' => 'mobile',
                'first_name'  => 'Jane',
                'last_name'   => 'Smith',
            ],
        ];

        $result = $this->service->formatRecentActivity($input);

        $this->assertEquals('Jane Smith', $result[0]['staff']);
    }

    public function test_formatRecentActivity_handles_multiple_records(): void
    {
        $input = [
            [
                'profile_id'  => 'some-uuid-123',
                'event_time'  => '2025-05-19 10:00:00',
                'event_type'  => 'clock-in',
                'sync_status' => 'synced',
                'device_info' => 'mobile',
                'first_name'  => 'John',
                'last_name'   => 'Doe',
            ],
            [
                'profile_id'  => 'some-uuid-456',
                'event_time'  => '2025-05-19 09:00:00',
                'event_type'  => 'clock-out',
                'sync_status' => 'synced',
                'device_info' => 'desktop',
                'first_name'  => 'Jane',
                'last_name'   => 'Smith',
            ],
        ];

        $result = $this->service->formatRecentActivity($input);

        $this->assertCount(2, $result);
        $this->assertEquals('John Doe', $result[0]['staff']);
        $this->assertEquals('Jane Smith', $result[1]['staff']);
    }

    public function test_formatRecentActivity_handles_empty_array(): void
    {
        $result = $this->service->formatRecentActivity([]);

        $this->assertCount(0, $result);
        $this->assertEquals([], $result);
    }

    // formatOnsite tests

    public function test_formatOnsite_formats_raw_records_into_correct_shape(): void
    {
        $input = [
            [
                'profile_id' => 'some-uuid-123',
                'event_time' => '2025-05-19 10:00:00',
                'event_type' => 'clock-in',
                'location'   => 'Office',
                'first_name' => 'John',
                'last_name'  => 'Doe',
            ],
        ];

        $result = $this->service->formatOnsite($input);

        $this->assertEquals([
            'profile_id' => 'some-uuid-123',
            'event_time' => '2025-05-19 10:00:00',
            'location'   => 'Office',
            'staff'      => 'John Doe',
        ], $result[0]);
    }

    public function test_formatOnsite_combines_first_and_last_name_into_staff(): void
    {
        $input = [
            [
                'profile_id' => 'some-uuid-123',
                'event_time' => '2025-05-19 10:00:00',
                'event_type' => 'clock-in',
                'location'   => 'Office',
                'first_name' => 'Jane',
                'last_name'  => 'Smith',
            ],
        ];

        $result = $this->service->formatOnsite($input);

        $this->assertEquals('Jane Smith', $result[0]['staff']);
    }

    public function test_formatOnsite_handles_multiple_onsite_staff(): void
    {
        $input = [
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
                'location'   => 'Warehouse',
                'first_name' => 'Jane',
                'last_name'  => 'Smith',
            ],
        ];

        $result = $this->service->formatOnsite($input);

        $this->assertCount(2, $result);
        $this->assertEquals('John Doe', $result[0]['staff']);
        $this->assertEquals('Jane Smith', $result[1]['staff']);
    }

    public function test_formatOnsite_handles_empty_array(): void
    {
        $result = $this->service->formatOnsite([]);

        $this->assertCount(0, $result);
        $this->assertEquals([], $result);
    }
}