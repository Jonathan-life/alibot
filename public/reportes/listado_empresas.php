<?php
require_once __DIR__ . '/../../Controllers/FacturaController.php';
require_once __DIR__ . '/../../Controllers/EmpresaController.php';

$empresaController = new EmpresaController();
$facturaController = new FacturaController();

$id_empresa = $_GET['id_empresa'] ?? null;
$periodo = $_GET['periodo'] ?? null; // ← PERIODO SELECCIONADO

if (!$id_empresa) {
    $empresas = $empresaController->listarEmpresas();
    include 'libro_contable.php';
    exit;
}

$empresa = $empresaController->obtenerEmpresaPorId((int)$id_empresa);

// ==============================
// FILTRO POR PERIODO (YYYY-MM)
// ==============================
if ($periodo) {
    $facturas = $facturaController->listarFacturasPorPeriodo((int)$id_empresa, $periodo);
} else {
    // Sin periodo → carga todo
    $facturas = $facturaController->listarFacturasPorEmpresa((int)$id_empresa);
}

if (!$empresa || empty($facturas)) {
    die("<div class='alert alert-danger'>No se encontró empresa o facturas del periodo seleccionado.</div>");
}

// Mapeo de tipos SUNAT
$tipo_doc_sunat = [
    'FACTURA' => '01',
    'BOLETA' => '03',
    'NOTA DE CREDITO' => '07',
    'NOTA DE DEBITO' => '08',
];

// Inicializar totales generales
$total_base = 0;
$total_igv = 0;
$total_importe = 0;

// Totales por tipo de operación
$total_base_gravadas = 0;
$total_igv_gravadas = 0;

$total_base_exoneradas = 0;
$total_igv_exoneradas = 0;

$total_base_inafectas = 0;
$total_igv_inafectas = 0;

$total_no_gravadas = 0;
$total_otros_tributos = 0;

$total_igv_gratuitas = 0;
$total_igv_exportacion = 0;



// RECORRER FACTURAS
foreach ($facturas as $f) {

    $total_base_gravadas += (float)($f['base_gravadas'] ?? 0);
    $total_igv_gravadas  += (float)($f['igv'] ?? 0);

    $total_base_exoneradas += (float)($f['base_exoneradas'] ?? 0);
    $total_base_inafectas  += (float)($f['base_inafectas'] ?? 0);
    $total_no_gravadas     += (float)($f['no_gravadas'] ?? 0);

    $total_otros_tributos += (float)($f['otros_tributos'] ?? 0);
}

// TOTALES FINALES
$total_base_general =
    $total_base_gravadas +
    $total_base_exoneradas +
    $total_base_inafectas +
    $total_no_gravadas;

$total_igv_general = $total_igv_gravadas;
$total_importe_general = $total_base_general + $total_igv_general;


// --- Obtener parámetros de la URL ---
$tipo = $_GET['tipo'] ?? '';
$periodo = $_GET['periodo'] ?? ''; // Ejemplo: 202409

// --- Convertir periodo a "MES AÑO" ---
$meses = [
    "01" => "ENERO", "02" => "FEBRERO", "03" => "MARZO", "04" => "ABRIL",
    "05" => "MAYO", "06" => "JUNIO", "07" => "JULIO", "08" => "AGOSTO",
    "09" => "SETIEMBRE", "10" => "OCTUBRE", "11" => "NOVIEMBRE", "12" => "DICIEMBRE"
];

// Extraer año y mes
$anio = substr($periodo, 0, 4);
$mes  = substr($periodo, 4, 2);

$nombreMes = $meses[$mes] ?? "MES DESCONOCIDO";

// --- Nombre del Formato según tipo ---
if ($tipo === "compras") {
    $formato = "FORMATO 8.1: REGISTRO DE COMPRAS - MONEDA NACIONAL";
} else {
    $formato = "FORMATO 14.1: REGISTRO DE VENTAS - MONEDA NACIONAL";
}
?>




<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro de Compras - Formato SUNAT 8.1</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">



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
/* ================================
   CONFIGURACIÓN PROFESIONAL IMPRESIÓN
================================ */

