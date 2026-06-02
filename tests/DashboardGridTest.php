<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class DashboardGridTest extends TestCase
{
    private string $dashboardGridFile;

    protected function setUp(): void
    {
        $this->dashboardGridFile = dirname(__DIR__) . '/DashboardGrid.php';
    }

    /**
     * Test that DashboardGrid.php file exists
     */
    public function testDashboardGridFileExists(): void
    {
        $this->assertFileExists($this->dashboardGridFile);
    }

    /**
     * Test that DashboardGrid has main content section
     */
    public function testDashboardGridHasContentSection(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('class="dashboard-content"', $content);
        $this->assertStringContainsString('id="dashboard"', $content);
    }

    /**
     * Test that DashboardGrid has greeting title
     */
    public function testDashboardGridHasGreetingTitle(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('class="dashboard-title"', $content);
        $this->assertStringContainsString('Hi, Sarah', $content);
    }

    /**
     * Test that DashboardGrid displays current date
     */
    public function testDashboardGridDisplaysDate(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('class="dashboard-date"', $content);
        $this->assertStringContainsString('Monday, 1 June 2026', $content);
    }

    /**
     * Test that DashboardGrid has Bootstrap grid structure
     */
    public function testDashboardGridHasBootstrapGrid(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('class="row g-4"', $content);
        $this->assertStringContainsString('class="col-lg-8"', $content);
        $this->assertStringContainsString('class="col-lg-4"', $content);
    }

    /**
     * Test that DashboardGrid has status/scan card
     */
    public function testDashboardGridHasStatusCard(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('id="scan"', $content);
        $this->assertStringContainsString('class="dashboard-card"', $content);
        $this->assertStringContainsString('OFFSITE', $content);
        $this->assertStringContainsString('You are currently', $content);
        $this->assertStringContainsString('Clocked Out', $content);
    }

    /**
     * Test that DashboardGrid has QR scan button
     */
    public function testDashboardGridHasScanButton(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('class="scan-button"', $content);
        $this->assertStringContainsString('fa-qrcode', $content);
        $this->assertStringContainsString('Scan QR', $content);
    }

    /**
     * Test that DashboardGrid displays last action
     */
    public function testDashboardGridDisplaysLastAction(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('Last action: 08:46 - 14 days ago', $content);
    }

    /**
     * Test that DashboardGrid has activity card
     */
    public function testDashboardGridHasActivityCard(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('id="activity"', $content);
        $this->assertStringContainsString("Today's activity", $content);
        $this->assertStringContainsString('fa-location-dot', $content);
    }

    /**
     * Test that DashboardGrid shows activity status
     */
    public function testDashboardGridShowsActivityStatus(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('class="activity-box"', $content);
        $this->assertStringContainsString('No clock events today yet.', $content);
    }

    /**
     * Test that DashboardGrid has quick action cards
     */
    public function testDashboardGridHasQuickActionCards(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('class="quick-card"', $content);
        $this->assertStringContainsString('class="col-md-4"', $content);
    }

    /**
     * Test that DashboardGrid has Calendar quick action
     */
    public function testDashboardGridHasCalendarCard(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('fa-calendar-days', $content);
        $this->assertStringContainsString('Calendar', $content);
        $this->assertStringContainsString('View your schedule', $content);
        $this->assertStringContainsString('@click="showCalendar=true"', $content);
    }

    /**
     * Test that DashboardGrid has Leave Requests quick action
     */
    public function testDashboardGridHasLeaveRequestsCard(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('fa-file-lines', $content);
        $this->assertStringContainsString('Leave Requests', $content);
        $this->assertStringContainsString('Submit a new request', $content);
        $this->assertStringContainsString('@click="showLeave=true"', $content);
    }

    /**
     * Test that DashboardGrid has Profile quick action
     */
    public function testDashboardGridHasProfileCard(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('id="profile"', $content);
        $this->assertStringContainsString('fa-user', $content);
        $this->assertStringContainsString('Profile', $content);
        $this->assertStringContainsString('Manage your account', $content);
    }

    /**
     * Test that DashboardGrid uses Font Awesome icons
     */
    public function testDashboardGridUsesFontAwesomeIcons(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('class="fas', $content);
        $this->assertStringContainsString('quick-icon', $content);
    }

    /**
     * Test that DashboardGrid has Alpine.js directives
     */
    public function testDashboardGridHasAlpineDirectives(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('@click="showCalendar=true"', $content);
        $this->assertStringContainsString('@click="showLeave=true"', $content);
    }

    /**
     * Test that DashboardGrid uses Bootstrap utility classes
     */
    public function testDashboardGridUsesBootstrapUtilities(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('badge bg-secondary-subtle', $content);
        $this->assertStringContainsString('text-muted', $content);
        $this->assertStringContainsString('mt-4', $content);
    }

    /**
     * Test that DashboardGrid has proper responsive layout
     */
    public function testDashboardGridHasResponsiveLayout(): void
    {
        $content = file_get_contents($this->dashboardGridFile);
        
        $this->assertStringContainsString('col-lg-8', $content);
        $this->assertStringContainsString('col-lg-4', $content);
        $this->assertStringContainsString('col-md-4', $content);
    }
}
