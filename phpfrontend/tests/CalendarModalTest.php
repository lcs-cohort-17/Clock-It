<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

final class CalendarModalTest extends TestCase
{
    public function testCalendarModalUsesMergedAlpineStateAndBootstrapIcons(): void
    {
        $content = file_get_contents(dirname(__DIR__) . '/src/views/modals/CalendarModal.php');

        $this->assertStringContainsString('x-show="showCalendar"', $content);
        $this->assertStringContainsString('staff-dashboard-modal', $content);
        $this->assertStringContainsString('x-for="(day, index) in calendarDays"', $content);
        $this->assertStringContainsString('bi bi-chevron-left', $content);
        $this->assertStringContainsString('bi bi-chevron-right', $content);
    }
}
