<?php
header('Content-Type: application/json');
require_once "../db/Database.php";

// Recibir JSON
$data = json_decode(file_get_contents('php://input'), true);

// Depuración opcional
// file_put_contents("log_pago.txt", print_r($data, true));

if (!$data || !isset($data['pagos'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
    exit;
}

$pagos = $data['pagos'];

try {
    $db = new Database();
    $pdo = $db->getConnection();
    $pdo->beginTransaction();

    foreach ($pagos as $pago) {
        if (!isset($pago['id_deuda'], $pago['monto'], $pago['interes_moratorio'], $pago['capital_pagado'], $pago['saldo_pendiente'], $pago['fecha_pago'], $pago['metodo_pago'])) {
            throw new Exception("Datos de pago faltantes.");
        }

        // Insertar en tabla pagos
        $stmt = $pdo->prepare("
            INSERT INTO pagos (id_deuda, fecha_pago, monto, interes_moratorio, capital_pagado, saldo_pendiente, fecha_pago_actualizada, metodo_pago) 
            VALUES (:id_deuda, :fecha_pago, :monto, :interes_moratorio, :capital_pagado, :saldo_pendiente, NOW(), :metodo_pago)
        ");
        $stmt->execute([
            ':id_deuda' => $pago['id_deuda'],
            ':fecha_pago' => $pago['fecha_pago'],
            ':monto' => $pago['monto'],
            ':interes_moratorio' => $pago['interes_moratorio'],
            ':capital_pagado' => $pago['capital_pagado'],
            ':saldo_pendiente' => $pago['saldo_pendiente'],
            ':metodo_pago' => $pago['metodo_pago']
        ]);

        // Actualizar deuda
        $stmt = $pdo->prepare("UPDATE deudas SET saldo_total = :saldo_total, fecha_pagos = :fecha_pagos WHERE id = :id_deuda");
        $stmt->execute([
            ':saldo_total' => $pago['saldo_pendiente'],
            ':fecha_pagos' => $pago['fecha_pago'],
            ':id_deuda' => $pago['id_deuda']
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'ok', 'message' => 'Pago registrado y deuda actualizada correctamente']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
