<?php

namespace App\Models;

use PDO;
use PDOException;
use PDOStatement;

class ProfileDb
{
    public function __construct(private ?PDO $pdo = null) {}

    public function getProfilesDb(): array
    {
        try {
            $statement = $this->pdo->prepare("SELECT * FROM profiles WHERE is_active = 1");
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);

            // Capitalize first names in all profiles
            foreach ($data as &$profile) {
                if (isset($profile['first_name']) && $profile['first_name'] !== null) {
                    $profile['first_name'] = $this->capitalizeName($profile['first_name']);
                }
            }

            return ['success' => true, 'data' => $data];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getProfileByIdDb(string $employeeId): array
    {
        try {
            $statement = $this->pdo->prepare("SELECT * FROM profiles WHERE employee_id = :employee_id AND is_active = 1");
            $statement->execute(['employee_id' => $employeeId]);
            $profile = $statement->fetch(PDO::FETCH_ASSOC);

            if ($profile === false) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

            if (isset($profile['first_name']) && $profile['first_name'] !== null) {
                $profile['first_name'] = $this->capitalizeName($profile['first_name']);
            }

            // Remove sensitive data before returning
            unset($profile['password']);

            return ['success' => true, 'data' => $profile];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createProfileDb(string $firstName, string $lastName, string $employeeId, string $role, string $email): array
    {
        // Validate role
        if (!in_array($role, ['staff', 'admin'], true)) {
            return ['success' => false, 'error' => 'Role must be either staff or admin'];
        }

        // Validate employee ID format based on role
        if ($role === 'staff' && !str_starts_with($employeeId, 'S-')) {
            return ['success' => false, 'error' => 'Staff employee_id must start with S-'];
        }

        if ($role === 'admin' && !str_starts_with($employeeId, 'A-')) {
            return ['success' => false, 'error' => 'Admin employee_id must start with A-'];
        }

        // Check if employee already exists
        try {
            $checkStmt = $this->pdo->prepare("SELECT employee_id FROM profiles WHERE employee_id = :employee_id");
            $checkStmt->execute(['employee_id' => $employeeId]);
            if ($checkStmt->fetch()) {
                return ['success' => false, 'error' => 'Employee ID already exists'];
            }

            // Check if email already exists
            $checkEmailStmt = $this->pdo->prepare("SELECT email FROM profiles WHERE email = :email");
            $checkEmailStmt->execute(['email' => $email]);
            if ($checkEmailStmt->fetch()) {
                return ['success' => false, 'error' => 'Email already exists'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        // Generate password
        $password = $this->generateRandomPassword(8);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $statement = $this->pdo->prepare(
                "INSERT INTO profiles (first_name, last_name, employee_id, role, is_active, email, password) 
                 VALUES (:first_name, :last_name, :employee_id, :role, 1, :email, :password)"
            );

            $statement->execute([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'employee_id' => $employeeId,
                'role' => $role,
                'email' => $email,
                'password' => $hashedPassword,
            ]);

            // Fetch the created profile
            $fetchStatement = $this->pdo->prepare("SELECT * FROM profiles WHERE employee_id = :employee_id");
            $fetchStatement->execute(['employee_id' => $employeeId]);
            $profile = $fetchStatement->fetch(PDO::FETCH_ASSOC);

            if ($profile === false) {
                return ['success' => false, 'error' => 'Profile not created'];
            }

            // Return the plain text password only for initial creation response
            $profile['plain_text_password'] = $password;
            unset($profile['password']);

            return ['success' => true, 'data' => $profile];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateProfileDb(string $employeeId, array $fields): array
    {
        if (empty($fields)) {
            return ['success' => false, 'error' => 'No fields provided for update'];
        }

        // Remove sensitive fields that shouldn't be updated directly
        unset($fields['password'], $fields['employee_id'], $fields['is_active']);

        if (empty($fields)) {
            return ['success' => false, 'error' => 'No valid fields provided for update'];
        }

        $setParts = [];
        $params = [];

        foreach ($fields as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }

        $params['employee_id'] = $employeeId;
        $setClause = implode(', ', $setParts);

        try {
            $statement = $this->pdo->prepare("UPDATE profiles SET {$setClause} WHERE employee_id = :employee_id AND is_active = 1");
            $statement->execute($params);

            if ($statement->rowCount() === 0) {
                return ['success' => false, 'error' => 'Profile not found or no changes made'];
            }

            $fetchStatement = $this->pdo->prepare("SELECT * FROM profiles WHERE employee_id = :employee_id AND is_active = 1");
            $fetchStatement->execute(['employee_id' => $employeeId]);
            $profile = $fetchStatement->fetch(PDO::FETCH_ASSOC);

            if ($profile === false) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

            if (isset($profile['first_name']) && $profile['first_name'] !== null) {
                $profile['first_name'] = $this->capitalizeName($profile['first_name']);
            }

            // Remove sensitive data
            unset($profile['password']);

            return ['success' => true, 'data' => $profile];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteProfileDb(string $employeeId): array
    {
        try {
            // Check if profile exists and is active
            $checkStmt = $this->pdo->prepare("SELECT employee_id FROM profiles WHERE employee_id = :employee_id AND is_active = 1");
            $checkStmt->execute(['employee_id' => $employeeId]);
            if (!$checkStmt->fetch()) {
                return ['success' => false, 'error' => 'Active profile not found'];
            }

            // Soft delete
            $statement = $this->pdo->prepare("UPDATE profiles SET is_active = 0 WHERE employee_id = :employee_id");
            $statement->execute(['employee_id' => $employeeId]);

            $fetchStatement = $this->pdo->prepare("SELECT id, employee_id, is_active, first_name, last_name, email, role FROM profiles WHERE employee_id = :employee_id");
            $fetchStatement->execute(['employee_id' => $employeeId]);
            $profile = $fetchStatement->fetch(PDO::FETCH_ASSOC);

            return ['success' => true, 'message' => 'Profile deleted successfully', 'data' => $profile];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function resetPasswordDb(string $employeeId): array
    {
        $password = $this->generateRandomPassword(8);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $statement = $this->pdo->prepare("UPDATE profiles SET password = :password WHERE employee_id = :employee_id AND is_active = 1");
            $statement->execute(['password' => $hashedPassword, 'employee_id' => $employeeId]);

            if ($statement->rowCount() === 0) {
                return ['success' => false, 'error' => 'Active profile not found'];
            }

            $fetchStatement = $this->pdo->prepare("SELECT id, employee_id, first_name, last_name, email, role FROM profiles WHERE employee_id = :employee_id");
            $fetchStatement->execute(['employee_id' => $employeeId]);
            $profile = $fetchStatement->fetch(PDO::FETCH_ASSOC);

            if ($profile === false) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

            // Return the new plain text password
            $profile['new_password'] = $password;

            return ['success' => true, 'data' => $profile];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updatePasswordDb(string $employeeId, string $currentPassword, string $newPassword): array
    {
        try {
            // First, get the current hashed password
            $getStmt = $this->pdo->prepare("SELECT password FROM profiles WHERE employee_id = :employee_id AND is_active = 1");
            $getStmt->execute(['employee_id' => $employeeId]);
            $profile = $getStmt->fetch(PDO::FETCH_ASSOC);

            if ($profile === false) {
                return ['success' => false, 'error' => 'Active profile not found'];
            }

            // Verify current password
            if (!password_verify($currentPassword, $profile['password'])) {
                return ['success' => false, 'error' => 'Current password is incorrect'];
            }

            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update the password
            $statement = $this->pdo->prepare("UPDATE profiles SET password = :password WHERE employee_id = :employee_id");
            $statement->execute(['password' => $hashedPassword, 'employee_id' => $employeeId]);

            $fetchStatement = $this->pdo->prepare("SELECT id, employee_id, first_name, last_name, email, role FROM profiles WHERE employee_id = :employee_id");
            $fetchStatement->execute(['employee_id' => $employeeId]);
            $updatedProfile = $fetchStatement->fetch(PDO::FETCH_ASSOC);

            return ['success' => true, 'message' => 'Password updated successfully', 'data' => $updatedProfile];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function authenticateUser(string $email, string $password): array
    {
        try {
            $statement = $this->pdo->prepare(
                "SELECT * FROM profiles WHERE email = :email AND is_active = 1"
            );
            $statement->execute(['email' => $email]);
            $profile = $statement->fetch(PDO::FETCH_ASSOC);

            if ($profile === false) {
                return ['success' => false, 'error' => 'Invalid email or password'];
            }

            if (!password_verify($password, $profile['password'])) {
                return ['success' => false, 'error' => 'Invalid email or password'];
            }

            // Remove sensitive data
            unset($profile['password']);

            return ['success' => true, 'data' => $profile];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function capitalizeName(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        return ucfirst(strtolower($value));
    }

    private function generateRandomPassword(int $length): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $maxIndex)];
        }

        return $password;
    }
}