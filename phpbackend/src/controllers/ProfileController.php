<?php

namespace Controllers;

use Models\Profile;
use Types\ApiResponse;

class ProfileController
{
    private Profile $profileModel;

    public function __construct(Profile $profileModel)
    {
        $this->profileModel = $profileModel;
    }

    // ─── Helper: send JSON response ──────────────────────────────
    private function json(int $status, array $body): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($body);
    }

    // ─── Helper: capitalize first name ───────────────────────────
    private function capitalizeFirstName(?string $name): ?string
    {
        if ($name === null || $name === '') return $name;
        return strtoupper(substr($name, 0, 1)) . strtolower(substr($name, 1));
    }

    // ─── GET ALL ─────────────────────────────────────────────────
    public function getProfiles(): void
    {
        try {
            $result = $this->profileModel->getProfiles();

            if (!$result->success) {
                $this->json(400, $result->toArray());
                return;
            }

            $this->json(200, $result->toArray());
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── GET BY ID ───────────────────────────────────────────────
    public function getProfileById(string $employeeId): void
    {
        try {
            $result = $this->profileModel->getProfileById($employeeId);

            if (!$result->success) {
                $this->json(400, ['success' => false, 'error' => $result->error]);
                return;
            }

            // Remove sensitive fields before sending
            $safeData = $result->data;
            unset($safeData['password'], $safeData['password_hash'], $safeData['reset_token']);

            $this->json(200, ['success' => true, 'data' => $safeData]);
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── CREATE ──────────────────────────────────────────────────
    public function createProfile(array $body): void
    {
        try {
            $firstName  = $body['first_name']  ?? null;
            $lastName   = $body['last_name']   ?? null;
            $employeeId = $body['employee_id'] ?? null;
            $role       = $body['role']        ?? null;
            $email      = $body['email']       ?? null;

            if (!$firstName || !$lastName || !$employeeId || !$role || !$email) {
                $this->json(400, ['success' => false, 'error' => 'All fields are required']);
                return;
            }

            $result = $this->profileModel->createProfile($firstName, $lastName, $employeeId, $role, $email);

            if (!$result->success) {
                $this->json(400, $result->toArray());
                return;
            }

            $this->json(201, $result->toArray());
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── LOGIN ───────────────────────────────────────────────────
    public function loginProfile(array $body): void
    {
        try {
            $email    = $body['email']    ?? null;
            $password = $body['password'] ?? null;

            if (!$email || !$password) {
                $this->json(400, ['success' => false, 'error' => 'Email and password are required']);
                return;
            }

            $result = $this->profileModel->loginProfile($email);

            if (!$result->success) {
                // Don't expose whether email exists
                $this->json(401, ['success' => false, 'error' => 'Invalid email or password']);
                return;
            }

            $user = $result->data;

            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->json(401, ['success' => false, 'error' => 'Invalid email or password']);
                return;
            }

            // Generate JWT
            $token = $this->generateJwt([
                'userId'      => $user['id'],
                'email'       => $user['email'],
                'role'        => $user['role'],
                'employee_id' => $user['employee_id'],
            ]);

            // Get profile for capitalized first_name
            $profileResult = $this->profileModel->getProfileById($user['employee_id']);
            $firstName = null;
            if ($profileResult->success && !empty($profileResult->data['first_name'])) {
                $firstName = $this->capitalizeFirstName($profileResult->data['first_name']);
            }

            $this->json(200, [
                'success'    => true,
                'token'      => $token,
                'first_name' => $firstName,
                'user'       => [
                    'id'          => $user['id'],
                    'first_name'  => $firstName,
                    'last_name'   => $user['last_name'],
                    'email'       => $user['email'],
                    'employee_id' => $user['employee_id'],
                    'role'        => $user['role'],
                    // password intentionally omitted
                ],
            ]);
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── UPDATE ──────────────────────────────────────────────────
    public function updateProfile(string $employeeId, array $body): void
    {
        try {
            $result = $this->profileModel->updateProfile($employeeId, $body);

            if (!$result->success) {
                $this->json(400, $result->toArray());
                return;
            }

            $this->json(200, $result->toArray());
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── DELETE (SOFT) ───────────────────────────────────────────
    public function deleteProfile(string $employeeId): void
    {
        try {
            $result = $this->profileModel->deleteProfile($employeeId);

            if (!$result->success) {
                $this->json(400, $result->toArray());
                return;
            }

            $this->json(200, $result->toArray());
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── RESET PASSWORD (Admin) ───────────────────────────────────
    public function resetPassword(string $employeeId): void
    {
        try {
            $result = $this->profileModel->resetPassword($employeeId);

            if (!$result->success) {
                $this->json(400, $result->toArray());
                return;
            }

            $this->json(200, $result->toArray());
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── UPDATE PASSWORD (Staff) ──────────────────────────────────
    public function updatePassword(string $employeeId, array $body, array $user): void
    {
        try {
            $oldPassword = $body['oldPassword'] ?? null;
            $newPassword = $body['newPassword'] ?? null;

            if (!$oldPassword || !$newPassword) {
                $this->json(400, ['success' => false, 'error' => 'Old password and new password are required']);
                return;
            }

            // Fetch user to verify old password
            $userResult = $this->profileModel->loginProfile($user['email']);

            if (!$userResult->success) {
                $this->json(404, ['success' => false, 'error' => 'User not found']);
                return;
            }

            if (!password_verify($oldPassword, $userResult->data['password'])) {
                $this->json(401, ['success' => false, 'error' => 'Old password is incorrect']);
                return;
            }

            // Hash new password — controller hashes, model just stores
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            $result = $this->profileModel->updatePassword($employeeId, $hashedPassword);

            if (!$result->success) {
                $this->json(400, $result->toArray());
                return;
            }

            $this->json(200, ['success' => true, 'message' => 'Password updated successfully']);
        } catch (\Throwable $e) {
            $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── JWT Helper ──────────────────────────────────────────────
    private function generateJwt(array $payload): string
    {
        $secret  = $_ENV['JWT_SECRET'] ?? 'secret';
        $header  = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode(array_merge($payload, ['exp' => time() + 7200])));
        $sig     = base64_encode(hash_hmac('sha256', "{$header}.{$payload}", $secret, true));

        return "{$header}.{$payload}.{$sig}";
    }
  
  
      public function clearCache($request, $response)
    {
        try {
            $user = $request->getAttribute('user');
            $employeeId = $user['employee_id'] ?? null;

            if (!$employeeId) {
                return $response->withStatus(401)->withJson([
                    'success' => false,
                    'error' => 'User must be logged in to clear cache'
                ]);
            }

            $result = $this->profileDb->clearCache($employeeId);

            if (!$result['success']) {
                return $response->withStatus(400)->withJson($result);
            }

            return $response->withStatus(200)->withJson($result);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
