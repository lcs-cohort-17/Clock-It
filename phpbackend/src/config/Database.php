<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use PDO;
use PDOException;

// Load .env file credentials (Get env creds from database manager)
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

class Database {

    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private function __construct() {

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'],
            $_ENV['DB_NAME'],
            $_ENV['DB_CHARSET']
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

            error_log(
                'Database connection failed: ' . $e->getMessage()
            );

            http_response_code(500);

            echo json_encode([
                'error' => 'Database connection failed.'
            ]);

            exit;
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
