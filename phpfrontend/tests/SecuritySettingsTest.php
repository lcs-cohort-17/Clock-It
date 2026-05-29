<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class SecuritySettingsTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        require_once __DIR__ . '/../src/views/partials/settings_features.php';

        $_SESSION['security_timeout'] = 30;
        unset($_SESSION['security_error'], $_SESSION['security_success']);
    }

    public function testSecuritySettingsCardRenders(): void
    {
        ob_start();
        render_security_settings_card();
        $output = ob_get_clean();

        $this->assertStringContainsString('Security Settings', $output);
        $this->assertStringContainsString('Session timeout (minutes)', $output);
        $this->assertStringContainsString('Require strong passwords', $output);
        $this->assertStringContainsString('Enable two-factor authentication', $output);
        $this->assertStringContainsString('Save Security Settings', $output);
    }

    public function testStrongPasswordsCheckboxIsCheckedByDefault(): void
    {
        ob_start();
        render_security_settings_card();
        $output = ob_get_clean();

        $this->assertStringContainsString('<input type="checkbox" checked>', $output);
    }

    public function testTimeoutFieldDisplaysDefaultValue(): void
    {
        ob_start();
        render_security_settings_card();
        $output = ob_get_clean();

        $this->assertStringContainsString('name="security_timeout"', $output);
        $this->assertStringContainsString('value="30"', $output);
    }

    public function testSavedTimeoutValueDisplaysFromSession(): void
    {
        $_SESSION['security_timeout'] = 45;

        ob_start();
        render_security_settings_card();
        $output = ob_get_clean();

        $this->assertStringContainsString('value="45"', $output);
    }

    public function testInvalidTimeoutShowsClearErrorMessage(): void
    {
        $_SESSION['security_error'] = 'Please enter a positive whole number for the session timeout.';

        ob_start();
        render_security_settings_card();
        $output = ob_get_clean();

        $this->assertStringContainsString('Please enter a positive whole number for the session timeout.', $output);
        $this->assertStringContainsString('alert error', $output);
    }

    public function testSuccessfulSaveShowsSuccessMessage(): void
    {
        $_SESSION['security_success'] = 'Security settings saved successfully.';

        ob_start();
        render_security_settings_card();
        $output = ob_get_clean();

        $this->assertStringContainsString('Security settings saved successfully.', $output);
        $this->assertStringContainsString('alert success', $output);
    }
}
