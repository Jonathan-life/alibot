<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../db/;

$q = $pdo->query("
    SELECT s.id, e.ruc, e.razon_social, s.creado_en, s.precio, s.estado, svc.nombre as servicio
    FROM solicitudes s
    JOIN empresas e ON e.id = s.empresa_id
    JOIN servicios svc ON svc.id = s.servicio_id
    ORDER BY s.creado_en DESC
");

$rows = $q->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "data" => $rows
]);
