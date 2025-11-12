<?php
// exportar_registro_compras.php
// Genera un Excel (PhpSpreadsheet) con formato similar a la imagen (Registro de Compras 8.1)
// Requisitos: composer require phpoffice/phpspreadsheet

require_once __DIR__ . '/../../Controllers/FacturaController.php';
require_once __DIR__ . '/../../Controllers/EmpresaController.php';

// Composer autoload
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$empresaController = new EmpresaController();
$facturaController = new FacturaController();

$id_empresa = isset($_GET['id_empresa']) ? (int)$_GET['id_empresa'] : null;
if (!$id_empresa) {
    die("Falta id_empresa en parametro GET. Ej: exportar_registro_compras.php?id_empresa=1");
}

$empresa = $empresaController->obtenerEmpresaPorId($id_empresa);
$facturas = $facturaController->listarFacturasPorEmpresa($id_empresa);

if (!$empresa) {
    die("Empresa no encontrada.");
}

// Crear spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// ----- CORRECCIÓN: usar getDefaultStyle() sobre $spreadsheet, NO sobre $sheet -----
$spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

// ----- Encabezado similar a la imagen -----
$tituloEmpresa = strtoupper($empresa['razon_social'] ?? 'EMPRESA');
$rucEmpresa = $empresa['ruc'] ?? '';

$periodo = '';
if (!empty($facturas)) {
    $fechaPrimera = $facturas[0]['fecha_emision'] ?? null;
    if ($fechaPrimera) {
        $d = new DateTime($fechaPrimera);
        $mesesEs = [
            'January'=>'ENERO','February'=>'FEBRERO','March'=>'MARZO','April'=>'ABRIL','May'=>'MAYO','June'=>'JUNIO',
            'July'=>'JULIO','August'=>'AGOSTO','September'=>'SETIEMBRE','October'=>'OCTUBRE','November'=>'NOVIEMBRE','December'=>'DICIEMBRE'
        ];
        $mesIngles = $d->format('F');
        $periodo = ($mesesEs[$mesIngles] ?? $mesIngles) . ' ' . $d->format('Y');
    }
}

// Encabezado visual (merge y estilo)
$sheet->mergeCells('A1:O1');
$sheet->setCellValue('A1', $tituloEmpresa);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

$sheet->mergeCells('A2:O2');
$sheet->setCellValue('A2', 'RUC: ' . $rucEmpresa);
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

$sheet->mergeCells('A3:O3');
$sheet->setCellValue('A3', 'FORMATO 8.1: "REGISTRO DE COMPRAS"');
$sheet->getStyle('A3')->getFont()->setBold(true);
$sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

$sheet->mergeCells('A4:O4');
$sheet->setCellValue('A4', ($periodo ? $periodo . ' - ' : '') . 'MONEDA NACIONAL');
$sheet->getStyle('A4')->getFont()->setBold(true);
$sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

// (opcional) borde exterior para encabezado - activar si deseas
// $sheet->getStyle('A1:O4')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

// Salto de fila
$startRow = 6;

// ----- Encabezados de columnas -----
$headers = [
    'Nº registro','Fecha doc', 'Fecha vcto/pago', 'Tipo', 'Serie', 'Número',
    'Tipo doc prov', 'RUC prov', 'Nombre prov',
    'Base imponible', 'IGV', 'Importe total', 'Moneda', 'Tipo de cambio', 'Otros tributos'
];

$col = 'A';
foreach ($headers as $h) {
    $cell = $col . $startRow;
    $sheet->setCellValue($cell, $h);
    $sheet->getStyle($cell)->getFont()->setBold(true);
    $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9D9D9');
    $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getColumnDimension($col)->setWidth(15);
    $col++;
}

// ajustar anchos
$sheet->getColumnDimension('A')->setWidth(12);
$sheet->getColumnDimension('B')->setWidth(12);
$sheet->getColumnDimension('C')->setWidth(14);
$sheet->getColumnDimension('I')->setWidth(30);
$sheet->getColumnDimension('J')->setWidth(15);
$sheet->getColumnDimension('K')->setWidth(12);
$sheet->getColumnDimension('L')->setWidth(15);
$sheet->getColumnDimension('N')->setWidth(12);

// ----- Filas de datos -----
$row = $startRow + 1;

$sumBase = 0.0;
$sumIGV = 0.0;
$sumImporte = 0.0;

$sumBasePEN = 0.0;
$sumIGVPEN = 0.0;
$sumImportePEN = 0.0;

