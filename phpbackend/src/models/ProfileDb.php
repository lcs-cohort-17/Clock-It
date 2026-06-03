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
            $statement = $this->pdo->prepare("SELECT * FROM profiles");
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'data' => $data];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getProfileByIdDb(string $employeeId): array
    {
        try {
            $statement = $this->pdo->prepare("SELECT * FROM profiles WHERE employee_id = :employee_id");
            $statement->execute(['employee_id' => $employeeId]);
            $profile = $statement->fetch(PDO::FETCH_ASSOC);

            if ($profile === false) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

            if (array_key_exists('first_name', $profile) && $profile['first_name'] !== null) {
                $profile['first_name'] = $this->capitalizeName($profile['first_name']);
            }

            return ['success' => true, 'data' => $profile];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createProfileDb(string $firstName, string $lastName, string $employeeId, string $role, string $email): array
    {
        if (!in_array($role, ['staff', 'admin'], true)) {
            return ['success' => false, 'error' => 'Role must be either staff or admin'];
        }

        if ($role === 'staff' && !str_starts_with($employeeId, 'S-')) {
            return ['success' => false, 'error' => 'Staff employee_id must start with S-'];
        }

        if ($role === 'admin' && !str_starts_with($employeeId, 'A-')) {
            return ['success' => false, 'error' => 'Admin employee_id must start with A-'];
        }

        $password = $this->generateRandomPassword(8);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $statement = $this->pdo->prepare(
                "INSERT INTO profiles (first_name, last_name, employee_id, role, is_active, email, password) VALUES (:first_name, :last_name, :employee_id, :role, 1, :email, :password)"
            );

            $statement->execute([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'employee_id' => $employeeId,
                'role' => $role,
                'email' => $email,
                'password' => $hashedPassword,
            ]);

            $fetchStatement = $this->pdo->prepare("SELECT * FROM profiles WHERE employee_id = :employee_id");
            $fetchStatement->execute(['employee_id' => $employeeId]);
            $profile = $fetchStatement->fetch(PDO::FETCH_ASSOC);

            if ($profile === false) {
                return ['success' => false, 'error' => 'Profile not created'];
            }

            $profile['password'] = $password;

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

        $setParts = [];
        $params = [];

        foreach ($fields as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }

        $params['employee_id'] = $employeeId;
        $setClause = implode(', ', $setParts);

        try {
            $statement = $this->pdo->prepare("UPDATE profiles SET {$setClause} WHERE employee_id = :employee_id");
            $statement->execute($params);

            if ($statement->rowCount() === 0) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

            $fetchStatement = $this->pdo->prepare("SELECT * FROM profiles WHERE employee_id = :employee_id");
            $fetchStatement->execute(['employee_id' => $employeeId]);
            $profile = $fetchStatement->fetch(PDO::FETCH_ASSOC);

            if ($profile === false) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

            if (array_key_exists('first_name', $profile) && $profile['first_name'] !== null) {
                $profile['first_name'] = $this->capitalizeName($profile['first_name']);
            }

            return ['success' => true, 'data' => $profile];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteProfileDb(string $employeeId): array
    {
        try {
            $statement = $this->pdo->prepare("UPDATE profiles SET is_active = 0 WHERE employee_id = :employee_id");
            $statement->execute(['employee_id' => $employeeId]);

            if ($statement->rowCount() === 0) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

            $fetchStatement = $this->pdo->prepare("SELECT id, employee_id, is_active FROM profiles WHERE employee_id = :employee_id");
            $fetchStatement->execute(['employee_id' => $employeeId]);
            $profile = $fetchStatement->fetch(PDO::FETCH_ASSOC);

            return ['success' => true, 'message' => 'profile deleted successfully', 'data' => $profile];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function resetPasswordDb(string $employeeId): array
    {
        $password = $this->generateRandomPassword(8);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $statement = $this->pdo->prepare("UPDATE profiles SET password = :password WHERE employee_id = :employee_id");
            $statement->execute(['password' => $hashedPassword, 'employee_id' => $employeeId]);

            if ($statement->rowCount() === 0) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

            $fetchStatement = $this->pdo->prepare("SELECT * FROM profiles WHERE employee_id = :employee_id");
            $fetchStatement->execute(['employee_id' => $employeeId]);
            $profile = $fetchStatement->fetch(PDO::FETCH_ASSOC);

            if ($profile === false) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

            $profile['password'] = $password;

            return ['success' => true, 'data' => $profile];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updatePasswordDb(string $employeeId, string $newPassword): array
    {
        try {
            $statement = $this->pdo->prepare("UPDATE profiles SET password = :password WHERE employee_id = :employee_id");
            $statement->execute(['password' => $newPassword, 'employee_id' => $employeeId]);

            if ($statement->rowCount() === 0) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

            $fetchStatement = $this->pdo->prepare("SELECT * FROM profiles WHERE employee_id = :employee_id");
            $fetchStatement->execute(['employee_id' => $employeeId]);
            $profile = $fetchStatement->fetch(PDO::FETCH_ASSOC);

            if ($profile === false) {
                return ['success' => false, 'error' => 'Profile not found'];
            }

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
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }
}
