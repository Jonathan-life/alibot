<?php
require_once __DIR__ . '/../../Controllers/FacturasController.php';

$controller = new FacturasController();
$data = $controller->listarFacturas();

$empresa = $data['empresa'];
$facturas = $data['facturas'];
$fecha_inicio = $data['fecha_inicio'];
$fecha_fin = $data['fecha_fin'];
$idEmpresa = $data['idEmpresa'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Facturas - <?= htmlspecialchars($empresa['razon_social']) ?> (<?= htmlspecialchars($empresa['ruc']) ?>)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { padding-top: 40px; }
        .table th, .table td { vertical-align: middle; }
    </style>
</head>
<body>
<div class="container">
    <h3 class="mb-4">📄 Facturas de <strong><?= htmlspecialchars($empresa['razon_social']) ?></strong> (<?= htmlspecialchars($empresa['ruc']) ?>)</h3>

    <form method="GET" class="row g-3 mb-4 align-items-end">
        <input type="hidden" name="id_empresa" value="<?= $idEmpresa ?>">
        <div class="col-auto">
            <label for="fecha_inicio" class="form-label">Fecha inicio</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
        </div>
        <div class="col-auto">
            <label for="fecha_fin" class="form-label">Fecha fin</label>
            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="?id_empresa=<?= $idEmpresa ?>" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>

    <?php if (!empty($facturas)): ?>
        <table class="table table-bordered table-hover table-sm">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nro CPE</th>
                    <th>Emisor</th>
                    <th>Importe</th>
                    <th>Moneda</th>
                    <th>Fecha Emisión</th>
                    <th>Estado SUNAT</th>
                    <th>Archivo XML</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($facturas as $factura): ?>
                    <tr>
                        <td><?= $factura['id_factura'] ?></td>
                        <td><?= htmlspecialchars($factura['nro_cpe'] ?? '-') ?></td>
                        <td>
                            <?= htmlspecialchars($factura['nombre_emisor'] ?? '-') ?><br>
                            <small class="text-muted"><?= htmlspecialchars($factura['ruc_emisor'] ?? '-') ?></small>
                        </td>
                        <td><?= number_format((float)$factura['importe_total'], 2) ?></td>
                        <td><?= htmlspecialchars($factura['moneda'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($factura['fecha_emision'] ?? '-') ?></td>
                        <td>
                            <?php
                            $estado = htmlspecialchars($factura['estado_sunat'] ?? 'Desconocido');
                            $badge_class = match ($estado) {
                                'ACEPTADO' => 'success',
                                'RECHAZADO' => 'danger',
                                'OBSERVADO' => 'warning',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $badge_class ?>"><?= $estado ?></span>
                        </td>
                        <td>
                            <?php
                            $archivos = $controller->obtenerArchivos($factura['id_factura']);
                            $xml_encontrado = false;

                            if (!empty($archivos)) {
                                foreach ($archivos as $archivo) {
                                    if (strtoupper($archivo['tipo']) === 'ZIP') {
                                        echo "<a href='descargar.php?id={$archivo['id_archivo']}' class='btn btn-sm btn-outline-success'>📥 Descargar XML</a>";
                                        $xml_encontrado = true;
                                        break;
                                    }
                                }
                            }

                            if (!$xml_encontrado) {
                                echo "<span class='text-muted'>Sin XML</span>";
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">⚠ No hay facturas registradas para esta empresa en el rango de fechas seleccionado.</div>
    <?php endif; ?>

    <a href="venta_sire.php" class="btn btn-secondary mt-3">← Volver al listado de empresas</a>
</div>
</body>
</html>

<?php $controller->cerrarConexion(); ?>
