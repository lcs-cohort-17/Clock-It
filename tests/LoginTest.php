<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LoginTest extends TestCase
{
    private function renderLoginPage(): string
    {
        $title = 'Login | Clock-It';

        ob_start();
        require __DIR__ . '/../src/views/Login.php';

        return (string) ob_get_clean();
    }

    public function testLoginPageRendersRequiredBootstrapFormElements(): void
    {
        $html = $this->renderLoginPage();

        $this->assertStringContainsString('Clock It', $html);
        $this->assertStringContainsString('id="email"', $html);
        $this->assertStringContainsString('type="email"', $html);
        $this->assertStringContainsString('class="form-control form-control-lg"', $html);
        $this->assertStringContainsString('id="password"', $html);
        $this->assertStringContainsString('type="password"', $html);
        $this->assertStringContainsString('id="rememberMe"', $html);
        $this->assertStringContainsString('class="form-check-input"', $html);
        $this->assertStringContainsString('id="submitLogin"', $html);
        $this->assertStringContainsString('class="submit-button btn btn-primary btn-lg w-100"', $html);
    }

    public function testForgotPasswordModalUsesBootstrapAndSecurityMessage(): void
    {
        $html = $this->renderLoginPage();
        $js = file_get_contents(__DIR__ . '/../public/assets/js/login.js');

        $this->assertStringContainsString('data-bs-toggle="modal"', $html);
        $this->assertStringContainsString('data-bs-target="#forgotPasswordModal"', $html);
        $this->assertStringContainsString('class="modal fade"', $html);
        $this->assertStringContainsString('id="forgotEmail"', $html);
        $this->assertStringContainsString('class="form-control"', $html);
        $this->assertStringContainsString('id="sendResetLink"', $html);
        $this->assertStringContainsString('class="btn btn-primary"', $html);
        $this->assertStringContainsString("If that email exists in our system, we've sent a password reset link.", (string) $js);
    }

    public function testAlpineLoginStateCallsDemoApisAndStoresRememberedEmail(): void
    {
        $html = $this->renderLoginPage();
        $js = file_get_contents(__DIR__ . '/../public/assets/js/login.js');

        $this->assertStringContainsString('x-data="clockitLogin()"', $html);
        $this->assertStringContainsString('@submit.prevent="submitLogin"', $html);
        $this->assertStringContainsString('@submit.prevent="submitForgotPassword"', $html);
        $this->assertStringContainsString('/api/login', (string) $js);
        $this->assertStringContainsString('/api/forgot-password', (string) $js);
        $this->assertStringContainsString('localStorage.setItem', (string) $js);
        $this->assertStringContainsString('validateLogin', (string) $js);
        $this->assertStringContainsString('validateForgotPassword', (string) $js);
    }

    public function testDemoCredentialsAndRouterTargetsAreAvailable(): void
    {
        $html = $this->renderLoginPage();
        $router = file_get_contents(__DIR__ . '/../public/index.php');
        $mockUsers = require __DIR__ . '/../src/data/MockUsers.php';

        $this->assertStringContainsString('taaraa@clockit.com', $html);
        $this->assertStringContainsString('admin123', $html);
        $this->assertStringContainsString('shaheed@clockit.com', $html);
        $this->assertStringContainsString('staff123', $html);
        $this->assertSame('admin', $mockUsers[0]['role']);
        $this->assertSame('staff', $mockUsers[1]['role']);
        $this->assertStringContainsString("case '/'", (string) $router);
        $this->assertStringContainsString("case '/login'", (string) $router);
        $this->assertStringContainsString("'/admin-dashboard'", (string) $router);
        $this->assertStringContainsString("'/staff-dashboard'", (string) $router);
    }
}
