<?php
header('Content-Type: application/json; charset=utf-8');
require_once "../../db/Database.php";

try {
    // 📦 Leer el cuerpo JSON
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    // 📋 Verificar que se recibió JSON válido
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        echo json_encode(['status' => 'error', 'message' => 'No se recibió JSON válido']);
        exit;
    }

    // 📦 Extraer variables
    $id = $data['id'] ?? null;
    $id_empresa = $data['id_empresa'] ?? null;
    $ruc = $data['ruc'] ?? null;
    $periodo_tributario = $data['periodo_tributario'] ?? null;
    $formulario = $data['formulario'] ?? null;
    $numero_orden = $data['numero_orden'] ?? null;
    $tributo_multa = $data['tributo_multa'] ?? null;
    $tipo = $data['tipo'] ?? null;
    $fecha_emision = $data['fecha_emision'] ?? null;
    $fecha_notificacion = $data['fecha_notificacion'] ?? null;
    $fecha_pagos = $data['fecha_pagos'] ?? null;
    $fecha_calculos = $data['fecha_calculos'] ?? null;
    $etapa_basica = $data['etapa_basica'] ?? null;
    $importe_tributaria = floatval($data['importe_tributaria'] ?? 0);
    $interes_capitalizado = floatval($data['interes_capitalizado'] ?? 0);
    $interes_moratorio = floatval($data['interes_moratorio'] ?? 0);
    $pagos = floatval($data['pagos'] ?? 0);
    $saldo_total = floatval($data['saldo_total'] ?? 0);

    if (empty($id_empresa)) {
        echo json_encode(['status' => 'error', 'message' => 'id_empresa faltante']);
        exit;
    }

    $db = new Database();
    $pdo = $db->getConnection();

    if ($id) {
        // 🔄 UPDATE
        $sql = "UPDATE deudas SET
            id_empresa = :id_empresa,
            ruc = :ruc,
            periodo_tributario = :periodo_tributario,
            formulario = :formulario,
            numero_orden = :numero_orden,
            tributo_multa = :tributo_multa,
            tipo = :tipo,
            fecha_emision = :fecha_emision,
            fecha_notificacion = :fecha_notificacion,
            fecha_pagos = :fecha_pagos,
            fecha_calculos = :fecha_calculos,
            etapa_basica = :etapa_basica,
            importe_tributaria = :importe_tributaria,
            interes_capitalizado = :interes_capitalizado,
            interes_moratorio = :interes_moratorio,
            pagos = :pagos,
            saldo_total = :saldo_total
            WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_empresa' => $id_empresa,
            ':ruc' => $ruc,
            ':periodo_tributario' => $periodo_tributario,
            ':formulario' => $formulario,
            ':numero_orden' => $numero_orden,
            ':tributo_multa' => $tributo_multa,
            ':tipo' => $tipo,
            ':fecha_emision' => $fecha_emision,
            ':fecha_notificacion' => $fecha_notificacion,
            ':fecha_pagos' => $fecha_pagos,
            ':fecha_calculos' => $fecha_calculos,
            ':etapa_basica' => $etapa_basica,
            ':importe_tributaria' => $importe_tributaria,
            ':interes_capitalizado' => $interes_capitalizado,
            ':interes_moratorio' => $interes_moratorio,
            ':pagos' => $pagos,
            ':saldo_total' => $saldo_total,
            ':id' => $id,
        ]);
        echo json_encode(['status' => 'ok']);
    } else {
        // 🆕 INSERT
        $sql = "INSERT INTO deudas (
            id_empresa, ruc, periodo_tributario, formulario, numero_orden, tributo_multa,
            tipo, fecha_emision, fecha_notificacion, fecha_pagos, fecha_calculos,
            etapa_basica, importe_tributaria, interes_capitalizado, interes_moratorio,
            pagos, saldo_total
        ) VALUES (
            :id_empresa, :ruc, :periodo_tributario, :formulario, :numero_orden, :tributo_multa,
            :tipo, :fecha_emision, :fecha_notificacion, :fecha_pagos, :fecha_calculos,
            :etapa_basica, :importe_tributaria, :interes_capitalizado, :interes_moratorio,
            :pagos, :saldo_total
        )";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_empresa' => $id_empresa,
            ':ruc' => $ruc,
            ':periodo_tributario' => $periodo_tributario,
            ':formulario' => $formulario,
            ':numero_orden' => $numero_orden,
            ':tributo_multa' => $tributo_multa,
            ':tipo' => $tipo,
            ':fecha_emision' => $fecha_emision,
            ':fecha_notificacion' => $fecha_notificacion,
            ':fecha_pagos' => $fecha_pagos,
            ':fecha_calculos' => $fecha_calculos,
            ':etapa_basica' => $etapa_basica,
            ':importe_tributaria' => $importe_tributaria,
            ':interes_capitalizado' => $interes_capitalizado,
            ':interes_moratorio' => $interes_moratorio,
            ':pagos' => $pagos,
            ':saldo_total' => $saldo_total,
        ]);
        $nuevoId = $pdo->lastInsertId();
        echo json_encode(['status' => 'ok', 'nuevo_id' => $nuevoId]);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
