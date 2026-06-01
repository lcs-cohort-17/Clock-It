<?php

use PHPUnit\Framework\TestCase;

final class AdminDashboardViewTest extends TestCase
{
    public function testAdminDashboardRendersSuccessfully(): void
    {
        $html = $this->renderAdminDashboard();

        $this->assertStringContainsString('<!doctype html>', $html);
        $this->assertStringContainsString('Admin Dashboard', $html);
    }

    public function testAdminDashboardContainsExportLogsToCsvText(): void
    {
        $html = $this->renderAdminDashboard();

        $this->assertStringContainsString('Export Logs to CSV', $html);
    }

    public function testAdminDashboardContainsExportButton(): void
    {
        $html = $this->renderAdminDashboard();

        $this->assertStringContainsString('<button', $html);
        $this->assertStringContainsString('x-on:click="exportLogs"', $html);
    }

    public function testExportButtonContainsExpectedBootstrapButtonClasses(): void
    {
        $html = $this->renderAdminDashboard();

        $this->assertStringContainsString('class="btn btn-primary d-inline-flex align-items-center gap-2"', $html);
    }

    public function testExportLoadingSpinnerMarkupIsPresent(): void
    {
        $html = $this->renderAdminDashboard();

        $this->assertStringContainsString('spinner-border spinner-border-sm', $html);
        $this->assertStringContainsString('x-show="loading"', $html);
    }

    public function testExportErrorAlertContainerIsPresent(): void
    {
        $html = $this->renderAdminDashboard();

        $this->assertStringContainsString('class="alert alert-danger py-2 px-3 mb-0"', $html);
        $this->assertStringContainsString('x-show="error"', $html);
        $this->assertStringContainsString('role="alert"', $html);
    }

    private function renderAdminDashboard(): string
    {
        $title = 'Admin Dashboard | Clock-It';
        $user = [
            'id' => 'admin-001',
            'name' => 'Demo Admin',
            'email' => 'admin@clockit.app',
            'employeeId' => 'ADM-001',
            'role' => 'admin',
        ];
        $stats = [
            'currentlyOnsite' => 2,
            'totalStaffToday' => 3,
            'pendingSync' => 0,
            'totalEvents' => 3,
        ];
        $events = [
            ['userName' => 'Demo Staff', 'type' => 'clock-in', 'timestamp' => '2026-05-29 08:00'],
            ['userName' => 'Anele Mokoena', 'type' => 'clock-in', 'timestamp' => '2026-05-29 08:15'],
            ['userName' => 'Lihle Dlamini', 'type' => 'clock-out', 'timestamp' => '2026-05-29 16:02'],
        ];
        $onsiteStaff = [
            ['name' => 'Demo Staff', 'employeeId' => 'EMP-001', 'clockedInAt' => '08:00'],
            ['name' => 'Anele Mokoena', 'employeeId' => 'EMP-002', 'clockedInAt' => '08:15'],
        ];

        ob_start();
        require __DIR__ . '/../src/views/admin/dashboard.php';

        return (string) ob_get_clean();
    }
}
