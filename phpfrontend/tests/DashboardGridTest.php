<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

final class DashboardGridTest extends TestCase
{
    private string $content;

    protected function setUp(): void
    {
        $this->content = file_get_contents(
            dirname(__DIR__) . '/src/views/partials/DashboardGrid.php'
        );
    }

    public function testGridContainsStatusActivityAndScanAction(): void
    {
        $this->assertStringContainsString('staff-status-card', $this->content);
        $this->assertStringContainsString("Today's activity", $this->content);
        $this->assertStringContainsString("app_url('/scan-qr')", $this->content);
    }

    public function testGridOpensMergedCalendarAndLeaveModals(): void
    {
        $this->assertStringContainsString('@click="showCalendar = true"', $this->content);
        $this->assertStringContainsString('@click="showLeave = true"', $this->content);
        $this->assertStringContainsString("modals/CalendarModal.php", $this->content);
        $this->assertStringContainsString("modals/LeaveRequestModal.php", $this->content);
    }

    public function testProfileActionUsesCanonicalRoute(): void
    {
        $this->assertStringContainsString("app_url('/profile')", $this->content);
    }
}
