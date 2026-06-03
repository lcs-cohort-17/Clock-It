<?php
 
use PHPUnit\Framework\TestCase;
use App\Middleware\AuthMiddleware;
 
class SettingsControllerTest extends TestCase
{
    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------
 
    private function makeModel(array $overrides = []): SettingsModel
    {
        $model = $this->createMock(SettingsModel::class);
 
        $model->method('findActiveUser')->willReturn(
            $overrides['user'] ?? ['user_id' => '1', 'role' => 'admin', 'is_active' => 1]
        );
        $model->method('getAllSettings')->willReturn(
            $overrides['settings'] ?? [
                'session_timeout_minutes' => '30',
                'data_retention_days'     => '90',
            ]
        );
        $model->method('getSetting')->willReturnCallback(
            fn($key) => ($overrides['settings'] ?? [
                'session_timeout_minutes' => '30',
                'data_retention_days'     => '90',
            ])[$key] ?? null
        );
        $model->method('upsertSetting')->willReturn(true);
        $model->method('purgeAttendance')->willReturn($overrides['deleted'] ?? 5);
 
        return $model;
    }
 
    private function makeAuth(bool $isAdmin = true): AuthMiddleware
    {
        $auth = $this->createMock(AuthMiddleware::class);
        $auth->method('requireAdminFromAuth')->willReturn(
            $isAdmin
                ? null
                : ['status' => 403, 'body' => ['success' => false, 'error' => 'Admin privileges required']]
        );
 
        return $auth;
    }
 
    private function adminAuth(): array
    {
        return ['userId' => '1', 'role' => 'admin'];
    }
 
    private function staffAuth(): array
    {
        return ['userId' => '2', 'role' => 'staff'];
    }
 
    // ----------------------------------------------------------------
    // GET /api/admin/settings
    // ----------------------------------------------------------------
 
    public function test_get_settings_returns_200_with_integers(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth());
 
