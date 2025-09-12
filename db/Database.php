<?php
class Database {
    private $host = 'localhost';
    private $port = 3306;
    private $db   = 'sistema_contable';   // tu base de datos
    private $user = 'root';     // tu usuario
    private $pass = '';         // tu contraseña
    private $charset = 'utf8mb4';
    private $conn;

    public function getConnection() {
        if ($this->conn) {
            return $this->conn;
        }

        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
            exit;
        }

        return $this->conn;
    }
}
