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

    public function testUsesBootstrap(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'container-fluid') ||
            str_contains($this->content, 'table-responsive')
        );
    }

    public function testUsesAlpineJs(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'x-data') ||
            str_contains($this->content, '@click')
        );
    }

    public function testDoesNotUseTailwind(): void
    {
        $tailwind =
            str_contains($this->content, 'bg-') ||
            str_contains($this->content, 'text-') ||
            str_contains($this->content, 'md:');

        $this->assertFalse($tailwind);
    }

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

    public function testSearchFieldExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'Search')
        );
    }

    public function testStaffFilterExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'Staff')
        );
    }

    public function testStatusFilterExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'Status')
        );
    }

    public function testManualEditIconExists(): void
    {
        $edited =
            str_contains($this->content, 'fa-pencil') ||
            str_contains($this->content, 'bi-pencil');

        $this->assertTrue($edited);
    }

    public function testCsvExportExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'Export CSV')
        );
    }

    public function testAuditTrailTableExists(): void
    {
        $this->assertStringContainsString(
            '<table',
            $this->content
        );
    }

    public function testResponsiveTableExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'table-responsive')
        );
    }

    public function testLoadingStateExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'Loading')
        );
    }

    public function testErrorHandlingExists(): void
    {
        $error =
            str_contains($this->content, 'Error') ||
            str_contains($this->content, 'error');

        $this->assertTrue($error);
    }

    public function testActiveToggleStateExists(): void
    {
        $active =
            str_contains($this->content, 'activeTab') ||
            str_contains($this->content, 'currentView');

        $this->assertTrue($active);
    }
}