        ob_start();
        $controller->getSettings($this->adminAuth());
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame(30, $output['session_timeout_minutes']);
        $this->assertSame(90, $output['data_retention_days']);
    }
 
    public function test_get_settings_blocked_for_non_admin(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth(false));
 
        ob_start();
        $controller->getSettings($this->staffAuth());
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Admin privileges required', $output['error']);
    }
 
    public function test_get_settings_blocked_for_stale_token(): void
    {
        // JWT says admin but DB row says staff
        $model = $this->makeModel([
            'user' => ['user_id' => '1', 'role' => 'staff', 'is_active' => 1],
        ]);
        $controller = new SettingsController($model, $this->makeAuth());
 
        ob_start();
        $controller->getSettings($this->adminAuth());
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Forbidden', $output['message']);
    }
 
    public function test_get_settings_blocked_for_inactive_user(): void
    {
        $model = $this->makeModel([
            'user' => ['user_id' => '1', 'role' => 'admin', 'is_active' => 0],
        ]);
        $controller = new SettingsController($model, $this->makeAuth());
 
        ob_start();
        $controller->getSettings($this->adminAuth());
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Forbidden', $output['message']);
    }
 
    // ----------------------------------------------------------------
    // PUT /api/admin/settings
    // ----------------------------------------------------------------
 
    public function test_update_settings_succeeds_with_both_fields(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth());
 
        ob_start();
        $controller->updateSettings($this->adminAuth(), [
            'session_timeout_minutes' => 60,
            'data_retention_days'     => 180,
        ]);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Settings updated', $output['message']);
    }
 
    public function test_update_settings_succeeds_with_one_field(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth());
 
        ob_start();
        $controller->updateSettings($this->adminAuth(), ['data_retention_days' => 60]);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Settings updated', $output['message']);
    }
 
    public function test_update_settings_rejects_zero(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth());
 
        ob_start();
        $controller->updateSettings($this->adminAuth(), ['data_retention_days' => 0]);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Validation failed', $output['message']);
        $this->assertArrayHasKey('data_retention_days', $output['errors']);
    }
 
    public function test_update_settings_rejects_negative(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth());
 
        ob_start();
        $controller->updateSettings($this->adminAuth(), ['session_timeout_minutes' => -5]);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Validation failed', $output['message']);
        $this->assertArrayHasKey('session_timeout_minutes', $output['errors']);
    }
 
    public function test_update_settings_rejects_non_numeric_string(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth());
 
        ob_start();
        $controller->updateSettings($this->adminAuth(), ['data_retention_days' => 'abc']);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Validation failed', $output['message']);
        $this->assertArrayHasKey('data_retention_days', $output['errors']);
    }
 
    public function test_update_settings_rejects_empty_body(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth());
 
        ob_start();
        $controller->updateSettings($this->adminAuth(), []);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Validation failed', $output['message']);
        $this->assertArrayHasKey('body', $output['errors']);
    }
 
    public function test_update_settings_blocked_for_non_admin(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth(false));
 
        ob_start();
        $controller->updateSettings($this->staffAuth(), ['data_retention_days' => 60]);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Admin privileges required', $output['error']);
    }
 
    // ----------------------------------------------------------------
    // POST /api/admin/data-retention/purge
    // ----------------------------------------------------------------
 
    public function test_purge_returns_deleted_count(): void
    {
        $controller = new SettingsController($this->makeModel(['deleted' => 42]), $this->makeAuth());
 
        ob_start();
        $controller->purge($this->adminAuth(), ['confirm' => true, 'days' => 30]);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame(42, $output['deleted_count']);
    }
 
    public function test_purge_uses_settings_days_when_days_omitted(): void
{
    $model = $this->createMock(SettingsModel::class);

    $model->method('findActiveUser')
          ->willReturn(['user_id' => '1', 'role' => 'admin', 'is_active' => 1]);

    $model->expects($this->once())
          ->method('getSetting')
          ->with('data_retention_days')
          ->willReturn('90');

    $model->method('purgeAttendance')
          ->willReturn(10);

    $controller = new SettingsController($model, $this->makeAuth());

    ob_start();
    $controller->purge($this->adminAuth(), ['confirm' => true]);
    $output = json_decode(ob_get_clean(), true);

    $this->assertSame(10, $output['deleted_count']);
}
 
    public function test_purge_fails_without_confirm_key(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth());
 
        ob_start();
        $controller->purge($this->adminAuth(), ['days' => 30]);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Validation failed', $output['message']);
        $this->assertArrayHasKey('confirm', $output['errors']);
    }
 
    public function test_purge_fails_with_confirm_false(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth());
 
        ob_start();
        $controller->purge($this->adminAuth(), ['confirm' => false, 'days' => 30]);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Validation failed', $output['message']);
        $this->assertArrayHasKey('confirm', $output['errors']);
    }
 
    public function test_purge_fails_with_confirm_string_true(): void
    {
        // "true" as a string should not pass — must be boolean true
        $controller = new SettingsController($this->makeModel(), $this->makeAuth());
 
        ob_start();
        $controller->purge($this->adminAuth(), ['confirm' => 'true', 'days' => 30]);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Validation failed', $output['message']);
    }
 
    public function test_purge_rejects_negative_days(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth());
 
        ob_start();
        $controller->purge($this->adminAuth(), ['confirm' => true, 'days' => -10]);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Validation failed', $output['message']);
        $this->assertArrayHasKey('days', $output['errors']);
    }
 
    public function test_purge_rejects_zero_days(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth());
 
        ob_start();
        $controller->purge($this->adminAuth(), ['confirm' => true, 'days' => 0]);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Validation failed', $output['message']);
        $this->assertArrayHasKey('days', $output['errors']);
    }
 
    public function test_purge_rejects_non_numeric_days(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth());
 
        ob_start();
        $controller->purge($this->adminAuth(), ['confirm' => true, 'days' => 'abc']);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Validation failed', $output['message']);
        $this->assertArrayHasKey('days', $output['errors']);
    }
 
    public function test_purge_blocked_for_non_admin(): void
    {
        $controller = new SettingsController($this->makeModel(), $this->makeAuth(false));
 
        ob_start();
        $controller->purge($this->staffAuth(), ['confirm' => true, 'days' => 30]);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame('Admin privileges required', $output['error']);
    }
 
    public function test_purge_returns_zero_when_nothing_to_delete(): void
    {
        $controller = new SettingsController($this->makeModel(['deleted' => 0]), $this->makeAuth());
 
        ob_start();
        $controller->purge($this->adminAuth(), ['confirm' => true, 'days' => 9999]);
        $output = json_decode(ob_get_clean(), true);
 
        $this->assertSame(0, $output['deleted_count']);
    }
}