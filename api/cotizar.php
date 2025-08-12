<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status"=>"error", "message"=>"Método no permitido"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(["status"=>"error", "message"=>"JSON inválido"]);
    exit;
}

$servicio = $input['servicio'] ?? null;
$desde = $input['desde'] ?? null;
$hasta = $input['hasta'] ?? null;

if (!$servicio || !$desde || !$hasta) {
    echo json_encode(["status"=>"error", "message"=>"Datos incompletos"]);
    exit;
}

$tarifas = [
    'buzon_sunat' => 0.20,
    'compras_sire' => 0.15,
    'ventas_sire' => 0.15,
    'casilla_sunafil' => 0.25
];

if (!isset($tarifas[$servicio])) {
    echo json_encode(["status"=>"error", "message"=>"Servicio inválido"]);
    exit;
}

// MODO MOCK: simular conteo. En producción aquí llamarías al bot que haga conteo real.
$cantidad = rand(10, 80);
$precio = $cantidad * $tarifas[$servicio];

echo json_encode([
    "status" => "success",
    "data" => [
        "cantidad" => $cantidad,
        "precio" => number_format($precio, 2, '.', '')
    ]
]);
