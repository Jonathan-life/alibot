<?php 
// 🔹 Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "sistema_contable");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// 🔹 Verificar si se ha especificado la empresa
if (!isset($_GET['id_empresa'])) {
    die("Empresa no especificada.");
}

$idEmpresa = intval($_GET['id_empresa']);

// 🔹 Obtener la empresa y las facturas asociadas
$sql_empresa = "SELECT * FROM empresas WHERE id_empresa = $idEmpresa";
$result_empresa = $conexion->query($sql_empresa);

if (!$result_empresa || $result_empresa->num_rows == 0) {
    die("Empresa no encontrada.");
}

$empresa = $result_empresa->fetch_assoc();

// 🔹 Traer las facturas asociadas a esta empresa
$sql_facturas = "SELECT * FROM facturas WHERE id_empresa = $idEmpresa ORDER BY id_factura";
$result_facturas = $conexion->query($sql_facturas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Facturas de <?= htmlspecialchars($empresa['razon_social']) ?> (<?= $empresa['ruc'] ?>)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

    <h3>Facturas de <?= htmlspecialchars($empresa['razon_social']) ?> (<?= $empresa['ruc'] ?>)</h3>

    <?php if ($result_facturas->num_rows > 0): ?>
        <table class="table table-bordered table-sm table-hover mt-3">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nro CPE</th>
                    <th>Emisor</th> 
                    <th>Importe</th>
                    <th>Moneda</th>
                    <th>Fecha Emisión</th>
                    <th>Estado</th>
                    <th>Archivo</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($factura = $result_facturas->fetch_assoc()): ?>
                <tr>
                    <td><?= $factura['id_factura'] ?></td>
                    <td><?= htmlspecialchars($factura['nro_cpe']) ?></td>
                    <td>
                        <!-- Mostrar el nombre y RUC del emisor -->
                        <?= htmlspecialchars($factura['emisor_nombre']) ?> (<?= htmlspecialchars($factura['emisor_ruc']) ?>)
                    </td>
                    <td><?= number_format($factura['importe_total'], 2) ?></td>
                    <td><?= htmlspecialchars($factura['moneda'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($factura['fecha_emision']) ?></td>
                    <td><?= htmlspecialchars($factura['estado']) ?></td>
                    <td>
                        <?php
                        // 🔹 Archivos asociados a esta factura
                        $id_factura = $factura['id_factura'];
                        $sql_archivos = "SELECT * FROM archivos_factura WHERE id_factura = $id_factura ORDER BY tipo";
                        $res_archivos = $conexion->query($sql_archivos);

                        while ($archivo = $res_archivos->fetch_assoc()) {
                            $etiqueta = strtoupper($archivo['tipo']); // ZIP o PDF
                            echo "<a href='descargar.php?id={$archivo['id_archivo']}' 
                                    class='btn btn-sm btn-outline-primary me-1'>
                                    Descargar $etiqueta
                                  </a>";
                        }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No hay facturas para esta empresa.</div>
    <?php endif; ?>

    <a href="venta_sire.php" class="btn btn-secondary mt-3">← Volver al listado de empresas</a>

</body>
</html>

<?php $conexion->close(); ?>
