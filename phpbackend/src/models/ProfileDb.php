<?php

namespace App\Models;

use Types\ApiResponse;
use Types\ProfileModelInterface;
use Config\Database;
use PDO;
use PDOException;

class ProfileDb implements ProfileModelInterface
{
    private PDO $db;
    
    public function __construct()
    {
        // Use the single Database class
        $this->db = Database::getInstance()->getConnection();
    }
    
    // ─── HELPER FUNCTIONS ─────────────────────────────────────────
    
    private function capitalizeFirstName(?string $first_name): ?string
    {
        if (empty($first_name)) {
            return $first_name;
        }
        return ucfirst(strtolower($first_name));
    }
    
    private function generatePassword(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        $length = 8;
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }

    // ─── GET ALL USERS ─────────────────────────────────────────────
    
    public function adminGettingAllUsersDb(): ApiResponse
    {
        try {
            // CHANGED: profiles -> users
            $stmt = $this->db->prepare("SELECT * FROM users");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return new ApiResponse(true, $data);
        } catch (PDOException $error) {
            return new ApiResponse(false, null, $error->getMessage());
        }
    }
    
    // ─── GET BY ID ─────────────────────────────────────────────────
    
    public function getProfileByIdDb(string $employee_id): ApiResponse
    {
        try {
            // CHANGED: profiles -> users
            $stmt = $this->db->prepare("SELECT * FROM users WHERE employee_id = :employee_id");
            $stmt->execute(['employee_id' => $employee_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data === false) {
                return new ApiResponse(false, null, 'Profile not found');
            }
            
            if (isset($data['first_name']) && !empty($data['first_name'])) {
                $data['first_name'] = $this->capitalizeFirstName($data['first_name']);
            }
            
            return new ApiResponse(true, $data);
        } catch (PDOException $error) {
            return new ApiResponse(false, null, $error->getMessage());
        }
    }
    
    // ─── CREATE USER ───────────────────────────────────────────────
    
    public function adminCreatingUserDb(
        string $first_name,
        string $last_name,
        string $employee_id,
        string $role,
        string $email,
        string $img = null
    ): ApiResponse {
        // Validate role
        if ($role !== 'staff' && $role !== 'admin') {
            return new ApiResponse(false, null, 'Role must be either staff or admin');
        }
        
        // Generate plain text password and hash it
        $plainPassword = $this->generatePassword();
        echo "Generated password: $plainPassword\n"; // Debugging line - remove in production
        $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);
        
        try {
            // DON'T validate employee_id format - the trigger will handle it!
            // Just insert, let the database trigger generate the correct employee_id
            
            $stmt = $this->db->prepare(
                "INSERT INTO users (first_name, last_name, employee_id, role, is_active, email, password, img) 
                VALUES (:first_name, :last_name, :employee_id, :role, 1, :email, :password, :img)"
            );
            
            $stmt->execute([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'employee_id' => $employee_id,  // Send it, but trigger may override
                'role' => $role,
                'email' => $email,
                'password' => $hashedPassword,
                'img' => $img
            ]);
            
            // IMPORTANT: Don't search by the employee_id you sent!
            // Search by email instead (email is unique and won't be changed by trigger)
            $stmt2 = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt2->execute(['email' => $email]);
            $data = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            if ($data === false) {
                // If email doesn't work, try the most recent user
                $stmt3 = $this->db->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT 1");
                $stmt3->execute();
                $data = $stmt3->fetch(PDO::FETCH_ASSOC);
            }
            
            if ($data === false) {
                return new ApiResponse(false, null, 'Failed to retrieve created profile for email: ' . $email);
            }
            
            // Return plain text password to admin
            $data['password'] = $plainPassword;
            
            return new ApiResponse(true, $data);
            
        } catch (PDOException $error) {
            error_log("PDOException: " . $error->getMessage());
            return new ApiResponse(false, null, $error->getMessage());
        }
    }
    
    // ─── LOGIN ─────────────────────────────────────────────────────
    