@media print {

    @page {
        size: A4 landscape;
        margin: 5mm;
    }

    body {
        font-size: 9px;
        margin: 0;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    button, .btn, .no-print {
        display: none !important;
    }

    #capturaPDF {
        width: 100%;
        margin: 0;
    }

    table {
        page-break-inside: auto;
    }

    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
    }

    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

}

</style>
</head>
<body>
<div id="capturaPDF" class="container-fluid mt-3">


<div class="header-info">
    <h4><?= htmlspecialchars($empresa['razon_social']) ?></h4>
    <h5>RUC: <?= htmlspecialchars($empresa['ruc']) ?></h5>
    <p><?= $formato ?></p>
    <p>PERIODO: <?= $nombreMes . " " . $anio ?></p>
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

  

    <table class="table table-bordered" id="tablaCompras">
        <thead>
            <tr>
                <th rowspan="3">N° Registro</th>
                <th rowspan="2">Fecha del Dcto</th>
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

                <!-- Gravadas -->
                <td><?= number_format((float)$f['base_gravadas'], 2) ?></td>
                <td><?= number_format((float)$f['igv'], 2) ?></td>

                <!-- Exoneradas -> operaciones gravadas y no gravadas -->
                <td><?= number_format((float)$f['base_exoneradas'], 2) ?></td>
                <td>0.00</td>

                <!-- Inafectas -->
                <td><?= number_format((float)$f['base_inafectas'], 2) ?></td>
                <td>0.00</td>

                <!-- Gravadas no gravadas -->
                <td>0.00</td>

                <!-- Otros tributos -->
                <td>0.00</td>

                <!-- Total -->
                <td><?= number_format((float)$f['importe_total'], 2) ?></td>

                <!-- Tipo de cambio (si moneda = USD) -->
                <td><?= ($f['moneda'] == 'USD') ? '3.80' : '1.00' ?></td>

           
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="10" class="text-end">TOTALES:</td>

                <!-- Gravadas -->
                <td><?= number_format($total_base_gravadas, 2) ?></td>
                <td><?= number_format($total_igv_gravadas, 2) ?></td>

                <!-- Exoneradas -->
                <td><?= number_format($total_base_exoneradas, 2) ?></td>
                <td><?= number_format($total_igv_exoneradas, 2) ?></td>

                <!-- Inafectas -->
                <td><?= number_format($total_base_inafectas, 2) ?></td>
                <td><?= number_format($total_igv_inafectas, 2) ?></td>

                <!-- No gravadas -->
                <td><?= number_format($total_no_gravadas, 2) ?></td>

                <!-- Otros tributos -->
                <td><?= number_format($total_otros_tributos, 2) ?></td>

                <!-- Importe total -->
                <td><?= number_format($total_importe_general, 2) ?></td>

   

                <!-- Referencias -->
                <td colspan="4"></td>
            </tr>
        </tfoot>



    </table>

<div class="text-end mt-3">
   

<button class="btn btn-primary" onclick="imprimirRegistro()">
    Imprimir
</button>

    <button class="btn btn-danger" onclick="exportPDF()">Exportar a PDF</button>

</div>

</div>
<!-- jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- AutoTable (aunque no se usa, lo dejamos si lo necesitas luego) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<!-- html2canvas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<!-- XLSX + FileSaver -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>


<script>
    function imprimirRegistro() {
    window.print();
}

