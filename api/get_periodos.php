<?php
require_once __DIR__ . '/../db/Database.php';

$db = new Database();
$pdo = $db->getConnection();

$empresa = $_GET['empresa'] ?? null;
$tipo = $_GET['tipo'] ?? null;

if (!$empresa || !$tipo) {
    echo json_encode([]);
    exit;
}

$origen = strtolower($tipo) === "compras" ? "COMPRA" : "VENTA";

$sql = "SELECT DISTINCT DATE_FORMAT(fecha_emision, '%Y%m') AS periodo
        FROM facturas
        WHERE id_empresa = ? AND origen = ?
        ORDER BY periodo DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$empresa, $origen]);

echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
