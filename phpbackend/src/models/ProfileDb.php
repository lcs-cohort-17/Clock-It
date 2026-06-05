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
    
    public function __construct(?PDO $db = null)
    {
        // Use the passed database connection or fall back to the single Database class
        $this->db = $db ?? Database::getInstance()->getConnection();
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

        // Validate employee_id format
        if ($role === 'staff' && strpos($employee_id, 'S-') !== 0) {
            return new ApiResponse(false, null, 'Staff employee_id must start with S-');
        }
        if ($role === 'admin' && strpos($employee_id, 'A-') !== 0) {
            return new ApiResponse(false, null, 'Admin employee_id must start with A-');
        }
        
        // Generate plain text password and hash it
        $plainPassword = $this->generatePassword();
        // echo "Generated password: $plainPassword\n"; // Debugging line - remove in production
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

       // ─── GET CURRENT USER PROFILE (for logged-in user) ─────────────────
    public function getCurrentUserProfileDb(string $employee_id): ApiResponse
    {
        try {
            $stmt = $this->db->prepare("SELECT user_id, first_name, last_name, employee_id, role, is_active, email, img, created_at FROM users WHERE employee_id = :employee_id");
            $stmt->execute(['employee_id' => $employee_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data === false) {
                return new ApiResponse(false, null, 'User profile not found');
            }
            
            // Capitalize first name
            if (isset($data['first_name']) && !empty($data['first_name'])) {
                $data['first_name'] = $this->capitalizeFirstName($data['first_name']);
            }
            
            // Build full profile image URL if img exists
            if (isset($data['img']) && !empty($data['img'])) {
                // Get base URL from environment or use default
                $baseUrl = $_ENV['BASE_URL'] ?? 'http://localhost:8000';
                $data['img'] = $baseUrl . '/uploads/' . $data['img'];
            } else {
                $data['img'] = null;
            }
            
            // Remove sensitive fields
            unset($data['password']);
            
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
    
    public function adminResetPasswordDb(string $employee_id): ApiResponse
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

    // ─── GET SESSION TIMEOUT FROM SETTINGS ──────────────────────────
public function getSessionTimeoutDb(): ApiResponse
{
    try {
        // Check if settings table exists, if not, create it
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'settings'");
        $stmt->execute();
        $tableExists = $stmt->fetchColumn();
        
        if (!$tableExists) {
            // Create settings table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    setting_key VARCHAR(100) UNIQUE NOT NULL,
                    setting_value TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            // Insert default timeout
            $this->db->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('session_timeout_minutes', '30')");
            return new ApiResponse(true, ['session_timeout_minutes' => 30]);
        }
        
        // Get timeout from settings
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'session_timeout_minutes'");
        $stmt->execute();
        $timeout = $stmt->fetchColumn();
        
        $timeoutMinutes = $timeout ? (int)$timeout : 30;
        
        return new ApiResponse(true, ['session_timeout_minutes' => $timeoutMinutes]);
    } catch (PDOException $error) {
        return new ApiResponse(false, null, $error->getMessage());
    }
}


// ─── CREATE PASSWORD_RESETS TABLE ───────────────────────────────
private function ensurePasswordResetsTable(): void
{
    $this->db->exec("
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            token VARCHAR(100) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            used BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_email (email)
        )
    ");
}

// ─── FORGOT PASSWORD ────────────────────────────────────────────
public function forgotPasswordDb(string $email): ApiResponse
{
    try {
        $this->ensurePasswordResetsTable();
        
        // Check if user exists
        $stmt = $this->db->prepare("SELECT user_id FROM users WHERE email = :email AND is_active = 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Don't reveal if email exists for security
            return new ApiResponse(true, null, null, 'If your email is registered, you will receive a reset link');
        }
        
        // Generate random token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
        
        // Store token
        $stmt = $this->db->prepare("
            INSERT INTO password_resets (email, token, expires_at) 
            VALUES (:email, :token, :expires_at)
        ");
        $stmt->execute([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
        
        // Send email (you'll need to configure mail)
        $this->sendResetEmail($email, $token);
        
        return new ApiResponse(true, null, null, 'Password reset link sent to your email');
        
    } catch (PDOException $error) {
        return new ApiResponse(false, null, $error->getMessage());
    }
}

// ─── SEND RESET EMAIL ───────────────────────────────────────────
private function sendResetEmail(string $email, string $token): void
{
    $resetLink = "http://localhost:3000/reset-password?token=" . $token;
    $subject = "Password Reset Request";
    $message = "Click this link to reset your password: " . $resetLink . "\n\nThis link expires in 1 hour.";
    $headers = "From: noreply@yourapp.com";
    
    // Use mail() function (or configure SMTP)
    mail($email, $subject, $message, $headers);
    
    // For development, log the token
    error_log("Password reset token for $email: $token");
}

// ─── RESET PASSWORD (with token) ─────────────────────────────────
public function resetPasswordWithTokenDb(string $token, string $newPassword): ApiResponse
{
    try {
        $this->ensurePasswordResetsTable();
        
        // Find valid token (removed 'used' check since column may not exist yet)
        $stmt = $this->db->prepare("
            SELECT * FROM password_resets 
            WHERE token = :token AND expires_at > NOW()
        ");
        $stmt->execute(['token' => $token]);
        $reset = $stmt->fetch();
        
        if (!$reset) {
            return new ApiResponse(false, null, 'Invalid or expired reset token');
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        // Update user password
        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE email = :email");
        $stmt->execute([
            'password' => $hashedPassword,
            'email' => $reset['email']
        ]);
        
        // Mark token as used (only if column exists)
        try {
            $stmt = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE token = :token");
            $stmt->execute(['token' => $token]);
        } catch (PDOException $e) {
            // Column doesn't exist yet - that's fine
            error_log("Could not mark token as used: " . $e->getMessage());
        }
        
        return new ApiResponse(true, null, null, 'Password reset successfully');
        
    } catch (PDOException $error) {
        return new ApiResponse(false, null, $error->getMessage());
    }
}
}