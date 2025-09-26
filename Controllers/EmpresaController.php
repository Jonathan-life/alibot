<?php
require_once __DIR__ . '/../db/Database.php';

class EmpresaController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection(); // Se asume que es un objeto PDO
    }

public function listarEmpresas() {
    $sql = "SELECT id_empresa, ruc, razon_social, estado FROM empresas ORDER BY razon_social";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    // fetchAll con PDO::FETCH_ASSOC devuelve un array de arrays asociativos
    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $empresas;
}


public function obtenerEmpresaConFacturas($idEmpresa) {
    // Obtener datos de la empresa
    $sql_empresa = "SELECT id_empresa, ruc, razon_social FROM empresas WHERE id_empresa = ?";
    $stmt_emp = $this->conn->prepare($sql_empresa);
    $stmt_emp->execute([$idEmpresa]);
    $empresa = $stmt_emp->fetch(PDO::FETCH_ASSOC);

    if (!$empresa) return null;

    // Obtener facturas
    $sql_facturas = "
        SELECT 
            f.id_factura,
            f.nro_cpe,
            f.emisor_ruc,
            f.emisor_nombre,
            f.receptor_ruc,
            f.receptor_nombre,
            f.importe_total,
            f.moneda,
            DATE_FORMAT(f.fecha_emision, '%Y-%m-%d') AS fecha_emision,
            f.estado,
            a.id_archivo,
            a.nombre_archivo
        FROM facturas f
        LEFT JOIN archivos_factura a ON f.id_factura = a.id_factura
        WHERE f.id_empresa = ?
        ORDER BY f.fecha_emision DESC
    ";

    $stmt = $this->conn->prepare($sql_facturas);
    $stmt->execute([$idEmpresa]);
    $empresa['facturas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $empresa;
}
public function obtenerEmpresaPorId($id) {
    $sql = "SELECT id_empresa, ruc, razon_social, estado 
            FROM empresas 
            WHERE id_empresa = ? 
            LIMIT 1";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$id]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    return $empresa ?: null;
}
public function contarFacturas($idEmpresa) {
    $sql = "SELECT COUNT(*) AS total FROM facturas WHERE id_empresa = :id_empresa";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(':id_empresa', $idEmpresa, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $row['total'] ?? 0;
}
 public function sumarFacturas($idEmpresa) {
        $sql = "SELECT SUM(importe_total) AS total FROM facturas WHERE id_empresa = :id_empresa";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_empresa', $idEmpresa, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }


}
