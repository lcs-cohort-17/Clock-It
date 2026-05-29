<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class AdminSettingsPageTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['gs_connected'] = false;
        $_SESSION['gs_connected_sheet'] = '';
        $_SESSION['gs_connected_since'] = '';
        $_SESSION['security_timeout'] = 30;
        $_SESSION['retention_days'] = 365;
        $_SESSION['retention_records'] = [
            ['id' => 1, 'name' => 'Old attendance record', 'date' => date('Y-m-d', strtotime('-420 days'))],
            ['id' => 2, 'name' => 'Recent attendance record', 'date' => date('Y-m-d', strtotime('-10 days'))],
        ];
        unset($_SESSION['security_error'], $_SESSION['security_success'], $_SESSION['retention_error'], $_SESSION['retention_success']);
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    private function renderSettingsPage(): string
    {
        ob_start();
        include __DIR__ . '/../src/views/admin/settings.php';
        return ob_get_clean();
    }

    private function renderSettingsPost(string $timeout): string
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'save_security',
            'security_timeout' => $timeout,
        ];

        return $this->renderSettingsPage();
    }

    private function renderSettingsActionPost(string $action): string
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => $action];

        return $this->renderSettingsPage();
    }

    public function testSettingsPageRendersSuccessfully(): void
    {
        $output = $this->renderSettingsPage();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('<h1>Settings</h1>', $output);
    }

    public function testSidebarIsVisibleOnSettingsPage(): void
    {
        $output = $this->renderSettingsPage();

        $this->assertStringContainsString('sidebar admin-sidebar', $output);
        $this->assertStringContainsString('Clock-It', $output);
        $this->assertStringContainsString('Admin portal', $output);
        $this->assertStringContainsString('/admin-dashboard', $output);
        $this->assertStringContainsString('/admin/settings', $output);
    }

    public function testSettingsPageShowsThreeFeatureCards(): void
    {
        $output = $this->renderSettingsPage();

        $this->assertStringContainsString('Google Sheets Integration', $output);
        $this->assertStringContainsString('Security Settings', $output);
        $this->assertStringContainsString('Data Retention', $output);
        $this->assertSame(3, substr_count($output, 'class="card settings-card'));
    }

    public function testGoogleSheetsFeatureCardContent(): void
    {
        $output = $this->renderSettingsPage();

        $this->assertStringContainsString('Connect attendance export to Google Sheets.', $output);
        $this->assertStringContainsString('Two-way sync of attendance data', $output);
        $this->assertStringContainsString('Not connected', $output);
        $this->assertStringContainsString('Connect Google Sheet', $output);
        $this->assertStringContainsString('viewBox="0 0 48 48"', $output);
    }

    public function testConnectedGoogleSheetsStateDisplaysOnSettingsPage(): void
    {
        $_SESSION['gs_connected'] = true;
        $_SESSION['gs_connected_sheet'] = 'attendance_export_2026';
        $_SESSION['gs_connected_since'] = '29 May 2026';

        $output = $this->renderSettingsPage();

        $this->assertStringContainsString('Connected', $output);
        $this->assertStringContainsString('Disconnect', $output);
        $this->assertStringContainsString('attendance_export_2026', $output);
    }

    public function testConnectButtonOpensSetupModal(): void
    {
        $_GET['modal'] = 'connect';

        $output = $this->renderSettingsPage();

        $this->assertStringContainsString('Connect to Google Sheets', $output);
        $this->assertStringContainsString('A new Google Sheet will be created for attendance data.', $output);
        $this->assertStringContainsString('Confirm &amp; Connect', $output);
    }

    public function testDisconnectButtonOpensConfirmationModal(): void
    {
        $_SESSION['gs_connected'] = true;
        $_GET['modal'] = 'disconnect';

        $output = $this->renderSettingsPage();

        $this->assertStringContainsString('Disconnect Google Sheets?', $output);
        $this->assertStringContainsString('Are you sure?', $output);
        $this->assertStringContainsString('Yes, disconnect', $output);
    }

    public function testStatusBadgeUpdatesAfterConnecting(): void
    {
        $output = $this->renderSettingsActionPost('connect_google_sheets');

        $this->assertTrue($_SESSION['gs_connected']);
        $this->assertStringContainsString('Connected', $output);
        $this->assertStringContainsString('Disconnect', $output);
    }

    public function testStatusBadgeUpdatesAfterDisconnecting(): void
    {
        $_SESSION['gs_connected'] = true;

        $output = $this->renderSettingsActionPost('disconnect_google_sheets');

        $this->assertFalse($_SESSION['gs_connected']);
        $this->assertStringContainsString('Not connected', $output);
        $this->assertStringContainsString('Connect Google Sheet', $output);
    }

    public function testSecurityFeatureCardContent(): void
    {
        $output = $this->renderSettingsPage();

        $this->assertStringContainsString('Require strong passwords', $output);
        $this->assertStringContainsString('Enable two-factor authentication', $output);
        $this->assertStringContainsString('Session timeout (minutes)', $output);
        $this->assertStringContainsString('Save Security Settings', $output);
        $this->assertStringContainsString('type="checkbox" checked', $output);
    }

    public function testDataRetentionFeatureCardContent(): void
    {
        $output = $this->renderSettingsPage();

        $this->assertStringContainsString('Keep attendance logs for 12 months.', $output);
        $this->assertStringContainsString('Keep records for (days)', $output);
        $this->assertStringContainsString('Purge Old Records', $output);
    }

    public function testAdminCanSaveTimeoutValueSuccessfully(): void
    {
        $output = $this->renderSettingsPost('45');

        $this->assertSame(45, $_SESSION['security_timeout']);
        $this->assertStringContainsString('Security settings saved successfully.', $output);
        $this->assertStringContainsString('value="45"', $output);
    }

    public function testSavedTimeoutPersistsOnPageRefresh(): void
    {
        $this->renderSettingsPost('60');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        $output = $this->renderSettingsPage();

        $this->assertStringContainsString('value="60"', $output);
    }

    public function testLettersAreRejectedForTimeout(): void
    {
        $output = $this->renderSettingsPost('abc');

        $this->assertSame(30, $_SESSION['security_timeout']);
        $this->assertStringContainsString('Please enter a positive whole number for the session timeout.', $output);
    }

    public function testNegativeNumbersAreRejectedForTimeout(): void
    {
        $output = $this->renderSettingsPost('-5');

        $this->assertSame(30, $_SESSION['security_timeout']);
        $this->assertStringContainsString('Please enter a positive whole number for the session timeout.', $output);
    }

    public function testEmptyTimeoutIsRejected(): void
    {
        $output = $this->renderSettingsPost('');

        $this->assertSame(30, $_SESSION['security_timeout']);
        $this->assertStringContainsString('Please enter a positive whole number for the session timeout.', $output);
    }

    public function testSettingsUsesPartialFeatureRenderer(): void
    {
        require_once __DIR__ . '/../src/views/partials/settings_features.php';

        $this->assertTrue(function_exists('render_google_sheets_settings_card'));
        $this->assertTrue(function_exists('render_security_settings_card'));
        $this->assertTrue(function_exists('render_data_retention_settings_card'));
    }
}
