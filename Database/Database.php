<?php

namespace Support\Database;

use PDO;

class Database {
    private static ?PDO $pdo = null;

    public static function connect(): void {
        if (!self::$pdo) {
            $host = env('DB_HOST');
            $dbname = env('DB_DATABASE');
            $user = env('DB_USERNAME');
            $pass = env('DB_PASSWORD');
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            self::$pdo = new PDO($dsn, $user, $pass);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    public static function setConnection(PDO $mock): void {
        self::$pdo = $mock;
    }

    public static function getConnection(): PDO {
        if (!self::$pdo) {
            self::connect();
        }
        return self::$pdo;
    }
}
