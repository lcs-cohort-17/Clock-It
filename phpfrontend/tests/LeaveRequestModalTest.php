<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

final class LeaveRequestModalTest extends TestCase
{
    public function testLeaveModalUsesMergedResponsiveForm(): void
    {
        $content = file_get_contents(dirname(__DIR__) . '/src/views/modals/LeaveRequestModal.php');

        $this->assertStringContainsString('x-show="showLeave"', $content);
        $this->assertStringContainsString('staff-dashboard-modal', $content);
        $this->assertStringContainsString('@submit.prevent="showLeave = false"', $content);
        $this->assertStringContainsString('Annual Leave', $content);
        $this->assertStringContainsString('Sick Leave', $content);
        $this->assertStringContainsString('Family Responsibility', $content);
        $this->assertStringContainsString('col-12 col-sm-6', $content);
    }
}
