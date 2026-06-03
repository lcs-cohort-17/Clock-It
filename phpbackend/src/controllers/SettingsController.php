<?php
 
use App\Middleware\AuthMiddleware;
 
class SettingsController
{
    public function __construct(private SettingsModel $model, private AuthMiddleware $auth) {}
 
    // GET /api/admin/settings
    // Returns session_timeout_minutes and data_retention_days cast to integers
    public function getSettings(array $auth): void
    {
        try {
            $guard = $this->auth->requireAdminFromAuth($auth);
            if ($guard !== null) {
                $this->respond($guard['status'], $guard['body']);
                return;
            }
 
            // Cross-check token role against the database to guard against stale tokens
            $user = $this->model->findActiveUser((string) ($auth['userId'] ?? ''));
            if ($user === null || empty($user['is_active']) || ($user['role'] ?? null) !== 'admin') {
                $this->respond(403, ['message' => 'Forbidden']);
                return;
            }
 
            $settings = $this->model->getAllSettings();
 
            $this->respond(200, [
                'session_timeout_minutes' => isset($settings['session_timeout_minutes'])
                    ? (int) $settings['session_timeout_minutes']
                    : null,
                'data_retention_days' => isset($settings['data_retention_days'])
                    ? (int) $settings['data_retention_days']
                    : null,
            ]);
        } catch (Throwable $exception) {
            $this->respond(500, [
                'message' => 'Internal Server Error',
                'error'   => $exception->getMessage(),
            ]);
        }
    }
 
    // PUT /api/admin/settings
    // Updates session_timeout_minutes and/or data_retention_days
    // Rejects non-numeric, zero, and negative values
    public function updateSettings(array $auth, array $body): void
    {
        try {
            $guard = $this->auth->requireAdminFromAuth($auth);
            if ($guard !== null) {
                $this->respond($guard['status'], $guard['body']);
                return;
            }
 
            // Cross-check token role against the database to guard against stale tokens
            $user = $this->model->findActiveUser((string) ($auth['userId'] ?? ''));
            if ($user === null || empty($user['is_active']) || ($user['role'] ?? null) !== 'admin') {
                $this->respond(403, ['message' => 'Forbidden']);
                return;
            }
 
            $errors = SettingsValidator::validateUpdate($body);
            if ($errors !== []) {
                $this->respond(400, [
                    'message' => 'Validation failed',
                    'errors'  => $errors,
                ]);
                return;
            }
 
            $allowed = ['session_timeout_minutes', 'data_retention_days'];
            foreach ($allowed as $key) {
                if (array_key_exists($key, $body)) {
                    $this->model->upsertSetting($key, (string)(int) $body[$key]);
                }
            }
 
            $updated = $this->model->getAllSettings();
 
            $this->respond(200, [
                'message'                 => 'Settings updated',
                'session_timeout_minutes' => (int) ($updated['session_timeout_minutes'] ?? 0),
                'data_retention_days'     => (int) ($updated['data_retention_days'] ?? 0),
            ]);
        } catch (Throwable $exception) {
            $this->respond(500, [
                'message' => 'Internal Server Error',
                'error'   => $exception->getMessage(),
            ]);
        }
    }
 
    // POST /api/admin/data-retention/purge
    // Deletes attendance records older than `days` (falls back to data_retention_days setting)
    // Requires confirm: true in body
    public function purge(array $auth, array $body): void
    {
        try {
            $guard = $this->auth->requireAdminFromAuth($auth);
            if ($guard !== null) {
                $this->respond($guard['status'], $guard['body']);
                return;
            }
 
            // Cross-check token role against the database to guard against stale tokens
            $user = $this->model->findActiveUser((string) ($auth['userId'] ?? ''));
            if ($user === null || empty($user['is_active']) || ($user['role'] ?? null) !== 'admin') {
                $this->respond(403, ['message' => 'Forbidden']);
                return;
            }
 
            $errors = SettingsValidator::validatePurge($body);
            if ($errors !== []) {
                $this->respond(400, [
                    'message' => 'Validation failed',
                    'errors'  => $errors,
                ]);
                return;
            }
 
            // Use provided days or fall back to the stored data_retention_days setting
            if (isset($body['days'])) {
                $days = (int) $body['days'];
            } else {
                $stored = $this->model->getSetting('data_retention_days');
                if ($stored === null) {
                    $this->respond(500, ['message' => 'data_retention_days setting not found']);
                    return;
                }
                $days = (int) $stored;
            }
 
            $deletedCount = $this->model->purgeAttendance($days);
 
            $this->respond(200, ['deleted_count' => $deletedCount]);
        } catch (Throwable $exception) {
            $this->respond(500, [
                'message' => 'Internal Server Error',
                'error'   => $exception->getMessage(),
            ]);
        }
    }
 
    // Keeps every response consistent — mirrors LeaveController::respond()
    private function respond(int $status, array $payload): void
    {
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    }
}