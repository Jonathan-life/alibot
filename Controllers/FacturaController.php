<?php
require_once __DIR__ . '/../db/Database.php';

class FacturaController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection(); // Objeto PDO
    }

    public function listarFacturasPorEmpresa($id_empresa) {
        $sql = "
            SELECT *
            FROM facturas
            WHERE id_empresa = ?
            ORDER BY fecha_emision ASC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_empresa]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

public function listarFacturasPorPeriodo($empresaId, $periodo) {

    // Si viene como '202501', convertir a '2025-01'
    if (preg_match('/^\d{6}$/', $periodo)) {
        $periodo = substr($periodo, 0, 4) . '-' . substr($periodo, 4, 2);
    }

    $sql = "SELECT * FROM facturas 
            WHERE id_empresa = ? 
            AND DATE_FORMAT(fecha_emision, '%Y-%m') = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$empresaId, $periodo]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}

