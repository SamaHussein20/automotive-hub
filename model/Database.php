<?php

class Database {
    private static $instance = null;
    private $host = "localhost";
    private $dbName = "automotive_hub_db";
    private $username = "root";
    private $password = "";
    private $conn = null;

    // Singleton Pattern: prevent creating many Database objects using new Database().
    private function __construct() {}

    private function __clone() {}

    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";

                $this->conn = new PDO($dsn, $this->username, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (PDOException $e) {
                die("Database Connection Error: " . $e->getMessage());
            }
        }

        return $this->conn;
    }

    // Kept for old code compatibility, but it now returns the same singleton connection.
    public function connect() {
        return $this->getConnection();
    }
}
