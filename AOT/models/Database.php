<?php


require_once __DIR__ . '/../config/config.php';

class Database
{
    private static ?Database $instance = null;
    private mysqli $conn;

    private function __construct()
    {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->conn->connect_error) {
            http_response_code(500);
            die(json_encode([
                'ok'  => false,
                'msg' => 'DB connection failed: ' . $this->conn->connect_error
            ]));
        }

        $this->conn->set_charset('utf8mb4');
    }

    /** Prevent cloning */
    private function __clone() {}

    /** Get the singleton instance */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Return the active mysqli connection */
    public function getConnection(): mysqli
    {
        return $this->conn;
    }

    /**
     * 
     *
     * @param string 
     * @param string 
     * @param mixed  
     * @return array
     */
    public function query(string $sql, string $types = '', ...$params): array
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Prepare failed: ' . $this->conn->error);
        }

        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows   = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return $rows;
    }

    /**
     * 
     *
     * @return int  
     */
    public function execute(string $sql, string $types = '', ...$params): int
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Prepare failed: ' . $this->conn->error);
        }

        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $id = $stmt->insert_id ?: $stmt->affected_rows;
        $stmt->close();

        return $id;
    }
}
