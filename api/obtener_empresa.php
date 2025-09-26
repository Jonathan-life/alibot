<?php
require_once __DIR__ . '/../Controllers/EmpresaController.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(["success" => false, "error" => "ID no proporcionado"]);
    exit;
}

$controller = new EmpresaController();
$empresa = $controller->obtenerEmpresaPorId($_GET['id']);

if ($empresa) {
    // Supón que tienes métodos para estadísticas
    $empresa['total_facturas'] = $controller->contarFacturas($_GET['id']);
    $empresa['total_importe'] = $controller->sumarFacturas($_GET['id']);
    echo json_encode(["success" => true] + $empresa);
} else {
    echo json_encode(["success" => false, "error" => "Empresa no encontrada"]);
}
