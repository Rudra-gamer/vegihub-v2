<?php


function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $db = env('DB_DATABASE', 'vegihub');
        $user = env('DB_USERNAME', 'root');
        $pass = env('DB_PASSWORD', '');

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die("Database Connection Failed: " . $e->getMessage());
            }
            die("Database connection error. Please try again later.");
        }
    }
    return $pdo;
}
