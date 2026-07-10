<?php
// config/database.php
// Public frontend — connects to the SAME database as jayaplus_admin.
// Edit the four values below if your DB name/user/pass differ from the admin panel.

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
            die('<div style="font-family:sans-serif;max-width:600px;margin:60px auto;padding:30px;background:#fff5f5;color:#7A1228;border-radius:8px;border:1px solid #7A1228;">
                <h2 style="margin-top:0">⚠ Site temporarily unavailable</h2>
                <p>Database connection failed: ' . htmlspecialchars($this->conn->connect_error) . '</p>
                <p style="color:#888;font-size:13px;">Check config/database.php and ensure MySQL is running.</p>
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

    public function fetchOne($sql, $types = '', ...$params) {
        $result = $this->query($sql, $types, ...$params);
        if ($result instanceof mysqli_stmt) {
            $res = $result->get_result();
            return $res ? $res->fetch_assoc() : null;
        }
        return $result ? $result->fetch_assoc() : null;
    }

    public function fetchAll($sql, $types = '', ...$params) {
        $result = $this->query($sql, $types, ...$params);
        if ($result instanceof mysqli_stmt) {
            $res = $result->get_result();
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function insert($sql, $types = '', ...$params) {
        $this->query($sql, $types, ...$params);
        return $this->conn->insert_id;
    }

    public function escape($val) {
        return $this->conn->real_escape_string($val);
    }

    public function affectedRows() {
        return $this->conn->affected_rows;
    }
}

function db() {
    return Database::getInstance();
}
function conn() {
    return Database::getInstance()->getConnection();
}
