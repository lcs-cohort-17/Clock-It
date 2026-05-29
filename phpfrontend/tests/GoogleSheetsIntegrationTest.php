<?php
/**
 * GoogleSheetsIntegrationTest
 *
 * Tests for the GoogleSheetsIntegration component including:
 * - Component rendering
 * - Status indicator display
 * - Modal visibility
 * - Session state management
 * - Form submissions
 */

namespace Tests;

use PHPUnit\Framework\TestCase;

class GoogleSheetsIntegrationTest extends TestCase
{
    /**
     * Flag to track if component has been included
     */
    private static $componentIncluded = false;

    /**
     * Set up test environment before each test
     */
    protected function setUp(): void
    {
        // Start session for each test
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Reset session state
        $_SESSION['gs_connected'] = false;
        $_SESSION['gs_connected_sheet'] = '';
        $_SESSION['gs_connected_since'] = '';
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Include component only once
        if (!self::$componentIncluded) {
            require_once __DIR__ . '/../src/views/partials/google_sheets_integration.php';
            self::$componentIncluded = true;
        }
    }

    /**
     * Tear down after each test
     */
    protected function tearDown(): void
    {
        // Clear session
        $_SESSION = [];
    }

    /**
     * Test component renders without errors
     */
    public function testComponentRendersSuccessfully(): void
    {
        ob_start();
        render_google_sheets_integration();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Google Sheets Integration', $output);
    }

    /**
     * Test component displays "Not connected" badge when disconnected
     */
    public function testDisplaysNotConnectedBadgeWhenDisconnected(): void
    {
        $_SESSION['gs_connected'] = false;
        
        ob_start();
        render_google_sheets_integration();
        $output = ob_get_clean();

        $this->assertStringContainsString('Not connected', $output);
        $this->assertStringContainsString('border-slate-300', $output);
    }

    /**
     * Test component displays "Connected" badge when connected
     */
    public function testDisplaysConnectedBadgeWhenConnected(): void
    {
        ob_start();
        render_google_sheets_integration(
            isConnected: true,
            connectedSheet: 'attendance_export_2026',
            connectedSince: '1 Jan 2026',
            modal: '',
            selfUrl: '/AdminSettingsPage.php'
        );
        $output = ob_get_clean();

        $this->assertStringContainsString('Connected', $output);
        $this->assertStringContainsString('border-[#9ccf57]', $output);
    }

    /**
     * Test Google Sheets icon renders
     */
    public function testGoogleSheetsIconRenders(): void
    {
        ob_start();
        render_google_sheets_integration();
        $output = ob_get_clean();

        $this->assertStringContainsString('<svg', $output);
        $this->assertStringContainsString('viewBox="0 0 48 48"', $output);
        $this->assertStringContainsString('xmlns="http://www.w3.org/2000/svg"', $output);
    }

    /**
     * Test "Connect Google Sheet" button shows when not connected
     */
    public function testConnectButtonShowsWhenNotConnected(): void
    {
        $_SESSION['gs_connected'] = false;
        
        ob_start();
        render_google_sheets_integration();
        $output = ob_get_clean();

        $this->assertStringContainsString('Connect Google Sheet', $output);
        $this->assertStringContainsString('bg-[#06466b]', $output);
    }

    /**
     * Test "Disconnect" button shows when connected
     */
    public function testDisconnectButtonShowsWhenConnected(): void
    {
        $_SESSION['gs_connected'] = true;
        $_SESSION['gs_connected_sheet'] = 'attendance_export_2026';
        $_SESSION['gs_connected_since'] = '28 May 2026';
        
        ob_start();
        render_google_sheets_integration();
        $output = ob_get_clean();

        $this->assertStringContainsString('Disconnect', $output);
        $this->assertStringContainsString('bg-red-700', $output);
    }

    /**
     * Test connected sheet details display when connected
     */
    public function testConnectedSheetDetailsDisplay(): void
    {
        $_SESSION['gs_connected'] = true;
        $_SESSION['gs_connected_sheet'] = 'attendance_export_2026';
        $_SESSION['gs_connected_since'] = '28 May 2026';
        
        ob_start();
        render_google_sheets_integration();
        $output = ob_get_clean();

        $this->assertStringContainsString('Connected sheet', $output);
        $this->assertStringContainsString('attendance_export_2026', $output);
        $this->assertStringContainsString('Connected since', $output);
        $this->assertStringContainsString('28 May 2026', $output);
        $this->assertStringContainsString('Clock-in &amp; clock-out events', $output);
    }

