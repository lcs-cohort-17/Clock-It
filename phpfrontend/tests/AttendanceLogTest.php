<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AttendanceLogTest extends TestCase
{
    private string $content;

    protected function setUp(): void
    {
        $this->content = file_get_contents(
            __DIR__ . '/../src/views/admin/attendance_log.php'
        );
    }

    /* ==========================================
       FRAMEWORK VALIDATION
    ========================================== */

    public function testUsesBootstrap(): void
    {
        $bootstrap =
            str_contains($this->content, 'container') ||
            str_contains($this->content, 'container-fluid') ||
            str_contains($this->content, 'row') ||
            str_contains($this->content, 'col-') ||
            str_contains($this->content, 'btn') ||
            str_contains($this->content, 'table-responsive');

        $this->assertTrue(
            $bootstrap,
            'Attendance Log should use Bootstrap.'
        );
    }

    public function testUsesAlpineJs(): void
    {
        $alpine =
            str_contains($this->content, 'x-data') ||
            str_contains($this->content, 'x-show') ||
            str_contains($this->content, 'x-for') ||
            str_contains($this->content, '@click');

        $this->assertTrue(
            $alpine,
            'Attendance Log should use Alpine.js.'
        );
    }

    public function testDoesNotUseTailwind(): void
    {
        $tailwind =
            str_contains($this->content, 'bg-') ||
            str_contains($this->content, 'text-') ||
            str_contains($this->content, 'flex') ||
            str_contains($this->content, 'grid') ||
            str_contains($this->content, 'md:') ||
            str_contains($this->content, 'lg:');

        $this->assertFalse(
            $tailwind,
            'Tailwind classes detected. Use Bootstrap instead.'
        );
    }

    /* ==========================================
       TOGGLE
    ========================================== */

    public function testClockEventsToggleExists(): void
    {
        $this->assertStringContainsString(
            'Clock Events',
            $this->content
        );
    }

    public function testAuditTrailToggleExists(): void
    {
        $this->assertStringContainsString(
            'Audit Trail',
            $this->content
        );
    }

    public function testToggleHasClickFunctionality(): void
    {
        $toggle =
            str_contains($this->content, '@click') ||
            str_contains($this->content, 'toggle') ||
            str_contains($this->content, 'activeTab') ||
            str_contains($this->content, 'currentView');

        $this->assertTrue(
            $toggle,
            'Toggle functionality not found.'
        );
    }

    /* ==========================================
       SEARCH & FILTERS
    ========================================== */

    public function testSearchInputExists(): void
    {
        $search =
            str_contains($this->content, 'search') ||
            str_contains($this->content, 'Search');

        $this->assertTrue(
            $search,
            'Search field missing.'
        );
    }

    public function testStaffFilterExists(): void
    {
        $staffFilter =
            str_contains($this->content, 'staffFilter') ||
            str_contains($this->content, 'Staff Filter') ||
            str_contains($this->content, 'staff');

        $this->assertTrue(
            $staffFilter,
            'Staff filter missing.'
        );
    }

    public function testStatusFilterExists(): void
    {
        $statusFilter =
            str_contains($this->content, 'statusFilter') ||
            str_contains($this->content, 'Status') ||
            str_contains($this->content, 'status');

        $this->assertTrue(
            $statusFilter,
            'Status filter missing.'
        );
    }

    public function testDebouncedSearchExists(): void
    {
        $debounce =
            str_contains($this->content, 'debounce') ||
            str_contains($this->content, 'setTimeout');

        $this->assertTrue(
            $debounce,
            'Debounced search not found.'
        );
    }

    /* ==========================================
       CLOCK EVENTS
    ========================================== */

    public function testStaffNameExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'staff.name') ||
            str_contains($this->content, 'Staff Name')
        );
    }

    public function testClockTimeExists(): void
    {
        $time =
            str_contains($this->content, 'clockIn') ||
            str_contains($this->content, 'clockOut') ||
            str_contains($this->content, 'timestamp');

        $this->assertTrue(
            $time,
            'Clock in/out time missing.'
        );
    }

    public function testDeviceFieldExists(): void
    {
        $this->assertStringContainsString(
            'Device',
            $this->content
        );
    }

    public function testLocationFieldExists(): void
    {
        $this->assertStringContainsString(
            'Location',
            $this->content
        );
    }

    public function testManualEditIconExists(): void
    {
        $edited =
            str_contains($this->content, 'edited') ||
            str_contains($this->content, 'tooltip') ||
            str_contains($this->content, 'pencil') ||
            str_contains($this->content, 'fa-pencil');

        $this->assertTrue(
            $edited,
            'Manual edit indicator missing.'
        );
    }

    /* ==========================================
       AUDIT TRAIL
    ========================================== */

    public function testAuditTrailTableExists(): void
    {
        $this->assertStringContainsString(
            '<table',
            $this->content
        );
    }

    public function testAuditTimestampExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'timestamp')
        );
    }

    public function testAuditAdminNameExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'admin')
        );
    }

    public function testAuditActionExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'action')
        );
    }

    public function testAuditDetailsExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'details')
        );
    }

    /* ==========================================
       CSV EXPORT
    ========================================== */

    public function testCsvExportExists(): void
    {
        $csv =
            str_contains($this->content, 'CSV') ||
            str_contains($this->content, 'Export CSV');

        $this->assertTrue(
            $csv,
            'CSV export missing.'
        );
    }

    /* ==========================================
       SORTING
    ========================================== */

    public function testAuditTrailSortingExists(): void
    {
        $sort =
            str_contains($this->content, 'sort') ||
            str_contains($this->content, 'orderBy');

        $this->assertTrue(
            $sort,
            'Sorting functionality missing.'
        );
    }

    /* ==========================================
       RESPONSIVENESS
    ========================================== */

    public function testResponsiveLayoutExists(): void
    {
        $responsive =
            str_contains($this->content, 'container-fluid') ||
            str_contains($this->content, 'table-responsive') ||
            str_contains($this->content, 'col-md') ||
            str_contains($this->content, 'col-lg') ||
            str_contains($this->content, 'col-sm');

        $this->assertTrue(
            $responsive,
            'Responsive Bootstrap layout missing.'
        );
    }

    /* ==========================================
       LOADING STATE
    ========================================== */

    public function testLoadingStateExists(): void
    {
        $loading =
            str_contains($this->content, 'Loading') ||
            str_contains($this->content, 'loading');

        $this->assertTrue(
            $loading,
            'Loading state missing.'
        );
    }

    /* ==========================================
       ERROR HANDLING
    ========================================== */

    public function testErrorHandlingExists(): void
    {
        $error =
            str_contains($this->content, 'Error') ||
            str_contains($this->content, 'error') ||
            str_contains($this->content, 'catch');

        $this->assertTrue(
            $error,
            'Error handling missing.'
        );
    }
}