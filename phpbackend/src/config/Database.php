<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class Database
{

    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private function __construct()
    {
        $this->loadEnv();

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

    public static function getInstance(): self
    {

        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function getConnection(): PDO
    {
        return self::getInstance()->connection;
    }

    private function __clone() {}

    public function __wakeup() {}

    private function loadEnv(): void
    {
        $envPath = __DIR__ . '/../../';
        $file = $envPath . '.env';

        if (class_exists(\Dotenv\Dotenv::class) && is_file($file)) {
            $dotenvClass = \Dotenv\Dotenv::class;
            $dotenvClass::createImmutable($envPath)->safeLoad();
            return;
        }

        if (!is_readable($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ($key === '' || getenv($key) !== false) {
                continue;
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}
