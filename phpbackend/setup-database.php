<?php
/**
 * Database Setup Script
 * Run this once to initialize the database and tables
 * Usage: php setup-database.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env file
$envPath = __DIR__ . '/';
$envFile = $envPath . '.env';

if (file_exists($envFile)) {
    $dotenv = Dotenv::createImmutable($envPath);
    $dotenv->load();
}

// Get database credentials from .env
$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbPort = $_ENV['DB_PORT'] ?? 3306;
$dbName = $_ENV['DB_NAME'] ?? 'clock_it';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? '';
$dbCharset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

echo "═══════════════════════════════════════════════════════════════\n";
echo "  Clock-It Database Setup\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Step 1: Connect to MySQL server (without specific database)
echo "Step 1: Connecting to MySQL server...\n";
try {
    $pdo = new PDO(
        "mysql:host=$dbHost;port=$dbPort;charset=$dbCharset",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "✓ Connected successfully\n\n";
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
    echo "\nPlease check your database credentials in .env file:\n";
    echo "  DB_HOST=$dbHost\n";
    echo "  DB_PORT=$dbPort\n";
    echo "  DB_USER=$dbUser\n";
    exit(1);
}

// Step 2: Create database
echo "Step 2: Creating database '$dbName'...\n";
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`;");
    echo "✓ Database created/exists\n\n";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 3: Use the database
echo "Step 3: Selecting database '$dbName'...\n";
try {
    $pdo->exec("USE `$dbName`;");
    echo "✓ Database selected\n\n";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 4: Create tables
echo "Step 4: Creating tables...\n";

$tables = [
    // Users Table
    "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id VARCHAR(50) UNIQUE NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('staff', 'admin') NOT NULL DEFAULT 'staff',
        is_active TINYINT(1) DEFAULT 1,
        img VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_employee_id (employee_id),
        INDEX idx_email (email),
        INDEX idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    
    // Profiles Table (legacy compatibility)
    "CREATE TABLE IF NOT EXISTS profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        employee_id VARCHAR(50) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX idx_employee_id (employee_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    
    // Sessions Table
    "CREATE TABLE IF NOT EXISTS sessions (
        session_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        employee_id VARCHAR(50) NOT NULL,
        clock_in_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        clock_out_time TIMESTAMP NULL,
        location VARCHAR(255),
        device_info VARCHAR(255),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_employee_id (employee_id),
        INDEX idx_clock_in_time (clock_in_time),
        INDEX idx_clock_out_time (clock_out_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    
    // Attendance Logs Table
    "CREATE TABLE IF NOT EXISTS attendance_logs (
        log_id INT AUTO_INCREMENT PRIMARY KEY,
        profile_id INT,
        user_id INT,
        employee_id VARCHAR(50),
        event_type ENUM('in', 'out', 'break_start', 'break_end') NOT NULL,
        event_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        location VARCHAR(255),
        device_info VARCHAR(255),
        sync_status ENUM('pending', 'synced', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
        INDEX idx_employee_id (employee_id),
        INDEX idx_event_type (event_type),
        INDEX idx_event_time (event_time),
        INDEX idx_sync_status (sync_status),
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    
    // Leave Requests Table
    "CREATE TABLE IF NOT EXISTS leave_requests (
        leave_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        employee_id VARCHAR(50) NOT NULL,
        leave_type VARCHAR(50) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        reason TEXT,
        status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
        approver_id INT,
        approval_date TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (approver_id) REFERENCES users(user_id) ON DELETE SET NULL,
        INDEX idx_employee_id (employee_id),
        INDEX idx_status (status),
        INDEX idx_start_date (start_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    
    // Admin Settings Table
    "CREATE TABLE IF NOT EXISTS admin_settings (
        setting_id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value LONGTEXT,
        setting_type VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_setting_key (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    
    // Audit Trail Table
    "CREATE TABLE IF NOT EXISTS audit_trail (
        audit_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(255) NOT NULL,
        entity_type VARCHAR(50),
        entity_id VARCHAR(50),
        old_values JSON,
        new_values JSON,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at),
        INDEX idx_action (action)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tableCount = 0;
foreach ($tables as $sql) {
    try {
        $pdo->exec($sql);
        $tableCount++;
        echo "  ✓ Table created\n";
    } catch (PDOException $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
}
echo "\n✓ $tableCount tables created successfully\n\n";

// Step 5: Insert sample data
echo "Step 5: Inserting sample data...\n";
try {
    // Check if admin user exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        // Password: "password123" hashed with bcrypt
        $hashedPassword = password_hash('password123', PASSWORD_BCRYPT);
        $pdo->exec("
            INSERT INTO users (employee_id, first_name, last_name, email, password, role, is_active)
            VALUES ('ADMIN-001', 'Admin', 'User', 'admin@clock-it.com', '$hashedPassword', 'admin', 1)
        ");
        echo "  ✓ Default admin user created\n";
        echo "    Email: admin@clock-it.com\n";
        echo "    Password: password123\n";
    } else {
        echo "  ℹ Admin user already exists, skipping\n";
    }
} catch (PDOException $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "✓ Database setup completed successfully!\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "You can now test the connection with:\n";
echo "  curl http://localhost:8000/api/health\n\n";
?>
