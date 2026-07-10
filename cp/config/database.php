<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_USER', 'crbcl90e_jayanews');
define('DB_PASS', 'WNH48aF(Oa8{');
define('DB_NAME', 'crbcl90e_jayanews');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die('<div style="font-family:sans-serif;padding:30px;background:#1a1a2e;color:#e63946;border-radius:8px;margin:20px;border:1px solid #e63946;">
                <h2>⚠ Database Connection Failed</h2>
                <p>Error: ' . $this->conn->connect_error . '</p>
                <p style="color:#aaa;font-size:13px;">Check config/database.php and ensure MySQL is running.</p>
            </div>');
        }
        $this->conn->set_charset(DB_CHARSET);
        $this->conn->query("SET time_zone = '+05:30'");
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    // Execute query with optional prepared statement
    public function query($sql, $types = '', ...$params) {
        if ($types && $params) {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            return $stmt;
        }
        return $this->conn->query($sql);
    }

    // Fetch single row
    public function fetchOne($sql, $types = '', ...$params) {
        $result = $this->query($sql, $types, ...$params);
        if ($result instanceof mysqli_stmt) {
            $res = $result->get_result();
            return $res->fetch_assoc();
        }
        return $result ? $result->fetch_assoc() : null;
    }

    // Fetch all rows
    public function fetchAll($sql, $types = '', ...$params) {
        $result = $this->query($sql, $types, ...$params);
        if ($result instanceof mysqli_stmt) {
            $res = $result->get_result();
            return $res->fetch_all(MYSQLI_ASSOC);
        }
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Insert and return last insert ID
    public function insert($sql, $types = '', ...$params) {
        $this->query($sql, $types, ...$params);
        return $this->conn->insert_id;
    }

    // Escape string
    public function escape($val) {
        return $this->conn->real_escape_string($val);
    }

    // Affected rows
    public function affectedRows() {
        return $this->conn->affected_rows;
    }
}

// Global helper
function db() {
    return Database::getInstance();
}
function conn() {
    return Database::getInstance()->getConnection();
}