<?php
require_once __DIR__ . '/../db/Database.php';

class FacturaController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection(); // Objeto PDO
    }

    public function listarFacturasPorEmpresa($id_empresa) {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM facturas
            WHERE id_empresa = ?
            ORDER BY fecha_emision ASC
        ");
        $stmt->execute([$id_empresa]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
