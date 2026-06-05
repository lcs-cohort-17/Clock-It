<?php

use PHPUnit\Framework\TestCase;

final class AttendanceHistoryTest extends TestCase
{
    private string $view;

    protected function setUp(): void
    {
        $this->view = file_get_contents(
            __DIR__ . '/../src/views/staff/AttendanceHistory.php'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CALENDAR COMPONENT
    |--------------------------------------------------------------------------
    */

    public function testCalendarContainerExists(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'calendar')
        );
    }

    public function testAlpineJsIsUsed(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'x-data')
        );
    }

    public function testMonthNavigationExists(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'Previous')
            || str_contains($this->view, 'prevMonth')
        );

        $this->assertTrue(
            str_contains($this->view, 'Next')
            || str_contains($this->view, 'nextMonth')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE EVENTS
    |--------------------------------------------------------------------------
    */

    public function testMockAttendanceDataExists(): void
    {
        $this->assertTrue(
            str_contains($this->view, '2026-05-22')
            || str_contains($this->view, 'attendanceEvents')
        );
    }

    public function testGreenDotIndicatorExists(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'green-dot')
            || str_contains($this->view, 'bg-success')
        );
    }

    public function testDateClickHandlerExists(): void
    {
        $this->assertTrue(
            str_contains($this->view, '@click')
            || str_contains($this->view, 'onclick')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BOOTSTRAP POPOVER
    |--------------------------------------------------------------------------
    */

    public function testBootstrapPopoverExists(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'data-bs-toggle="popover"')
            || str_contains($this->view, ':data-bs-toggle')
            || str_contains($this->view, 'bootstrap.Popover')
        );
    }

    public function testClockInClockOutTextExists(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'Clocked in')
            || str_contains($this->view, 'clockedIn')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVENESS
    |--------------------------------------------------------------------------
    */

    public function testBootstrapResponsiveClassesExist(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'container')
            || str_contains($this->view, 'row')
            || str_contains($this->view, 'col-')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | JAVASCRIPT
    |--------------------------------------------------------------------------
    */

    public function testNoTailwindClassesUsed(): void
    {
        $tailwindClasses = [
            'bg-slate',
            'text-slate',
            'grid-cols',
            'md:',
            'lg:',
            'sm:'
        ];

        $found = false;

        foreach ($tailwindClasses as $class) {
            if (str_contains($this->view, $class)) {
                $found = true;
            }
        }

        $this->assertFalse($found);
    }

    public function testCalendarJavascriptFileIncluded(): void
    {
        $layout = file_get_contents(
            __DIR__ . '/../src/views/layouts/app.php'
        );

        $this->assertTrue(
            str_contains($layout, 'calendar.js')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DEFINITION OF DONE
    |--------------------------------------------------------------------------
    */

    public function testDefinitionOfDoneRequirements(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'popover')
            || str_contains($this->view, 'data-bs-toggle')
        );

        $this->assertTrue(
            str_contains($this->view, 'green-dot')
            || str_contains($this->view, 'bg-success')
        );
    }
}
