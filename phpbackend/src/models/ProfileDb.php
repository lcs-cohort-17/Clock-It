<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Types\ApiResponse;
use PDO;
use PDOException;

class ProfileDb
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function getProfiles(): ApiResponse
    {
        $result = $this->getProfilesDb();
        return $this->responseFromArray($result);
    }

    public function getProfilesDb(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM profiles");
            $stmt->execute();

            return ['success' => true, 'data' => $stmt->fetchAll() ?: []];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createProfile(string $firstName, string $lastName, string $employeeId, string $role, string $email): ApiResponse
    {
        return $this->responseFromArray($this->createProfileDb($firstName, $lastName, $employeeId, $role, $email));
    }

    public function createProfileDb(string $firstName, string $lastName, string $employeeId, string $role, string $email): array
    {
        $validation = $this->validateRoleEmployeeId($role, $employeeId);
        if ($validation !== null) {
            return ['success' => false, 'error' => $validation];
        }

        $plainPassword = $this->generatePassword();
        $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO profiles (first_name, last_name, employee_id, role, email, password, is_active)
                 VALUES (:first_name, :last_name, :employee_id, :role, :email, :password, 1)"
            );
            $stmt->execute([
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':employee_id' => $employeeId,
                ':role' => $role,
                ':email' => $email,
                ':password' => $hashedPassword,
            ]);

            $fetch = $this->db->prepare("SELECT * FROM profiles WHERE employee_id = :employee_id");
            $fetch->execute(['employee_id' => $employeeId]);
            $profile = $fetch->fetch() ?: [];
            $profile['password'] = $plainPassword;

            return ['success' => true, 'data' => $profile];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function loginProfile(string $email): ApiResponse
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM profiles WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $profile = $stmt->fetch();

            if (!$profile) {
                return ApiResponse::fail('User not found');
            }

            return ApiResponse::ok($profile);
        } catch (PDOException $e) {
            return ApiResponse::fail($e->getMessage());
        }
    }

    public function getProfileById(string $employeeId): ApiResponse
    {
        return $this->responseFromArray($this->getProfileByIdDb($employeeId));
    }

    public function getProfileByIdDb(string $employeeId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM profiles WHERE employee_id = :employee_id");
            $stmt->execute(['employee_id' => $employeeId]);
            $profile = $stmt->fetch();

            if (!$profile) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

            if (array_key_exists('first_name', $profile) && $profile['first_name'] !== null && $profile['first_name'] !== '') {
                $profile['first_name'] = ucfirst(strtolower($profile['first_name']));
            }

            return ['success' => true, 'data' => $profile];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateProfile(string $employeeId, array $body): ApiResponse
    {
        return $this->responseFromArray($this->updateProfileDb($employeeId, $body));
    }

    public function updateProfileDb(string $employeeId, array $body): array
    {
        if ($body === []) {
            return ['success' => false, 'error' => 'No fields provided for update'];
        }

        try {
            $fields = [];
            $params = ['employee_id' => $employeeId];

            foreach ($body as $key => $value) {
                $fields[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }

            $stmt = $this->db->prepare('UPDATE profiles SET ' . implode(', ', $fields) . ' WHERE employee_id = :employee_id');
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

            return $this->getProfileByIdDb($employeeId);
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteProfile(string $employeeId): ApiResponse
    {
        return $this->responseFromArray($this->deleteProfileDb($employeeId));
    }

    public function deleteProfileDb(string $employeeId): array
    {
        try {
            $stmt = $this->db->prepare("UPDATE profiles SET is_active = 0 WHERE employee_id = :employee_id");
            $stmt->execute(['employee_id' => $employeeId]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

            $fetch = $this->db->prepare("SELECT * FROM profiles WHERE employee_id = :employee_id");
            $fetch->execute(['employee_id' => $employeeId]);

            return ['success' => true, 'data' => $fetch->fetch() ?: null, 'message' => 'profile deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function resetPassword(string $employeeId): ApiResponse
    {
        return $this->responseFromArray($this->resetPasswordDb($employeeId));
    }

    public function resetPasswordDb(string $employeeId): array
    {
        return $this->updatePasswordRecord($employeeId, $this->generatePassword(), true);
    }

    public function updatePassword(string $employeeId, string $hashedPassword): ApiResponse
    {
        return $this->responseFromArray($this->updatePasswordDb($employeeId, $hashedPassword));
    }

    public function updatePasswordDb(string $employeeId, string $hashedPassword): array
    {
        return $this->updatePasswordRecord($employeeId, $hashedPassword, false);
    }

    public function clearCache(string $employeeId): array
    {
        return ['success' => true, 'message' => "Cache cleared successfully for {$employeeId}"];
    }

    private function updatePasswordRecord(string $employeeId, string $password, bool $returnPlainPassword): array
    {
        $storedPassword = $returnPlainPassword ? password_hash($password, PASSWORD_BCRYPT) : $password;

        try {
            $stmt = $this->db->prepare("UPDATE profiles SET password = :password WHERE employee_id = :employee_id");
            $stmt->execute(['password' => $storedPassword, 'employee_id' => $employeeId]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

            $fetch = $this->db->prepare("SELECT * FROM profiles WHERE employee_id = :employee_id");
            $fetch->execute(['employee_id' => $employeeId]);
            $profile = $fetch->fetch() ?: [];
            $profile['password'] = $returnPlainPassword ? $password : $storedPassword;

            return ['success' => true, 'data' => $profile];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function validateRoleEmployeeId(string $role, string $employeeId): ?string
    {
        if (!in_array($role, ['staff', 'admin'], true)) {
            return 'Role must be either staff or admin';
        }

        if ($role === 'staff' && !str_starts_with($employeeId, 'S-')) {
            return 'Staff employee_id must start with S-';
        }

        if ($role === 'admin' && !str_starts_with($employeeId, 'A-')) {
            return 'Admin employee_id must start with A-';
        }

        return null;
    }

    private function generatePassword(): string
    {
        return substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789'), 0, 8);
    }

    private function responseFromArray(array $result): ApiResponse
    {
        return new ApiResponse(
            (bool) ($result['success'] ?? false),
            $result['data'] ?? null,
            $result['error'] ?? null,
            $result['message'] ?? null
        );
    }
}
