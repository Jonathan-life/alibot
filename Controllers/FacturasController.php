<?php
require_once __DIR__ . '/../db/Database.php';

class FacturasController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    private function validarFecha(string $fecha): bool {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) === 1;
    }

    public function listarFacturas() {
        if (!isset($_GET['id_empresa'])) {
            die("❌ Empresa no especificada.");
        }

        $idEmpresa = intval($_GET['id_empresa']);
        $fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $fecha_fin = $_GET['fecha_fin'] ?? '';

        $condiciones_filtro = "WHERE id_empresa = :id_empresa";
        $params = ['id_empresa' => $idEmpresa];

        if ($fecha_inicio && $this->validarFecha($fecha_inicio) && $fecha_fin && $this->validarFecha($fecha_fin)) {
            $condiciones_filtro .= " AND fecha_emision BETWEEN :fecha_inicio AND :fecha_fin";
            $params['fecha_inicio'] = $fecha_inicio;
            $params['fecha_fin'] = $fecha_fin;
        } elseif ($fecha_inicio && $this->validarFecha($fecha_inicio)) {
            $condiciones_filtro .= " AND fecha_emision >= :fecha_inicio";
            $params['fecha_inicio'] = $fecha_inicio;
        } elseif ($fecha_fin && $this->validarFecha($fecha_fin)) {
            $condiciones_filtro .= " AND fecha_emision <= :fecha_fin";
            $params['fecha_fin'] = $fecha_fin;
        }

        // Obtener empresa
        $sqlEmpresa = "SELECT * FROM empresas WHERE id_empresa = :id_empresa";
        $stmtEmpresa = $this->conn->prepare($sqlEmpresa);
        $stmtEmpresa->execute(['id_empresa' => $idEmpresa]);
        $empresa = $stmtEmpresa->fetch();

        if (!$empresa) {
            die("❌ Empresa no encontrada.");
        }

        // Obtener facturas
        $sqlFacturas = "SELECT * FROM facturas $condiciones_filtro ORDER BY id_factura DESC";
        $stmtFacturas = $this->conn->prepare($sqlFacturas);
        $stmtFacturas->execute($params);
        $facturas = $stmtFacturas->fetchAll();

        return [
            'empresa' => $empresa,
            'facturas' => $facturas,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'idEmpresa' => $idEmpresa
        ];
    }

    public function obtenerArchivos(int $idFactura) {
        $sqlArchivos = "SELECT * FROM archivos_factura WHERE id_factura = :id_factura ORDER BY tipo";
        $stmt = $this->conn->prepare($sqlArchivos);
        $stmt->execute(['id_factura' => $idFactura]);
        return $stmt->fetchAll();
    }

    public function cerrarConexion() {
        $this->conn = null; // Cierra la conexión PDO
    }
}
