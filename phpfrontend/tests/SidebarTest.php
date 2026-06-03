<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

final class SidebarTest extends TestCase
{
    public function testCanonicalStaffSidebarHasPortalRoutesAndLogout(): void
    {
        $content = file_get_contents(dirname(__DIR__) . '/phpfrontend/src/views/partials/staff_sidebar.php');

        $this->assertStringContainsString('Staff Portal', $content);
        $this->assertStringContainsString("app_url('/staff-dashboard')", $content);
        $this->assertStringContainsString("app_url('/scan-qr')", $content);
        $this->assertStringContainsString("app_url('/history')", $content);
        $this->assertStringContainsString("app_url('/profile')", $content);
        $this->assertStringContainsString("app_url('/logout')", $content);
        $this->assertStringContainsString('staff-sidebar-avatar', $content);
    }
}