    /**
     * Test connect modal displays when modal GET parameter is set
     */
    public function testConnectModalDisplaysWithGetParameter(): void
    {
        ob_start();
        render_google_sheets_integration(
            isConnected: false,
            modal: 'connect',
            selfUrl: '/AdminSettingsPage.php'
        );
        $output = ob_get_clean();

        $this->assertStringContainsString('Connect to Google Sheets', $output);
        $this->assertStringContainsString('What will happen:', $output);
        $this->assertStringContainsString('Permissions requested:', $output);
        $this->assertStringContainsString('Confirm &amp; Connect', $output);
    }

    /**
     * Test disconnect modal displays when modal GET parameter is set
     */
    public function testDisconnectModalDisplaysWithGetParameter(): void
    {
        ob_start();
        render_google_sheets_integration(
            isConnected: true,
            modal: 'disconnect',
            selfUrl: '/AdminSettingsPage.php'
        );
        $output = ob_get_clean();

        $this->assertStringContainsString('Disconnect Google Sheets?', $output);
        $this->assertStringContainsString('Are you sure?', $output);
        $this->assertStringContainsString('Yes, disconnect', $output);
    }

    /**
     * Test modal backdrop renders
     */
    public function testModalBackdropRenders(): void
    {
        $_GET['modal'] = 'connect';
        
        ob_start();
        render_google_sheets_integration();
        $output = ob_get_clean();

        $this->assertStringContainsString('bg-black/10', $output);
        $this->assertStringContainsString('backdrop-blur-sm', $output);
    }

    /**
     * Test HTML special characters are escaped
     */
    public function testHTMLSpecialCharactersAreEscaped(): void
    {
        $_SESSION['gs_connected'] = true;
        $_SESSION['gs_connected_sheet'] = '<script>alert("test")</script>';
        
        ob_start();
        render_google_sheets_integration();
        $output = ob_get_clean();

        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    /**
     * Test component includes sync status information
     */
    public function testComponentIncludesSyncStatusInfo(): void
    {
        $_SESSION['gs_connected'] = true;
        $_SESSION['gs_connected_sheet'] = 'attendance_export_2026';
        $_SESSION['gs_connected_since'] = '28 May 2026';
        
        ob_start();
        render_google_sheets_integration();
        $output = ob_get_clean();

        $this->assertStringContainsString('Syncing', $output);
        $this->assertStringContainsString('Last sync', $output);
    }

    /**
     * Test component includes proper Tailwind CSS classes
     */
    public function testComponentIncludesTailwindClasses(): void
    {
        ob_start();
        render_google_sheets_integration();
        $output = ob_get_clean();

        // Check for key Tailwind classes
        $tailwindClasses = [
            'rounded-xl',
            'border',
            'bg-white',
            'px-7',
            'py-7',
            'shadow-sm',
            'flex',
            'gap-5',
            'sm:flex-row',
        ];

        foreach ($tailwindClasses as $class) {
            $this->assertStringContainsString($class, $output);
        }
    }

    /**
     * Test component includes description text
     */
    public function testComponentIncludesDescriptionText(): void
    {
        ob_start();
        render_google_sheets_integration();
        $output = ob_get_clean();

        $this->assertStringContainsString('Two-way sync of attendance data', $output);
    }

    /**
     * Test modal close button is present
     */
    public function testModalCloseButtonIsPresent(): void
    {
        $_GET['modal'] = 'connect';
        
        ob_start();
        render_google_sheets_integration();
        $output = ob_get_clean();

        $this->assertStringContainsString('Close modal', $output);
        $this->assertStringContainsString('sr-only', $output);
    }

    /**
     * Test modals include form submissions
     */
    public function testModalsIncludeFormSubmissions(): void
    {
        $_GET['modal'] = 'connect';
        
        ob_start();
        render_google_sheets_integration();
        $output = ob_get_clean();

        $this->assertStringContainsString('<form', $output);
        $this->assertStringContainsString('method="POST"', $output);
        $this->assertStringContainsString('name="action"', $output);
        $this->assertStringContainsString('value="connect"', $output);
    }
}