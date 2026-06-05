<?php
namespace Config; 

require_once __DIR__ . '/../../../vendor/autoload.php';

use Dotenv\Dotenv;
use PDO;
use PDOException;

// Load .env file credentials (Get env creds from database manager)
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

class Database {

    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private function __construct() {
        $dbConnection = $_ENV['DB_CONNECTION'] ?? 'sqlite';

        if ($dbConnection === 'sqlite') {
            $dbPath = $_ENV['DB_DATABASE'] ?? (__DIR__ . '/../../storage/database.sqlite');
            $dir = dirname($dbPath);
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $dsn = 'sqlite:' . $dbPath;
            $user = null;
            $pass = null;
        } else {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $_ENV['DB_HOST'] ?? '127.0.0.1',
                $_ENV['DB_PORT'] ?? '3306',
                $_ENV['DB_NAME'] ?? 'attendance_system',
                $_ENV['DB_CHARSET'] ?? 'utf8mb4'
            );
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? '';
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
            
            // If using SQLite, enable foreign keys
            if ($dbConnection === 'sqlite') {
                $this->connection->exec('PRAGMA foreign_keys = ON;');
            }

            // Automatically initialize tables
            $this->initializeSchema();

        } catch (PDOException $e) {
            // Fallback to SQLite if MySQL failed
            if ($dbConnection !== 'sqlite') {
                try {
                    $dbPath = __DIR__ . '/../../storage/database.sqlite';
                    $dir = dirname($dbPath);
                    if (!file_exists($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $this->connection = new PDO('sqlite:' . $dbPath, null, null, $options);
                    $this->connection->exec('PRAGMA foreign_keys = ON;');
                    
                    $this->initializeSchema();
                    return;
                } catch (PDOException $ex) {
                    error_log('Fallback database connection failed: ' . $ex->getMessage());
                }
            }

            error_log('Database connection failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Database connection failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    private function initializeSchema(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            user_id INTEGER PRIMARY KEY AUTOINCREMENT,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            employee_id VARCHAR(50) UNIQUE,
            role VARCHAR(20) DEFAULT 'staff',
            is_active TINYINT DEFAULT 1,
            email VARCHAR(255) UNIQUE,
            password VARCHAR(255),
            img VARCHAR(255),
            must_change_password TINYINT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS attendance_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            profile_id INTEGER,
            event_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            event_type VARCHAR(10),
            sync_status VARCHAR(20) DEFAULT 'synced',
            device_info TEXT,
            location TEXT
        );

        CREATE TABLE IF NOT EXISTS sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            profile_id INTEGER,
            clock_in_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            clock_out_time TIMESTAMP NULL
        );
        ";

        $this->connection->exec($sql);

        // Migration: add must_change_password column if it doesn't exist (for existing databases)
        try {
            $this->connection->exec("ALTER TABLE users ADD COLUMN must_change_password TINYINT DEFAULT 0");
        } catch (\Exception $e) {
            // Column already exists — silently ignore
        }

        // Seed default users if table is empty
        $stmt = $this->connection->query("SELECT COUNT(*) FROM users");
        if ($stmt && (int)$stmt->fetchColumn() === 0) {
            $adminHash = password_hash('password123', PASSWORD_BCRYPT);
            $staffHash = password_hash('password123', PASSWORD_BCRYPT);

            $insert = $this->connection->prepare("
                INSERT INTO users (first_name, last_name, employee_id, role, is_active, email, password)
                VALUES (:first_name, :last_name, :employee_id, :role, 1, :email, :password)
            ");

            $insert->execute([
                'first_name' => 'Priya',
                'last_name' => 'Singh',
                'employee_id' => 'A-001',
                'role' => 'admin',
                'email' => 'admin@clockit.app',
                'password' => $adminHash
            ]);

            $insert->execute([
                'first_name' => 'Sarah',
                'last_name' => 'Mthembu',
                'employee_id' => 'S-101',
                'role' => 'staff',
                'email' => 'sarah@clockit.app',
                'password' => $staffHash
            ]);
        }
    }

    public static function getInstance(): self {

        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }

    private function __clone() {}

    public function __wakeup() {}
}
