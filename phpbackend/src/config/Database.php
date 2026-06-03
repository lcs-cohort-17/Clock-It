<?php

namespace Config;

use PDO;
use PDOException;

class Database {

    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private function __construct() {
        // We assume the environment variables have already been loaded 
        // by the entry point (index.php) via Dotenv.
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $_ENV['DB_HOST'] ?? 'localhost',
            $_ENV['DB_PORT'] ?? '3306',
            $_ENV['DB_NAME'],
            $_ENV['DB_CHARSET'] ?? 'utf8mb4'
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO(
                $dsn,
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                $options
            );
        } catch (PDOException $e) {
            // Log the error and stop execution
            error_log('Database connection failed: ' . $e->getMessage());
            
            // In a real API, throw a custom exception or return a clean JSON error
            throw new \RuntimeException('Database connection failed.');
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