<?php
// Este script está pensado para ejecutarse por CLI o cron. No debe exponerse públicamente sin autenticación.
if (PHP_SAPI !== 'cli') {
    header('Content-Type: application/json');
    echo json_encode(["status"=>"error","message"=>"Este endpoint sólo funciona por CLI"]);
    exit;
}

require_once __DIR__ . '/../db/conexion.php';

// Obtener solicitudes pendientes (limitado para ejemplo)
$stmt = $pdo->query("SELECT s.*, e.ruc FROM solicitudes s JOIN empresas e ON e.id = s.empresa_id WHERE s.estado = 'pendiente' LIMIT 5");
$pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($pendientes as $s) {
    $id = $s['id'];
    $ruc = $s['ruc'];
    $desde = $s['fecha_desde'];
    $hasta = $s['fecha_hasta'];
    // Aquí llamarías al bot Python y recogerías resultado real.
    // Ejemplo de llamada (suponiendo que python3 está en PATH):
    $cmd = escapeshellcmd("python3 ../bot/sunat_bot.py " . escapeshellarg($ruc) . " " . escapeshellarg($desde) . " " . escapeshellarg($hasta));
    $output = null;
    $return_var = null;
    exec($cmd, $output, $return_var);
    $json_output = @json_decode(implode("\n",$output), true);
    if ($json_output && isset($json_output['cantidad'])) {
        $cantidad = (int)$json_output['cantidad'];
        // Actualizar solicitud
        $upd = $pdo->prepare("UPDATE solicitudes SET cantidad_doc = ?, resultado_json = ?, estado = 'completado' WHERE id = ?");
        $upd->execute([$cantidad, json_encode($json_output), $id]);
        echo "Procesada solicitud $id - cantidad: $cantidad\n";
    } else {
        // Marcar como procesando o error según prefieras
        $upd = $pdo->prepare("UPDATE solicitudes SET estado = 'procesando' WHERE id = ?");
        $upd->execute([$id]);
        echo "Solicitud $id marcada como procesando (bot no devolvió datos)\n";
    }
}
