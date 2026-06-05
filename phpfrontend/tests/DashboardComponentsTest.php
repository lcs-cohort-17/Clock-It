<?php

declare(strict_types=1);

use ClockIt\Data\AttendanceRepository;
use PHPUnit\Framework\TestCase;

final class DashboardComponentsTest extends TestCase
{
    public function testQuickActionsRenderLinksAndSheetsStatus(): void
    {
        $repository = new AttendanceRepository();
        $actions = $repository->quickActions();
        $sheetsConnected = false;

        $html = $this->renderComponent('quick-actions.php', compact('actions', 'sheetsConnected'));

        $this->assertStringContainsString('href="/qr-generator"', $html);
        $this->assertStringContainsString('QR Generator', $html);
        $this->assertStringContainsString('href="/attendance-logs"', $html);
        $this->assertStringContainsString('Attendance Logs', $html);
        $this->assertStringContainsString('href="/sheets"', $html);
        $this->assertStringContainsString('Not connected', $html);
        $this->assertStringContainsString('btn btn-light', $html);
    }

    public function testOnsiteCardUsesAlpineApiBackedStateAndEmptyState(): void
    {
        $html = $this->renderComponent('onsite-card.php');

        $this->assertStringContainsString('Currently onsite', $html);
        $this->assertStringContainsString('list-group', $html);
        $this->assertStringContainsString('x-for="staff in onsiteStaff"', $html);
        $this->assertStringContainsString('staff.signedInAt', $html);
        $this->assertStringContainsString('No staff currently onsite.', $html);
        $this->assertStringContainsString('badge rounded-pill', $html);
    }

    public function testActivityCardUsesAlpineApiBackedStateAndEmptyState(): void
    {
        $html = $this->renderComponent('activity-card.php');

        $this->assertStringContainsString('Recent activity', $html);
        $this->assertStringContainsString('list-group', $html);
        $this->assertStringContainsString('x-for="event in recentActivity.slice(0, 10)"', $html);
        $this->assertStringContainsString('event.action', $html);
        $this->assertStringContainsString("event.action === 'Clock In'", $html);
        $this->assertStringContainsString('No events today.', $html);
    }

    private function renderComponent(string $component, array $variables = []): string
    {
        require_once __DIR__ . '/../src/bootstrap.php';
        extract($variables, EXTR_SKIP);

        ob_start();
        require __DIR__ . '/../src/views/partials/' . $component;

        return (string) ob_get_clean();
    }
}