/* ============================================================
   EXPORTAR A PDF (captura completa con salto de páginas)
============================================================ */
function exportPDF() {

    const { jsPDF } = window.jspdf;
    const element = document.getElementById("capturaPDF");

    // Ocultar elementos no imprimibles
    const botones = document.querySelectorAll("button, .btn, .no-print");
    botones.forEach(b => b.style.display = "none");

    document.body.style.cursor = "wait";

    html2canvas(element, {
        scale: 2,
        useCORS: true,
        scrollY: -window.scrollY
    }).then(canvas => {

        const imgData = canvas.toDataURL("image/png");
        const pdf = new jsPDF("landscape", "pt", "a4");
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();

        const imgWidth = pageWidth - 20;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;

        let y = 0;

        if (imgHeight <= pageHeight) {
            pdf.addImage(imgData, "PNG", 10, 10, imgWidth, imgHeight);
        } else {
            while (y < canvas.height) {

                let canvasPart = document.createElement("canvas");
                let ctx = canvasPart.getContext("2d");

                canvasPart.width = canvas.width;
                canvasPart.height = pageHeight * canvas.width / pageWidth;

                ctx.drawImage(
                    canvas,
                    0, y,
                    canvas.width, canvasPart.height,
                    0, 0,
                    canvas.width, canvasPart.height
                );

                let partImg = canvasPart.toDataURL("image/png");
                pdf.addImage(partImg, "PNG", 10, 10, imgWidth, pageHeight - 20);

                y += canvasPart.height;

                if (y < canvas.height) pdf.addPage();
            }
        }

        pdf.save("Registro_Compras_81.pdf");
        document.body.style.cursor = "default";

        botones.forEach(b => b.style.display = "");

    });
}


async function exportPantallaExcel() {

    const table = document.querySelector("#tablaCompras");
    const wb = XLSX.utils.book_new();

    // Convertir tabla a hoja base
    let ws = XLSX.utils.table_to_sheet(table);

    // Aplicar fusionado del encabezado SUNAT
    ws['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 2, c: 0 } }, // N° registro
        { s: { r: 0, c: 1 }, e: { r: 2, c: 1 } },
        { s: { r: 0, c: 2 }, e: { r: 2, c: 2 } },

        // Comprobante de pago
        { s: { r: 0, c: 3 }, e: { r: 1, c: 5 } },

        // Info proveedor
        { s: { r: 0, c: 7 }, e: { r: 1, c: 9 } },

        // Adquisiciones
        { s: { r: 0, c: 10 }, e: { r: 0, c: 11 } },
        { s: { r: 0, c: 12 }, e: { r: 0, c: 13 } },
        { s: { r: 0, c: 14 }, e: { r: 0, c: 15 } },

        // Referencia
        { s: { r: 0, c: 20 }, e: { r: 1, c: 23 } },
    ];

    // FORMATO VISUAL – Bordes, alineación, tamaño
    const cellRange = XLSX.utils.decode_range(ws['!ref']);
    for (let R = cellRange.s.r; R <= cellRange.e.r; R++) {
        for (let C = cellRange.s.c; C <= cellRange.e.c; C++) {
            let cell = ws[XLSX.utils.encode_cell({ r: R, c: C })];
            if (cell) {
                cell.s = {
                    border: {
                        top: { style: "thin", color: "000000" },
                        left: { style: "thin", color: "000000" },
                        bottom: { style: "thin", color: "000000" },
                        right: { style: "thin", color: "000000" }
                    },
                    alignment: { vertical: "center", horizontal: "center", wrapText: true }
                };
            }
        }
    }

    // Autoajustar columnas según contenido
    ws['!cols'] = Array(cellRange.e.c).fill({ wch: 15 });

    // Insertar encabezado y totales antes de la tabla
    const info = [
        ["<?= addslashes($empresa['razon_social']) ?>"],
        ["RUC: <?= $empresa['ruc'] ?>"],
        ["FORMATO 8.1 - REGISTRO DE COMPRAS"],
        ["PERIODO: SETIEMBRE 2025"],
        [""],
        ["Total Base Imponible: <?= number_format($total_base, 2) ?>  |  Total IGV: <?= number_format($total_igv, 2) ?>   |   Total General: <?= number_format($total_importe, 2) ?>"],
        [""]
    ];

    const headerSheet = XLSX.utils.aoa_to_sheet(info);

    // Combinar hoja final
    XLSX.utils.sheet_add_json(headerSheet, XLSX.utils.sheet_to_json(ws, { header: 1 }), {
        skipHeader: true,
        origin: "A8"
    });

    XLSX.utils.book_append_sheet(wb, headerSheet, "Registro Compras");

    XLSX.writeFile(wb, "Registro_Compras_SUNAT_8.1.xlsx");
}

</script>


</body>
</html>
