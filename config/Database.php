<?php
declare(strict_types=1);

class Database {
    private static ?PDO $pdo = null;

    public static function connect(): PDO {
        if (self::$pdo === null) {
            try {
                $env = [];
                $envPath = dirname(__DIR__) . '/.env';
                if (is_file($envPath)) {
                    $env = parse_ini_file($envPath, false, INI_SCANNER_RAW) ?: [];
                }

                $host = getenv('DB_HOST') ?: ($env['DB_HOST'] ?? 'localhost');
                $dbname = getenv('DB_NAME') ?: ($env['DB_NAME'] ?? 'mini_blog');
                $username = getenv('DB_USER') ?: ($env['DB_USER'] ?? 'root');
                $password = getenv('DB_PASSWORD') ?: ($env['DB_PASSWORD'] ?? '');

                self::$pdo = new PDO(
                    "mysql:host=" . $host . ";dbname=" . $dbname . ";charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("Database Connection Error: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}