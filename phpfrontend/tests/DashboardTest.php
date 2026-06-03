<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

final class DashboardTest extends TestCase
{
    public function testStaffDashboardUsesCanonicalShellAndGrid(): void
    {
        $content = file_get_contents(dirname(__DIR__) . '/phpfrontend/src/views/staff/staff-dashboard.php');

        $this->assertStringContainsString("partials/staff_sidebar.php", $content);
        $this->assertStringContainsString("partials/header.php", $content);
        $this->assertStringContainsString("partials/DashboardGrid.php", $content);
        $this->assertStringContainsString("layouts/app.php", $content);
    }

    public function testSharedLayoutLoadsCanonicalDashboardAssets(): void
    {
        $content = file_get_contents(dirname(__DIR__) . '/phpfrontend/src/views/layouts/app.php');

        $this->assertStringContainsString("/assets/css/dashboard.css", $content);
        $this->assertStringContainsString("/assets/js/dashboard.js", $content);
    }
}
