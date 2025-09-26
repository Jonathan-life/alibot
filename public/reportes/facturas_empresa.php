<?php
require_once __DIR__ . "/../../Controllers/EmpresaController.php";

if (!isset($_GET['id_empresa'])) {
    die("Empresa no especificada.");
}

$idEmpresa = intval($_GET['id_empresa']);

$controller = new EmpresaController();
$empresa = $controller->obtenerEmpresaConFacturas($idEmpresa);
if (!$empresa) {
    die("Empresa no encontrada.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Facturas de <?= htmlspecialchars($empresa['razon_social']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="container mt-4">

    <h3>Facturas de <?= htmlspecialchars($empresa['razon_social']) ?> (<?= $empresa['ruc'] ?>)</h3>

    <?php if (count($empresa['facturas']) > 0): ?>
        <table class="table table-bordered table-sm table-hover mt-3">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nro CPE</th>
                    <th>Receptor</th>
                    <th>Importe</th>
                    <th>Moneda</th>
                    <th>Fecha Emisión</th>
                    <th>Estado</th>
                    <th>Archivo</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($empresa['facturas'] as $factura): ?>
                <tr>
                    <td><?= $factura['id_factura'] ?></td>
                    <td><?= htmlspecialchars($factura['nro_cpe']) ?></td>
                    <td><?= htmlspecialchars($factura['receptor_nombre'] ?? '-') ?> (<?= htmlspecialchars($factura['receptor_ruc'] ?? '-') ?>)</td>
                    <td><?= number_format($factura['importe_total'], 2) ?></td>
                    <td><?= htmlspecialchars($factura['moneda'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($factura['fecha_emision']) ?></td>
                    <td><?= htmlspecialchars($factura['estado']) ?></td>
                    <td>
                        <?php if ($factura['id_archivo']): ?>
                            <a href="descargar.php?id=<?= $factura['id_archivo'] ?>" class="btn btn-sm btn-primary">Descargar ZIP</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No hay facturas para esta empresa.</div>
    <?php endif; ?>

    <a href="venta_sire.php" class="btn btn-secondary mt-3">← Volver al listado de empresas</a>

</body>
</html>
