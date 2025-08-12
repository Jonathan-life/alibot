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

require_once __DIR__ . '/../db/conexion.php';

// Validaciones mínimas
if (empty($input['ruc']) || empty($input['servicio']) || empty($input['desde']) || empty($input['hasta'])) {
    echo json_encode(["status"=>"error", "message"=>"Faltan datos obligatorios"]);
    exit;
}

// Asegurar empresa
$stmt = $pdo->prepare("SELECT id FROM empresas WHERE ruc = ? LIMIT 1");
$stmt->execute([$input['ruc']]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {
    $stmt = $pdo->prepare("INSERT INTO empresas (ruc, razon_social, correo) VALUES (?, ?, ?)");
    $stmt->execute([$input['ruc'], $input['razon_social'] ?? $input['ruc'], $input['correo'] ?? null]);
    $empresa_id = $pdo->lastInsertId();
} else {
    $empresa_id = $empresa['id'];
}

// Obtener servicio id
$stmt = $pdo->prepare("SELECT id FROM servicios WHERE clave = ? LIMIT 1");
$stmt->execute([$input['servicio']]);
$servicio = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$servicio) {
    echo json_encode(["status"=>"error", "message"=>"Servicio no encontrado. Ejecuta sql/alibot.sql para crear servicios."]);
    exit;
}

// Insertar solicitud
$stmt = $pdo->prepare("INSERT INTO solicitudes (empresa_id, servicio_id, fecha_desde, fecha_hasta, cantidad_doc, precio, estado) VALUES (?, ?, ?, ?, ?, ?, 'pendiente')");
$stmt->execute([$empresa_id, $servicio['id'], $input['desde'], $input['hasta'], $input['cantidad'] ?? 0, $input['precio'] ?? 0.00]);

echo json_encode(["status"=>"success", "message"=>"Solicitud registrada correctamente", "id" => $pdo->lastInsertId()]);
