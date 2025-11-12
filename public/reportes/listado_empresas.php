<?php
require_once __DIR__ . '/../../Controllers/FacturaController.php';
require_once __DIR__ . '/../../Controllers/EmpresaController.php';

$empresaController = new EmpresaController();
$facturaController = new FacturaController();

$id_empresa = $_GET['id_empresa'] ?? null;

if (!$id_empresa) {
    $empresas = $empresaController->listarEmpresas();
    include 'libro_contable.php';
    exit;
}

$empresa = $empresaController->obtenerEmpresaPorId((int)$id_empresa);
$facturas = $facturaController->listarFacturasPorEmpresa((int)$id_empresa);

if (!$empresa || empty($facturas)) {
    die("<div class='alert alert-danger'>No se encontró empresa o facturas.</div>");
}

// Mapeo de tipos de documentos a códigos SUNAT
$tipo_doc_sunat = [
    'FACTURA' => '01',
    'BOLETA' => '03',
    'NOTA DE CREDITO' => '07',
    'NOTA DE DEBITO' => '08',
];

// Totales
$total_base = 0;
$total_igv = 0;
$total_importe = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro de Compras - Formato SUNAT 8.1</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<style>
body { font-family: Arial, sans-serif; font-size: 11px; background-color: #fff; }
h4, h5, p { text-align: center; margin: 0; padding: 0; }
.table { border-collapse: collapse; width: 100%; text-align: center; }
.table th, .table td { border: 1px solid #000; padding: 3px; vertical-align: middle; }
.table thead tr th { background-color: #eaeaea; font-weight: bold; }
.header-info { text-align: center; margin-bottom: 10px; }
.resumen-totales { background-color: #f8f9fa; padding: 8px; border: 1px solid #ccc; margin-bottom: 10px; }
.resumen-totales strong { margin-right: 15px; }
tfoot td { font-weight: bold; background-color: #eaeaea; }
</style>
</head>
<body>
<div class="container-fluid mt-3">
    <div class="header-info">
        <h4><?= htmlspecialchars($empresa['razon_social']) ?></h4>
        <h5>RUC: <?= htmlspecialchars($empresa['ruc']) ?></h5>
        <p>FORMATO 8.1: REGISTRO DE COMPRAS - MONEDA NACIONAL</p>
        <p>PERIODO: SETIEMBRE 2025</p>
    </div>

    <?php
    // Calcular totales antes de mostrar
    foreach ($facturas as $f) {
        $base = (float)($f['base_imponible_gravadas'] ?? $f['base_imponible']);
        $igv = (float)($f['igv_gravadas'] ?? $f['igv']);
        $total = (float)$f['importe_total'];
        $total_base += $base;
        $total_igv += $igv;
        $total_importe += $total;
    }
    ?>

    <div class="resumen-totales text-center">
        <strong>Total Base Imponible:</strong> S/ <?= number_format($total_base, 2) ?>
        <strong>Total IGV:</strong> S/ <?= number_format($total_igv, 2) ?>
        <strong>Total General:</strong> S/ <?= number_format($total_importe, 2) ?>
    </div>

    <table class="table table-bordered" id="tablaCompras">
        <thead>
            <tr>
                <th rowspan="3">N° Registro</th>
                <th rowspan="3">Fecha del Dcto</th>
                <th rowspan="3">Fecha de Vcto o Pago</th>
                <th colspan="3">Comprobante de Pago o Documento</th>
                <th rowspan="3">N° del Comprobante o N° de DUA</th>
                <th colspan="3">Información del Proveedor</th>
                <th colspan="2">Adquisiciones Gravadas destinadas a operaciones gravadas y/o de exportación</th>
                <th colspan="2">Adquisiciones Gravadas destinadas a operaciones gravadas y/o no gravadas</th>
                <th colspan="2">Adquisiciones Gravadas destinadas a operaciones no gravadas</th>
                <th rowspan="3">Adquisiciones Gravadas no gravadas</th>
                <th rowspan="3">Otros Tributos y cargos</th>
                <th rowspan="3">Importe Total</th>
                <th rowspan="3">Tipo de Cambio</th>
                <th colspan="4">Referencia del comprob. de pago o doc original que se modifica</th>
            </tr>
            <tr>
                <th rowspan="2">Tipo</th>
                <th rowspan="2">N° Serie</th>
                <th rowspan="2">Año DUA</th>
                <th>Tipo Doc.</th>
                <th>Número</th>
                <th rowspan="2">Apellidos y Nombres / Razón Social</th>
                <th rowspan="2">Base Imponible</th>
                <th rowspan="2">IGV</th>
                <th rowspan="2">Base Imponible</th>
                <th rowspan="2">IGV</th>
                <th rowspan="2">Base Imponible</th>
                <th rowspan="2">IGV</th>
                <th rowspan="2">Fecha</th>
                <th rowspan="2">Tipo</th>
                <th rowspan="2">Serie</th>
                <th rowspan="2">N° del Comprobante de pago o documento</th>
            </tr>
        </thead>

        <tbody>
        <?php $i = 1; foreach ($facturas as $f): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($f['fecha_emision'] ?? '') ?></td>
                <td><?= htmlspecialchars($f['fecha_vencimiento'] ?? '') ?></td>

                <!-- Comprobante -->
                <td><?= htmlspecialchars($tipo_doc_sunat[$f['tipo_doc']] ?? '99') ?></td>
                <td><?= htmlspecialchars($f['serie'] ?? '') ?></td>
                <td><?= htmlspecialchars(date('Y', strtotime($f['fecha_emision'] ?? 'now'))) ?></td>
                <td><?= htmlspecialchars($f['correlativo'] ?? '') ?></td>

                <!-- Proveedor -->
                <td><?= htmlspecialchars($f['tipo_doc_identidad'] ?? '6') ?></td>
                <td><?= htmlspecialchars($f['ruc_emisor'] ?? '') ?></td>
                <td><?= htmlspecialchars($f['nombre_emisor'] ?? '') ?></td>

                <!-- Adquisiciones -->
                <td><?= number_format((float)($f['base_imponible_gravadas'] ?? $f['base_imponible']), 2) ?></td>
                <td><?= number_format((float)($f['igv_gravadas'] ?? $f['igv']), 2) ?></td>
                <td><?= number_format((float)($f['base_imponible_mixtas'] ?? 0), 2) ?></td>
                <td><?= number_format((float)($f['igv_mixtas'] ?? 0), 2) ?></td>
                <td><?= number_format((float)($f['base_imponible_no_gravadas'] ?? 0), 2) ?></td>
                <td><?= number_format((float)($f['igv_no_gravadas'] ?? 0), 2) ?></td>

                <td>0.00</td>
                <td>0.00</td>

                <td><?= number_format((float)$f['importe_total'], 2) ?></td>
                <td><?= htmlspecialchars($f['moneda'] ?? '') ?></td>

                <!-- Referencias -->
                <td></td><td></td><td></td><td></td>
            </tr>
        <?php endforeach; ?>
        </tbody>

        <tfoot>
            <tr>
                <td colspan="10" class="text-end">TOTALES:</td>
                <td><?= number_format($total_base, 2) ?></td>
                <td><?= number_format($total_igv, 2) ?></td>
                <td colspan="5"></td>
                <td><?= number_format($total_importe, 2) ?></td>
                <td colspan="6"></td>
            </tr>
        </tfoot>
    </table>

    <div class="text-end mt-3">
        <button class="btn btn-success" onclick="exportTableToExcel('tablaCompras', 'Registro_Compras')">Exportar a Excel</button>
    </div>
</div>

<script>
function exportTableToExcel(tableID, filename = '') {
    const wb = XLSX.utils.table_to_book(document.getElementById(tableID), { sheet: "RegistroCompras" });
    XLSX.writeFile(wb, filename || "Registro_Compras.xlsx");
}
</script>
</body>
</html>
