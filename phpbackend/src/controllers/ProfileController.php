<?php

namespace Controllers;

use App\Models\ProfileDb;
use Types\ProfileModelInterface;
use Types\ApiResponse;

class ProfileController
{
    private ProfileModelInterface $profileModel;

    public function __construct(ProfileModelInterface $profileModel)
    {
        $this->profileModel = $profileModel;
    }
    
    private function json(int $status, array $body): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($body);
    }

    private function capitalizeFirstName(?string $first_name): ?string
    {
        if ($first_name === null || $first_name === '') {
            return $first_name;
        }
        return strtoupper(substr($first_name, 0, 1)) . strtolower(substr($first_name, 1));
    }

    private function generateJwt(array $payload): string
    {
        $secret = $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this';
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload['exp'] = time() + 7200;
        $payloadEncoded = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "{$header}.{$payloadEncoded}", $secret, true);
        $signatureEncoded = base64_encode($signature);
        
        return "{$header}.{$payloadEncoded}.{$signatureEncoded}";
    }

    // ─── GET ALL USERS ─────────────────────────────────────────────
    public function adminGettingAllUsers(): void
    {
        try {
            $result = $this->profileModel->adminGettingAllUsersDb();

            if (!$result->isSuccess()) {
                $this->json(400, $result->toArray());
                return;
            }

            $this->json(200, $result->toArray());
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── GET USER BY ID ────────────────────────────────────────────
    public function getProfileById(string $employee_id): void
    {
        try {
            // Pass the value - variable name can be different from parameter name
            $result = $this->profileModel->getProfileByIdDb($employee_id);

            if (!$result->isSuccess()) {
                $this->json(404, ['success' => false, 'error' => $result->getError()]);
                return;
            }

            $safeData = $result->data;
            unset($safeData['password']);

            $this->json(200, ['success' => true, 'data' => $safeData]);
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── CREATE USER ───────────────────────────────────────────────
    public function adminCreatingUser(array $body): void
    {
        try {
            $first_name = $body['first_name']  ?? null;
            $last_name  = $body['last_name']   ?? null;
            $employee_id = $body['employee_id'] ?? null;
            $role       = $body['role']        ?? null;
            $email      = $body['email']       ?? null;
            $img        = $body['img']         ?? null;  // ← ADDED img

            if (!$first_name || !$last_name || !$employee_id || !$role || !$email) {
                $this->json(400, [
                    'success' => false, 
                    'error' => 'All fields are required: first_name, last_name, employee_id, role, email'
                ]);
                return;
            }

            // Pass img as the 6th parameter to match your model
            $result = $this->profileModel->adminCreatingUserDb(
                $first_name, 
                $last_name, 
                $employee_id, 
                $role, 
                $email,
                $img  // ← ADDED img parameter
            );

            if (!$result->isSuccess()) {
                $this->json(400, $result->toArray());
                return;
            }

            $this->json(201, $result->toArray());
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

// ─── LOGIN ─────────────────────────────────────────────────────
public function loginProfile(array $body): void
{
    try {
        $email    = $body['email']    ?? null;
        $password = $body['password'] ?? null;

        if (!$email || !$password) {
            $this->json(400, [
                'success' => false, 
                'error' => 'Email and password are required'
            ]);
            return;
        }

        $result = $this->profileModel->loginProfileDb($email);

        if (!$result->isSuccess()) {
            $this->json(401, [
                'success' => false, 
                'error' => 'Invalid email or password'
            ]);
            return;
        }

        $user = $result->data;

        if (!password_verify($password, $user['password'])) {
            $this->json(401, [
                'success' => false, 
                'error' => 'Invalid email or password'
            ]);
            return;
        }

        // ADD last_activity to the token payload!
        $token = $this->generateJwt([
            'user_id'       => $user['user_id'],
            'email'         => $user['email'],
            'role'          => $user['role'],
            'employee_id'   => $user['employee_id'],
            'last_activity' => time(),  // ← ADD THIS LINE!
        ]);

        $first_name = $user['first_name'] ? $this->capitalizeFirstName($user['first_name']) : null;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['current_user'] = [
            'id'         => $user['user_id'],
            'name'       => ($first_name ? $first_name : '') . ($user['last_name'] ? ' ' . $user['last_name'] : ''),
            'email'      => $user['email'],
            'employeeId' => $user['employee_id'],
            'role'       => $user['role'],
        ];

        // Check if user must change their password on first login
        $mustChangePassword = (int)($user['must_change_password'] ?? 0) === 1;

        $this->json(200, [
            'success'              => true,
            'token'                => $token,
            'first_name'           => $first_name,
            'must_change_password' => $mustChangePassword,
            'user'                 => [
                'user_id'     => $user['user_id'],
                'first_name'  => $first_name,
                'last_name'   => $user['last_name'],
                'email'       => $user['email'],
                'employee_id' => $user['employee_id'],
                'role'        => $user['role'],
                'img'         => $user['img'] ?? null,
            ]
        ]);
    } catch (\Throwable $e) {
        $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
    }
}

        // ─── GET CURRENT USER PROFILE (from JWT token) ────────────────────
    public function getCurrentUserProfile(array $request): void
    {
        try {
            // Get authenticated user from request (set by AuthMiddleware)
            $user = $request['user'] ?? null;
            
            if (!$user) {
                $this->json(401, [
                    'success' => false,
                    'error' => 'Unauthorized: Please login first'
                ]);
                return;
            }
            
            $employee_id = $user['employee_id'] ?? null;
            
            if (!$employee_id) {
                $this->json(401, [
                    'success' => false,
                    'error' => 'Invalid user data'
                ]);
                return;
            }
            
            $result = $this->profileModel->getCurrentUserProfileDb($employee_id);
            
            if (!$result->isSuccess()) {
                $this->json(404, $result->toArray());
                return;
            }
            
            $this->json(200, [
                'success' => true,
                'data' => $result->data
            ]);
            
        } catch (\Throwable $e) {
            $this->json(500, [
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // ─── UPDATE USER ───────────────────────────────────────────────
    public function adminUpdatingUser(string $employee_id, array $body): void
    {
        try {
            // IMPORTANT: Your table uses 'id', not 'user_id'
            unset($body['user_id'], $body['password'], $body['created_at'], $body['updated_at']);

            $result = $this->profileModel->adminUpdatingUserDb($employee_id, $body);

            if (!$result->isSuccess()) {
                $this->json(400, $result->toArray());
                return;
            }

            $this->json(200, $result->toArray());
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── DELETE USER ───────────────────────────────────────────────
    public function adminDeletingUser(string $employee_id): void
    {
        try {
            $result = $this->profileModel->adminDeletingUserDb($employee_id);

            if (!$result->isSuccess()) {
                $this->json(400, $result->toArray());
                return;
            }

            $this->json(200, $result->toArray());
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

        // ─── SOFT DELETE (SET is_active = 0) ───────────────────────────────
    public function softDeleteUser(string $employee_id): void
    {
        try {
            $result = $this->profileModel->softDeleteUserDb($employee_id, false);

            if (!$result->isSuccess()) {
                $this->json(400, $result->toArray());
                return;
            }

            $this->json(200, $result->toArray());
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }
        // ─── ACTIVATE USER (SET is_active = 1) ─────────────────────────────
    public function activateUser(string $employee_id): void
    {
        try {
            $result = $this->profileModel->softDeleteUserDb($employee_id, true);

            if (!$result->isSuccess()) {
                $this->json(400, $result->toArray());
                return;
            }

            $this->json(200, $result->toArray());
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── RESET PASSWORD ────────────────────────────────────────────
    public function resetPassword(string $employee_id): void
    {
        try {
            $result = $this->profileModel->resetPasswordDb($employee_id);

            if (!$result->isSuccess()) {
                $this->json(400, $result->toArray());
                return;
            }

            $this->json(200, $result->toArray());
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── UPDATE OWN PASSWORD ───────────────────────────────────────
    public function updatePassword(string $employee_id, array $body): void
    {
        try {
            // Change to snake_case to match requirement
            $oldPassword = $body['old_password'] ?? $body['oldPassword'] ?? null;  // Support both
            $newPassword = $body['new_password'] ?? $body['newPassword'] ?? null;  // Support both
            $email = $body['email'] ?? null;

            if (!$oldPassword || !$newPassword) {
                $this->json(400, [
                    'success' => false, 
                    'error' => 'old_password and new_password are required'
                ]);
                return;
            }

            if (!$email) {
                $this->json(400, [
                    'success' => false, 
                    'error' => 'Email is required'
                ]);
                return;
            }

            $userResult = $this->profileModel->loginProfileDb($email);

            if (!$userResult->isSuccess()) {
                $this->json(404, [
                    'success' => false, 
                    'error' => 'User not found'
                ]);
                return;
            }

            if (!password_verify($oldPassword, $userResult->data['password'])) {
                $this->json(401, [
                    'success' => false, 
                    'error' => 'Old password is incorrect'
                ]);
                return;
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $result = $this->profileModel->updatePasswordDb($employee_id, $hashedPassword);

            if (!$result->isSuccess()) {
                $this->json(400, $result->toArray());
                return;
            }

            $this->json(200, [
                'success' => true, 
                'message' => 'Password updated successfully'
            ]);
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── CLEAR CACHE ───────────────────────────────────────────────
        public function clearCache(array $requestData, ?array $user = null): void
        {
            try {
                // Get employee_id from authenticated user
                $employee_id = $user['employee_id'] ?? null;

                if (!$employee_id) {
                    $this->json(401, [
                        'success' => false, 
                        'error' => 'User must be logged in to clear cache'
                    ]);
                    return;
                }

                $result = $this->profileModel->clearCacheDb($employee_id);

                if (!$result->isSuccess()) {
                    $this->json(400, $result->toArray());
                    return;
                }

                $this->json(200, $result->toArray());
            } catch (\Throwable $e) {
                $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
            }
        }

        // ─── FORGOT PASSWORD ────────────────────────────────────────────
public function forgotPassword(array $body): void
{
    try {
        $email = $body['email'] ?? null;
        
        if (!$email) {
            $this->json(400, [
                'success' => false,
                'error' => 'Email is required'
            ]);
            return;
        }
        
        $result = $this->profileModel->forgotPasswordDb($email);
        
        if (!$result->isSuccess()) {
            $this->json(400, $result->toArray());
            return;
        }
        
        $this->json(200, $result->toArray());
        
    } catch (\Throwable $e) {
        $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
    }
}

// ─── RESET PASSWORD (with token) ─────────────────────────────────
public function resetPasswordWithToken(array $body): void
{
    try {
        $token = $body['token'] ?? null;
        $newPassword = $body['new_password'] ?? null;
        $confirmPassword = $body['confirm_password'] ?? null;
        
        if (!$token || !$newPassword) {
            $this->json(400, [
                'success' => false,
                'error' => 'Token and new password are required'
            ]);
            return;
        }
        
        if ($newPassword !== $confirmPassword) {
            $this->json(400, [
                'success' => false,
                'error' => 'Passwords do not match'
            ]);
            return;
        }
        
        if (strlen($newPassword) < 8) {
            $this->json(400, [
                'success' => false,
                'error' => 'Password must be at least 8 characters'
            ]);
            return;
        }
        
        $result = $this->profileModel->resetPasswordWithTokenDb($token, $newPassword);
        
        if (!$result->isSuccess()) {
            $this->json(400, $result->toArray());
            return;
        }
        
        $this->json(200, $result->toArray());
        
    } catch (\Throwable $e) {
        $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
    }
}
}