<?php
// src/models/UserDb.php

namespace App\Models;

use PDO;
use PDOException;

class UserDb
{
    private PDO $db;
    
    public function __construct(?PDO $db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            // Default connection for production
            $this->db = new PDO(
                "mysql:host=localhost;port=3307;dbname=clockit_db;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        }
    }
    
    // HELPER FUNCTIONS
    
    
    private function capitalizeFirstName(?string $name): ?string
    {
        if (empty($name)) {
            return $name;
        }
        return ucfirst(strtolower($name));
    }
    
    private function generatePassword(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($characters), 0, 8);
    }
    
    
    // GET ALL USERS
    
    public function getUsersDb(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return ['success' => true, 'data' => $data];
        } catch (PDOException $error) {
            return ['success' => false, 'error' => $error->getMessage()];
        }
    }
    
    
    public function getUserByIdDb(string $employeeId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE employee_id = :employee_id");
            $stmt->execute(['employee_id' => $employeeId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data === false) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            if (isset($data['first_name']) && !empty($data['first_name'])) {
                $data['first_name'] = $this->capitalizeFirstName($data['first_name']);
            }
            
            return ['success' => true, 'data' => $data];
        } catch (PDOException $error) {
            return ['success' => false, 'error' => $error->getMessage()];
        }
    }
    
    
    // CREATE USER
    
    public function createUserDb(
        string $first_name,
        string $last_name,
        string $employee_id,
        string $role,
        string $email
    ): array {
        // Validate role
        if (!in_array($role, ['staff', 'manager', 'admin'], true)) {
            return ['success' => false, 'error' => 'Role must be staff, manager, or admin'];
        }
        
        // Validate employee_id format
        if ($role === 'staff' && substr($employee_id, 0, 2) !== 'S-') {
            return ['success' => false, 'error' => 'Staff employee_id must start with S-'];
        }
        if ($role === 'admin' && substr($employee_id, 0, 2) !== 'A-') {
            return ['success' => false, 'error' => 'Admin employee_id must start with A-'];
        }
        
        // Generate plain text password and hash it
        $plainPassword = $this->generatePassword();
        $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);
        
        // Log both passwords (like the console.log in TypeScript)
        error_log("─────────────────────────────");
        error_log("Plain text password: " . $plainPassword);
        error_log("Hashed password: " . $hashedPassword);
        error_log("─────────────────────────────");
        
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO users (first_name, last_name, employee_id, role, email, password, is_active) 
                 VALUES (:first_name, :last_name, :employee_id, :role, :email, :password, 1)"
            );
            $stmt->execute([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'employee_id' => $employee_id,
                'role' => $role,
                'email' => $email,
                'password' => $hashedPassword
            ]);
            
            // Get the created record
            $stmt2 = $this->db->prepare("SELECT * FROM users WHERE employee_id = :employee_id");
            $stmt2->execute(['employee_id' => $employee_id]);
            $data = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            if ($data === false) {
                return ['success' => false, 'error' => 'Failed to retrieve created user'];
            }
            
            // Return plain text password to admin (not the hash)
            $data['password'] = $plainPassword;
            
            return ['success' => true, 'data' => $data];
        } catch (PDOException $error) {
            return ['success' => false, 'error' => $error->getMessage()];
        }
    }
    
    
    // LOGIN
    
    public function loginUserDb(string $email): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data === false) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            if ($data['is_active'] != 1) {
                return ['success' => false, 'error' => 'Account is disabled'];
            }
            
            return ['success' => true, 'data' => $data];
        } catch (PDOException $error) {
            return ['success' => false, 'error' => $error->getMessage()];
        }
    }
    
    
    // UPDATE USER
    
    public function updateUserDb(string $employee_id, array $updates): array
    {
        if (empty($updates)) {
            return ['success' => false, 'error' => 'No fields provided for update'];
        }
        
        try {
            $setClause = '';
            $params = ['employee_id' => $employee_id];
            
            foreach ($updates as $key => $value) {
                $setClause .= "$key = :$key, ";
                $params[$key] = $value;
            }
            $setClause = rtrim($setClause, ', ');
            
            $sql = "UPDATE users SET $setClause WHERE employee_id = :employee_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            // Check if any row was actually updated
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            // Get the updated record
            $stmt2 = $this->db->prepare("SELECT * FROM users WHERE employee_id = :employee_id");
            $stmt2->execute(['employee_id' => $employee_id]);
            $data = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            return ['success' => true, 'data' => $data];
            
        } catch (PDOException $error) {
            return ['success' => false, 'error' => $error->getMessage()];
        }
    }
    
    
    // DELETE USER (SOFT DELETE)
    
    public function deleteUserDb(string $employee_id): array
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET is_active = 0 WHERE employee_id = :employee_id");
            $stmt->execute(['employee_id' => $employee_id]);
            
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            // Get the updated record to return
            $stmt2 = $this->db->prepare("SELECT * FROM users WHERE employee_id = :employee_id");
            $stmt2->execute(['employee_id' => $employee_id]);
            $data = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            return ['success' => true, 'data' => $data, 'message' => 'user deleted successfully'];
        } catch (PDOException $error) {
            return ['success' => false, 'error' => $error->getMessage()];
        }
    }
    
    
    // RESET PASSWORD (Admin)
    
    public function resetPasswordDb(string $employee_id): array
    {
        try {
            $newPassword = $this->generatePassword();
            
            $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE employee_id = :employee_id");
            $stmt->execute(['password' => $newPassword, 'employee_id' => $employee_id]);
            
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            // Get updated record
            $stmt2 = $this->db->prepare("SELECT * FROM users WHERE employee_id = :employee_id");
            $stmt2->execute(['employee_id' => $employee_id]);
            $data = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            // Return plain text password (not hashed)
            $data['password'] = $newPassword;
            
            return ['success' => true, 'data' => $data];
        } catch (PDOException $error) {
            return ['success' => false, 'error' => $error->getMessage()];
        }
    }
    
    
    // UPDATE OWN PASSWORD (Staff)
    
    public function updatePasswordDb(string $employee_id, string $hashedPassword): array
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE employee_id = :employee_id");
            $stmt->execute(['password' => $hashedPassword, 'employee_id' => $employee_id]);
            
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            // Get updated record
            $stmt2 = $this->db->prepare("SELECT * FROM users WHERE employee_id = :employee_id");
            $stmt2->execute(['employee_id' => $employee_id]);
            $data = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            return ['success' => true, 'data' => $data];
        } catch (PDOException $error) {
            return ['success' => false, 'error' => $error->getMessage()];
        }
    }
}
