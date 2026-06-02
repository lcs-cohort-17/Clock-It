<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class CalendarModalTest extends TestCase
{
    private string $calendarModalFile;
    private string $content;

    protected function setUp(): void
    {
        $this->calendarModalFile = dirname(__DIR__) . '/modals/CalendarModal.php';
        $this->content = file_get_contents($this->calendarModalFile);
    }

    public function testCalendarModalFileExists(): void
    {
        $this->assertFileExists($this->calendarModalFile);
    }

    public function testCalendarModalHasModalWrapper(): void
    {
        $this->assertStringContainsString('x-show="showCalendar"', $this->content);
        $this->assertStringContainsString('class="custom-modal"', $this->content);
    }

    public function testCalendarModalHasHeader(): void
    {
        $this->assertStringContainsString('<h2>Calendar</h2>', $this->content);
        $this->assertStringContainsString('Pick a date to view your schedule.', $this->content);
    }

    public function testCalendarModalHasCloseButton(): void
    {
        $this->assertStringContainsString('class="btn-close"', $this->content);
        $this->assertStringContainsString('@click="showCalendar=false"', $this->content);
    }

    public function testCalendarModalRendersWeekdays(): void
    {
        $this->assertStringContainsString('<div>Su</div>', $this->content);
        $this->assertStringContainsString('<div>Mo</div>', $this->content);
        $this->assertStringContainsString('<div>Tu</div>', $this->content);
        $this->assertStringContainsString('<div>We</div>', $this->content);
        $this->assertStringContainsString('<div>Th</div>', $this->content);
        $this->assertStringContainsString('<div>Fr</div>', $this->content);
        $this->assertStringContainsString('<div>Sa</div>', $this->content);
    }

    public function testCalendarModalUsesAlpineTemplate(): void
    {
        $this->assertStringContainsString('<template x-for="day in 30">', $this->content);
        $this->assertStringContainsString('class="calendar-day"', $this->content);
        $this->assertStringContainsString('x-text="day"', $this->content);
    }
}
