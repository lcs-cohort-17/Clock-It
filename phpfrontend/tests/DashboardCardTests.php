<?php

use PHPUnit\Framework\TestCase;

class DashboardCardTests extends TestCase
{
    private string $content;

    protected function setUp(): void
    {
        $this->content = file_get_contents(
            __DIR__ . '/../phpfrontend/src/views/partials/dashboard-cards.php'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CARD LABELS
    |--------------------------------------------------------------------------
    */

    public function testCurrentlyOnsiteCardExists(): void
    {
        $this->assertStringContainsString(
            'Currently Onsite',
            $this->content
        );
    }

    public function testClockedInCardExists(): void
    {
        $this->assertStringContainsString(
            'Clocked In',
            $this->content
        );
    }

    public function testPendingSyncCardExists(): void
    {
        $this->assertStringContainsString(
            'Pending Sync',
            $this->content
        );
    }

    public function testTotalEventsCardExists(): void
    {
        $this->assertStringContainsString(
            'Total Events',
            $this->content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE GRID
    |--------------------------------------------------------------------------
    */

    public function testUsesBootstrapRow(): void
    {
        $this->assertStringContainsString(
            'row',
            $this->content
        );
    }

    public function testUsesMobileGrid(): void
    {
        $this->assertStringContainsString(
            'col-12',
            $this->content
        );
    }

    public function testUsesTabletGrid(): void
    {
        $this->assertStringContainsString(
            'col-sm-6',
            $this->content
        );
    }

    public function testUsesDesktopGrid(): void
    {
        $this->assertStringContainsString(
            'col-lg-3',
            $this->content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CARD STRUCTURE
    |--------------------------------------------------------------------------
    */

    public function testUsesBootstrapCardLayout(): void
    {
        $this->assertStringContainsString(
            'dashboard-stat-card',
            $this->content
        );
    }

    public function testCardUsesPadding(): void
    {
        $this->assertStringContainsString(
            'p-3',
            $this->content
        );
    }

    public function testCardUsesHeightUtility(): void
    {
        $this->assertStringContainsString(
            'h-100',
            $this->content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ICONS
    |--------------------------------------------------------------------------
    */

    public function testPeopleIconExists(): void
    {
        $this->assertStringContainsString(
            'bi-people-fill',
            $this->content
        );
    }

    public function testClockIconExists(): void
    {
        $this->assertStringContainsString(
            'bi-clock-fill',
            $this->content
        );
    }

    public function testSyncIconExists(): void
    {
        $this->assertStringContainsString(
            'bi-arrow-repeat',
            $this->content
        );
    }

    public function testEventsIconExists(): void
    {
        $this->assertStringContainsString(
            'bi-bar-chart-fill',
            $this->content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | COLORS
    |--------------------------------------------------------------------------
    */

    public function testPrimaryColorExists(): void
    {
        $this->assertStringContainsString(
            'text-primary',
            $this->content
        );
    }

    public function testSuccessColorExists(): void
    {
        $this->assertStringContainsString(
            'text-success',
            $this->content
        );
    }

    public function testWarningColorExists(): void
    {
        $this->assertStringContainsString(
            'text-warning',
            $this->content
        );
    }

    public function testDangerColorExists(): void
    {
        $this->assertStringContainsString(
            'text-danger',
            $this->content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VALUES
    |--------------------------------------------------------------------------
    */

    public function testCurrentlyOnsiteValueBindingExists(): void
    {
        $this->assertStringContainsString(
            "currentlyOnsite",
            $this->content
        );
    }

    public function testTotalStaffTodayBindingExists(): void
    {
        $this->assertStringContainsString(
            "totalStaffToday",
            $this->content
        );
    }

    public function testPendingSyncBindingExists(): void
    {
        $this->assertStringContainsString(
            "pendingSync",
            $this->content
        );
    }

    public function testTotalEventsBindingExists(): void
    {
        $this->assertStringContainsString(
            "totalEvents",
            $this->content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DEFINITION OF DONE
    |--------------------------------------------------------------------------
    */

    public function testDefinitionOfDoneCoverage(): void
    {
        $required = [
            'Currently Onsite',
            'Clocked In',
            'Pending Sync',
            'Total Events',
            'col-12',
            'col-sm-6',
            'col-lg-3',
            'dashboard-stat-card'
        ];

        foreach ($required as $item) {
            $this->assertStringContainsString(
                $item,
                $this->content
            );
        }
    }
}