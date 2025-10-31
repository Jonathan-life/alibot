<?php
require_once "../../db/Database.php";
require_once __DIR__ . "/../../vendor/autoload.php";


$db = new Database();
$pdo = $db->getConnection();

$campos = $_POST['campos'] ?? [];
$tipo = $_POST['tipo'] ?? 'pdf';

if (empty($campos)) {
  die("Debe seleccionar al menos un campo");
}

$sql = "SELECT " . implode(',', $campos) . " FROM deudas";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// === GENERAR PDF ==
if ($tipo === 'pdf') {
    $html = "<h3>Reporte de Deudas</h3><table border='1' width='100%' style='border-collapse:collapse;'>";
    $html .= "<tr>";
    foreach ($campos as $c) $html .= "<th>$c</th>";
    $html .= "</tr>";
    foreach ($rows as $r) {
        $html .= "<tr>";
        foreach ($campos as $c) $html .= "<td>".$r[$c]."</td>";
        $html .= "</tr>";
    }
    $html .= "</table>";

    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output("reporte.pdf", "I");
    exit;
}

// === GENERAR EXCEL ===
if ($tipo === 'excel') {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $col = 'A';
    foreach ($campos as $c) {
        $sheet->setCellValue($col.'1', strtoupper($c));
        $col++;
    }
    $fila = 2;
    foreach ($rows as $r) {
        $col = 'A';
        foreach ($campos as $c) {
            $sheet->setCellValue($col.$fila, $r[$c]);
            $col++;
        }
        $fila++;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="reporte.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