foreach ($facturas as $f) {
    $n_registro = $f['n_registro'] ?? '';
    if (empty($n_registro)) {
        $n_registro = ($f['serie'] ?? '') . '-' . ($f['correlativo'] ?? $f['numero'] ?? $f['id_factura'] ?? '');
    }

    $fecha_emision = $f['fecha_emision'] ?? '';
    $fecha_venc = $f['fecha_vencimiento'] ?? ($f['fecha_pago'] ?? '');
    $tipo = $f['tipo_doc'] ?? $f['tipo_comprobante'] ?? '01';
    $serie = $f['serie'] ?? '';
    $numero = $f['correlativo'] ?? $f['numero'] ?? '';
    $tipo_doc_prov = '6';
    $ruc_prov = $f['ruc_emisor'] ?? $f['ruc_proveedor'] ?? '';
    $nombre_prov = $f['nombre_emisor'] ?? $f['nombre_proveedor'] ?? '';
    $descripcion = $f['descripcion'] ?? '';

    $base = (float) ($f['base_imponible'] ?? 0);
    $igv = (float) ($f['igv'] ?? 0);
    $importe = (float) ($f['importe_total'] ?? 0);
    $moneda = strtoupper($f['moneda'] ?? 'PEN');

    $tipo_cambio = isset($f['tipo_cambio']) ? (float)$f['tipo_cambio'] : 0;
    $otros = (float) ($f['otros_tributos'] ?? 0);

    $sheet->setCellValue('A' . $row, $n_registro);
    $sheet->setCellValue('B' . $row, $fecha_emision);
    $sheet->setCellValue('C' . $row, $fecha_venc);
    $sheet->setCellValue('D' . $row, $tipo);
    $sheet->setCellValue('E' . $row, $serie);
    $sheet->setCellValue('F' . $row, $numero);
    $sheet->setCellValue('G' . $row, $tipo_doc_prov);
    $sheet->setCellValue('H' . $row, $ruc_prov);
    $sheet->setCellValue('I' . $row, $nombre_prov);

    $sheet->setCellValue('J' . $row, $base);
    $sheet->setCellValue('K' . $row, $igv);
    $sheet->setCellValue('L' . $row, $importe);

    $sheet->setCellValue('M' . $row, $moneda);
    $sheet->setCellValue('N' . $row, $tipo_cambio > 0 ? $tipo_cambio : '');
    $sheet->setCellValue('O' . $row, $otros);

    foreach (range('A','O') as $c) {
        $sheet->getStyle($c . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
    $sheet->getStyle('J' . $row . ':L' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('O' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

    $sumBase += $base;
    $sumIGV += $igv;
    $sumImporte += $importe;

    if ($moneda !== 'PEN' && $tipo_cambio > 0) {
        $sumBasePEN += $base * $tipo_cambio;
        $sumIGVPEN += $igv * $tipo_cambio;
        $sumImportePEN += $importe * $tipo_cambio;
    } else {
        $sumBasePEN += $base;
        $sumIGVPEN += $igv;
        $sumImportePEN += $importe;
    }

    $row++;
}

// ----- Totales -----
$footerStart = $row + 1;
$sheet->mergeCells("A{$footerStart}:I{$footerStart}");
$sheet->setCellValue("A{$footerStart}", "TOTALES:");
$sheet->getStyle("A{$footerStart}")->getFont()->setBold(true);
$sheet->getStyle("A{$footerStart}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

$sheet->setCellValue('J' . $footerStart, $sumBase);
$sheet->setCellValue('K' . $footerStart, $sumIGV);
$sheet->setCellValue('L' . $footerStart, $sumImporte);
$sheet->setCellValue('O' . $footerStart, 0);

$sheet->getStyle('J' . $footerStart . ':L' . $footerStart)->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle('J' . $footerStart . ':L' . $footerStart)->getFont()->setBold(true);
$sheet->getStyle('J' . $footerStart . ':L' . $footerStart)->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);

// Totales en PEN
$footerStart2 = $row + 3;
$sheet->mergeCells("A{$footerStart2}:I{$footerStart2}");
$sheet->setCellValue("A{$footerStart2}", "TOTAL CONVERTIDO A PEN (Base - IGV - Importe):");
$sheet->getStyle("A{$footerStart2}")->getFont()->setBold(true);
$sheet->getStyle("A{$footerStart2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

$sheet->setCellValue('J' . $footerStart2, $sumBasePEN);
$sheet->setCellValue('K' . $footerStart2, $sumIGVPEN);
$sheet->setCellValue('L' . $footerStart2, $sumImportePEN);
$sheet->getStyle('J' . $footerStart2 . ':L' . $footerStart2)->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle('J' . $footerStart2 . ':L' . $footerStart2)->getFont()->setBold(true);
$sheet->getStyle('J' . $footerStart2 . ':L' . $footerStart2)->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);

// Congelar encabezado (compatible)
$sheet->freezePane('A' . ($startRow + 1));

// (opcional) ajustar zoom para que entre en pantalla
$sheet->getSheetView()->setZoomScale(90);

// Marcar filas en USD sin tipo de cambio
$checkRow = $startRow + 1;
while ($checkRow < $row) {
    $mon = $sheet->getCell('M' . $checkRow)->getValue();
    $tc = $sheet->getCell('N' . $checkRow)->getValue();
    if (strtoupper($mon) !== 'PEN' && (empty($tc) || $tc == 0)) {
        $sheet->getStyle("A{$checkRow}:O{$checkRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');
    }
    $checkRow++;
}

// Preparar descarga
$filename = 'Registro_Compras_' . ($empresa['razon_social'] ? preg_replace('/\s+/', '_', substr($empresa['razon_social'],0,20)) : 'empresa') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
