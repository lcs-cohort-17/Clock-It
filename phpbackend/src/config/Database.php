<?php

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host     = 'sql34.cpt3.host-h.net';
        $dbname   = 'lcstuhmwsf_db7';
        $username = 'lcstuhmwsf_7';
        $password = 'vGvGwshmr8e4y49aY7R8';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }

    public static function getConnection() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}