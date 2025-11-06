<?php
header('Content-Type: application/json; charset=utf-8');
require_once "../../db/Database.php";

try {
    // 🧩 Leer cuerpo JSON
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        echo json_encode(['status' => 'error', 'message' => 'JSON inválido o vacío']);
        exit;
    }

    // 🔒 Validar empresa
    if (empty($data['id_empresa']) || empty($data['ruc'])) {
        echo json_encode(['status' => 'error', 'message' => 'Datos de empresa incompletos']);
        exit;
    }

    $db = new Database();
    $pdo = $db->getConnection();

    // 📋 Columnas válidas
    $columnas = [
        'id_empresa', 'ruc', 'periodo_tributario', 'formulario', 'numero_orden',
        'tributo_multa', 'tipo', 'fecha_emision', 'fecha_notificacion',
        'fecha_pagos', 'fecha_calculos', 'etapa_basica',
        'importe_tributaria', 'interes_capitalizado', 'interes_moratorio',
        'pagos', 'saldo_total'
    ];

    // 🔄 Si hay ID => actualizar
    if (!empty($data['id'])) {
        $id = $data['id'];
        $campos = [];
        $params = [':id' => $id];

        foreach ($columnas as $columna) {
            if (isset($data[$columna])) {
                $campos[] = "$columna = :$columna";
                $params[":$columna"] = $data[$columna];
            }
        }

        if (empty($campos)) {
            echo json_encode(['status' => 'error', 'message' => 'No se enviaron campos para actualizar']);
            exit;
        }

        $sql = "UPDATE deudas SET " . implode(', ', $campos) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['status' => 'ok', 'message' => 'Deuda actualizada correctamente']);
    } else {
        // 🆕 Insertar nuevo registro
        $camposSQL = implode(', ', $columnas);
        $valoresSQL = ':' . implode(', :', $columnas);

        $sql = "INSERT INTO deudas ($camposSQL) VALUES ($valoresSQL)";
        $stmt = $pdo->prepare($sql);

        $params = [];
        foreach ($columnas as $columna) {
            // Valores nulos o numéricos vacíos se convierten en 0 para evitar errores
            $valor = $data[$columna] ?? null;
            if (is_numeric($valor) && $valor === '') $valor = 0;
            $params[":$columna"] = $valor;
        }

        $stmt->execute($params);
        $nuevoId = $pdo->lastInsertId();

        echo json_encode(['status' => 'ok', 'nuevo_id' => $nuevoId]);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error inesperado: ' . $e->getMessage()]);
}
?>
