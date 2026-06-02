<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class SidebarTest extends TestCase
{
    private string $sidebarFile;
    private string $content;

    protected function setUp(): void
    {
        $this->sidebarFile = dirname(__DIR__) . '/Sidebar.php';
        $this->content = file_get_contents($this->sidebarFile);
    }

    /*
    |--------------------------------------------------------------------------
    | FILE EXISTS
    |--------------------------------------------------------------------------
    */

    public function testSidebarFileExists(): void
    {
        $this->assertFileExists($this->sidebarFile);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGO
    |--------------------------------------------------------------------------
    */

    public function testLogoExists(): void
    {
        $this->assertStringContainsString('class="sidebar-logo"', $this->content);
    }

    public function testLogoImageExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, '<img') || str_contains($this->content, 'logo')
        );
    }

    public function testLogoHasAltText(): void
    {
        $this->assertStringContainsString('alt=', $this->content);
    }

    /*
    |--------------------------------------------------------------------------
    | SIDEBAR STRUCTURE
    |--------------------------------------------------------------------------
    */

    public function testSidebarElementExists(): void
    {
        $this->assertStringContainsString('class="sidebar"', $this->content);
    }

    public function testSidebarNavExists(): void
    {
        $this->assertStringContainsString('<nav', $this->content);
    }

    public function testSidebarNavListExists(): void
    {
        $this->assertStringContainsString('<ul', $this->content);
    }

    /*
    |--------------------------------------------------------------------------
    | NAVIGATION ITEMS
    |--------------------------------------------------------------------------
    */

    public function testDashboardNavItemExists(): void
    {
        $this->assertStringContainsString('Dashboard', $this->content);
    }

    public function testCalendarNavItemExists(): void
    {
        $this->assertStringContainsString('Calendar', $this->content);
    }

    public function testLeaveNavItemExists(): void
    {
        $this->assertStringContainsString('Leave', $this->content);
    }

    public function testProfileNavItemExists(): void
    {
        $this->assertStringContainsString('Profile', $this->content);
    }

    public function testNavLinksExist(): void
    {
        $this->assertStringContainsString('<a ', $this->content);
        $this->assertStringContainsString('href=', $this->content);
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVE NAVIGATION STATE
    |--------------------------------------------------------------------------
    */

    public function testActiveClassExists(): void
    {
        $this->assertStringContainsString('active', $this->content);
    }

    public function testActiveNavItemIsDashboard(): void
    {
        // Active state should be on the dashboard nav item
        $this->assertMatchesRegularExpression('/active[^"]*"[^>]*>.*Dashboard|Dashboard.*active/s', $this->content);
    }

    /*
    |--------------------------------------------------------------------------
    | ICONS
    |--------------------------------------------------------------------------
    */

    public function testNavIconsExist(): void
    {
        $this->assertStringContainsString('fas fa-', $this->content);
    }

    public function testDashboardIconExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'fa-house') ||
            str_contains($this->content, 'fa-home') ||
            str_contains($this->content, 'fa-gauge')
        );
    }

    public function testCalendarIconExists(): void
    {
        $this->assertStringContainsString('fa-calendar', $this->content);
    }

    public function testLeaveIconExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'fa-file') ||
            str_contains($this->content, 'fa-clipboard')
        );
    }

    public function testProfileIconExists(): void
    {
        $this->assertStringContainsString('fa-user', $this->content);
    }

    /*
    |--------------------------------------------------------------------------
    | MOBILE NAVIGATION
    |--------------------------------------------------------------------------
    */

    public function testMobileToggleExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'sidebar-toggle') ||
            str_contains($this->content, 'navbar-toggler') ||
            str_contains($this->content, 'fa-bars')
        );
    }

    public function testSidebarHasResponsiveClass(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'sidebar-mobile') ||
            str_contains($this->content, 'd-lg-') ||
            str_contains($this->content, 'collapse') ||
            str_contains($this->content, 'offcanvas')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function testLogoutLinkExists(): void
    {
        $this->assertStringContainsString('logout', strtolower($this->content));
    }

    public function testLogoutIconExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'fa-sign-out') ||
            str_contains($this->content, 'fa-right-from-bracket') ||
            str_contains($this->content, 'fa-power-off')
        );
    }

    public function testLogoutHrefExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'logout.php') ||
            str_contains($this->content, '/logout') ||
            str_contains($this->content, 'action="logout"')
        );
    }
}
