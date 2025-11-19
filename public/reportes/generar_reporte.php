<?php
require_once "../../db/Database.php";
require_once __DIR__ . "/../../vendor/autoload.php";

$db = new Database();
$pdo = $db->getConnection();

// Obtener TODAS las columnas automáticamente
$sql = "SELECT * FROM deudas";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Si no hay registros
if (!$rows) {
    die("No hay datos para mostrar.");
}

// Obtener nombres de columnas dinámicamente
$columnas = array_keys($rows[0]);

// === GENERAR PDF ===
$html = "
<h2 style='text-align:center;'>REPORTE DE DEUDAS</h2>
<table border='1' width='100%' style='border-collapse:collapse; font-size:12px;'>
<tr>";

foreach ($columnas as $col) {
    $html .= "<th style='background:#eee; padding:5px; text-transform:uppercase;'>$col</th>";
}

$html .= "</tr>";

foreach ($rows as $r) {
    $html .= "<tr>";
    foreach ($columnas as $col) {
        $html .= "<td style='padding:4px;'>".$r[$col]."</td>";
    }
    $html .= "</tr>";
}

$html .= "</table>";

// Crear PDF
$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output("reporte_deudas.pdf", "I");
exit;
