<?php
header('Content-Type: application/json');
require_once "../db/Database.php";

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['pagos']) || !is_array($data['pagos'])) {
        throw new Exception("Datos inválidos o formato incorrecto.");
    }

    $db = new Database();
    $pdo = $db->getConnection();
    $pdo->beginTransaction();

    foreach ($data['pagos'] as $pago) {
        // Validar campos requeridos
        $requeridos = ['id_deuda', 'monto', 'fecha_pago', 'metodo_pago'];
        foreach ($requeridos as $campo) {
            if (!isset($pago[$campo])) {
                throw new Exception("Falta el campo requerido: $campo");
            }
        }

        // Convertir tipos
        $id_deuda = (int)$pago['id_deuda'];
        $monto = floatval($pago['monto']);
        $fecha_pago = $pago['fecha_pago'];
        $metodo_pago = trim($pago['metodo_pago']);
        $interes_moratorio = floatval($pago['interes_moratorio'] ?? 0);
        $capital_pagado = floatval($pago['capital_pagado'] ?? 0);
        $saldo_pendiente = isset($pago['saldo_pendiente']) ? floatval($pago['saldo_pendiente']) : null;

        // 1️⃣ Verificar que la deuda exista
        $select = $pdo->prepare("SELECT pagos, saldo_total, interes_moratorio FROM deudas WHERE id = :id");
        $select->execute([':id' => $id_deuda]);
        $deuda = $select->fetch(PDO::FETCH_ASSOC);

        if (!$deuda) {
            throw new Exception("No se encontró la deuda con ID $id_deuda.");
        }

        // 2️⃣ Calcular nuevos valores
        $nuevo_total_pagado = floatval($deuda['pagos']) + $monto;

        // Si no se envió saldo_pendiente, lo calculamos automáticamente
        if ($saldo_pendiente === null) {
            $saldo_pendiente = max(floatval($deuda['saldo_total']) - $monto, 0);
        }

        $nuevo_saldo_total = $saldo_pendiente;

        // 3️⃣ Insertar registro del pago
        $insert = $pdo->prepare("
            INSERT INTO pagos (
                id_deuda, fecha_pago, monto, interes_moratorio,
                capital_pagado, saldo_pendiente, metodo_pago, fecha_pago_actualizada
            ) VALUES (
                :id_deuda, :fecha_pago, :monto, :interes_moratorio,
                :capital_pagado, :saldo_pendiente, :metodo_pago, NOW()
            )
        ");
        $insert->execute([
            ':id_deuda' => $id_deuda,
            ':fecha_pago' => $fecha_pago,
            ':monto' => $monto,
            ':interes_moratorio' => $interes_moratorio,
            ':capital_pagado' => $capital_pagado,
            ':saldo_pendiente' => $nuevo_saldo_total,
            ':metodo_pago' => $metodo_pago
        ]);

        // 4️⃣ Actualizar SOLO campos relacionados con pago
        $update = $pdo->prepare("
            UPDATE deudas
            SET pagos = :nuevo_pagado,
                saldo_total = :nuevo_saldo,
                interes_moratorio = :interes_moratorio,
                fecha_pagos = :fecha_pago
            WHERE id = :id_deuda
        ");
        $update->execute([
            ':nuevo_pagado' => $nuevo_total_pagado,
            ':nuevo_saldo' => $nuevo_saldo_total,
            ':interes_moratorio' => $interes_moratorio,
            ':fecha_pago' => $fecha_pago,
            ':id_deuda' => $id_deuda
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'ok', 'message' => 'Pagos registrados correctamente.']);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