    public function loginProfileDb(string $email): ApiResponse
    {
        try {
            // CHANGED: profiles -> users
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data === false) {
                return new ApiResponse(false, null, 'User not found');
            }
            
            if ($data['is_active'] != 1) {
                return new ApiResponse(false, null, 'Account is disabled');
            }
            
            return new ApiResponse(true, $data);
        } catch (PDOException $error) {
            return new ApiResponse(false, null, $error->getMessage());
        }
    }
    
    // ─── UPDATE USER ───────────────────────────────────────────────
    
    public function adminUpdatingUserDb(string $employee_id, array $updates): ApiResponse
    {
        if (empty($updates)) {
            return new ApiResponse(false, null, 'No fields provided for update');
        }
        
        // Remove fields that shouldn't be updated
        unset($updates['user_id'], $updates['password'], $updates['created_at'], $updates['updated_at']);
        
        try {
            $setClause = '';
            $params = ['employee_id' => $employee_id];
            
            foreach ($updates as $key => $value) {
                $setClause .= "$key = :$key, ";
                $params[$key] = $value;
            }
            $setClause = rtrim($setClause, ', ');
            
            // CHANGED: profiles -> users
            $sql = "UPDATE users SET $setClause WHERE employee_id = :employee_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            // Get the updated record - CHANGED: profiles -> users
            $stmt2 = $this->db->prepare("SELECT * FROM users WHERE employee_id = :employee_id");
            $stmt2->execute(['employee_id' => $employee_id]);
            $data = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            if ($data === false) {
                return new ApiResponse(false, null, 'Profile not found');
            }
            
            return new ApiResponse(true, $data);
        } catch (PDOException $error) {
            return new ApiResponse(false, null, $error->getMessage());
        }
    }
    
    // ─── SOFT DELETE ───────────────────────────────────────────────
    
    public function adminDeletingUserDb(string $employee_id): ApiResponse
    {
        try {
            // CHANGED: profiles -> users
            $stmt = $this->db->prepare("UPDATE users SET is_active = 0 WHERE employee_id = :employee_id");
            $stmt->execute(['employee_id' => $employee_id]);
            
            if ($stmt->rowCount() === 0) {
                return new ApiResponse(false, null, 'Profile not found');
            }
            
            // Get the updated record - CHANGED: profiles -> users
            $stmt2 = $this->db->prepare("SELECT * FROM users WHERE employee_id = :employee_id");
            $stmt2->execute(['employee_id' => $employee_id]);
            $data = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            return new ApiResponse(true, $data, null, 'profile deleted successfully');
        } catch (PDOException $error) {
            return new ApiResponse(false, null, $error->getMessage());
        }
    }

    public function softDeleteUserDb(string $employee_id, bool $is_active): ApiResponse
    {
        try {
            // CHANGED: profiles -> users
            $stmt = $this->db->prepare("UPDATE users SET is_active = :is_active WHERE employee_id = :employee_id");
            $stmt->execute(['employee_id' => $employee_id, 'is_active' => $is_active]);
            
            if ($stmt->rowCount() === 0) {
                return new ApiResponse(false, null, 'Profile not found');
            }
            
            // Get the updated record - CHANGED: profiles -> users
            $stmt2 = $this->db->prepare("SELECT * FROM users WHERE employee_id = :employee_id");
            $stmt2->execute(['employee_id' => $employee_id]);
            $data = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            return new ApiResponse(true, $data, null, 'profile deleted successfully');
        } catch (PDOException $error) {
            return new ApiResponse(false, null, $error->getMessage());
        }
    }
    
    // ─── RESET PASSWORD (Admin) ────────────────────────────────────
    
    public function resetPasswordDb(string $employee_id): ApiResponse
    {
        try {
            $newPassword = $this->generatePassword();
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            
            // CHANGED: profiles -> users
            $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE employee_id = :employee_id");
            $stmt->execute(['password' => $hashedPassword, 'employee_id' => $employee_id]);
            
            if ($stmt->rowCount() === 0) {
                return new ApiResponse(false, null, 'Profile not found');
            }
            
            // Get updated record - CHANGED: profiles -> users
            $stmt2 = $this->db->prepare("SELECT * FROM users WHERE employee_id = :employee_id");
            $stmt2->execute(['employee_id' => $employee_id]);
            $data = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            // Return plain text password (not hashed)
            $data['password'] = $newPassword;
            
            return new ApiResponse(true, $data);
        } catch (PDOException $error) {
            return new ApiResponse(false, null, $error->getMessage());
        }
    }
    
    // ─── UPDATE OWN PASSWORD (Staff) ───────────────────────────────
    
    public function updatePasswordDb(string $employee_id, string $hashedPassword): ApiResponse
    {
        try {
            // CHANGED: profiles -> users
            $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE employee_id = :employee_id");
            $stmt->execute(['password' => $hashedPassword, 'employee_id' => $employee_id]);
            
            if ($stmt->rowCount() === 0) {
                return new ApiResponse(false, null, 'Profile not found');
            }
            
            // Get updated record - CHANGED: profiles -> users
            $stmt2 = $this->db->prepare("SELECT * FROM users WHERE employee_id = :employee_id");
            $stmt2->execute(['employee_id' => $employee_id]);
            $data = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            // Remove password from response for security
            unset($data['password']);
            
            return new ApiResponse(true, $data);
        } catch (PDOException $error) {
            return new ApiResponse(false, null, $error->getMessage());
        }
    }
    
    // ─── CLEAR CACHE ───────────────────────────────────────────────
    
    public function clearCacheDb(string $employee_id): ApiResponse
    {
        try {
            // This is a placeholder - implement your cache clearing logic here
            return new ApiResponse(true, null, null, 'Cache cleared successfully');
        } catch (\Exception $error) {
            return new ApiResponse(false, null, $error->getMessage());
        }
    }
}