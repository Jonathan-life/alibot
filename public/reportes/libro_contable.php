<?php
require_once __DIR__ . '/../../Controllers/FacturaController.php';
require_once __DIR__ . '/../../Controllers/EmpresaController.php';

$empresaController = new EmpresaController();
$facturaController = new FacturaController();

// Obtener id_empresa desde GET
$id_empresa = $_GET['id_empresa'] ?? null;
$tipo_cambio = 3.85; // USD -> PEN

// -----------------------------
// 1️⃣ Listado de empresas si no se recibió id_empresa
// -----------------------------
if (!$id_empresa) {
    $empresas = $empresaController->listarEmpresas();
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Empresas Registradas</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
          <link rel="stylesheet" href="../style/index.css">
    </head>
    <body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4 py-3">
        <div class="navbar-logo-container">
            <a class="navbar-brand fw-bold ms-4" href="#">
                <img src="../img/logcounting.png" alt="Logo" style="height:60px;">
            </a>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="../index.php">Inicio</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Mantenimiento</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../mantenimiento/usuario.php">Usuarios</a></li>
                        <li><a class="dropdown-item" href="../mantenimiento/empresa.php">Empresas</a></li>
                        <li><a class="dropdown-item" href="../mantenimiento/sunat-og.php">Descargar</a></li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#">Permisos</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="../permisos/usuario.php">Usuarios</a></li>
                                <li><a class="dropdown-item" href="../permisos/empresa.php">Empresas</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Reportes</a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#">SUNAT</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Buzón Electrónico</a></li>
                                <li><a class="dropdown-item" href="../reportes/libro_contable.php">Libros Electrónicos</a></li>
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">Compras SIRE</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="../reportes/venta_sire.php">PDF</a></li>
                                        <li><a class="dropdown-item" href="../reportes/ventasxml.php">XML</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">Ventas SIRE</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">PDF</a></li>
                                        <li><a class="dropdown-item" href="#">XML</a></li>
                                    </ul>
                                </li>
                                <li><a class="dropdown-item" href="../reportes/admin_contable.php">Cuadro de cálculo</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="#">Requerimientos</a></li>
            </ul>
    <div class="navbar-icons-container iconos-navbar d-flex gap-3 ms-auto">
    <a href="#" class="icon-link" title="Cerrar sesión">
      <i class="fas fa-power-off"></i>
    </a>
    <a href="#" class="icon-link" title="Mi perfil">
      <i class="fas fa-user-circle"></i>
    </a>
  </div>

</nav>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Empresas Registradas</h2>
        <div class="row">
            <?php foreach ($empresas as $empresa): ?>
                <div class="col-md-4 mb-4">
                    <div class="card border-primary shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($empresa['razon_social']) ?></h5>
                            <p class="card-text"><strong>RUC:</strong> <?= htmlspecialchars($empresa['ruc']) ?></p>
                            <a href="?id_empresa=<?= $empresa['id_empresa'] ?>" class="btn btn-sm btn-outline-primary">Ver facturas</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// -----------------------------
// 2️⃣ Si se recibió id_empresa, generar archivos
// -----------------------------
$empresa = $empresaController->obtenerEmpresaPorId((int)$id_empresa);
$facturas = $facturaController->listarFacturasPorEmpresa((int)$id_empresa);

if (!$empresa || empty($facturas)) {
    die("<div class='alert alert-danger'>No se encontró empresa o facturas.</div>");
}

// Crear carpeta de salida
$outputDir = __DIR__ . "/output";
if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);

// Generar TXT Libro Electrónico
$campos_txt = [
    'id_factura','tipo_doc','serie','correlativo','fecha_emision',
    'ruc_emisor','nombre_emisor','ruc_receptor','nombre_receptor',
    'base_imponible','igv','importe_total','moneda'
];
$lineas_txt = [];
foreach ($facturas as $f) {
    $fila = [];
    foreach ($campos_txt as $campo) {
        $valor = $f[$campo] ?? '';
        if (in_array($campo,['base_imponible','igv','importe_total']) && $f['moneda']==='USD') {
            $valor *= $tipo_cambio;
        }
        $fila[] = $valor;
    }
    $lineas_txt[] = implode('|',$fila);
}
$txtFile = "output/LE_Compras_{$empresa['ruc']}.txt";
file_put_contents(__DIR__ . "/$txtFile", implode("\n",$lineas_txt));

// Generar CSV Asientos Contables
$csvFile = "output/Asientos_Compras_{$empresa['ruc']}.csv";
$csv = fopen(__DIR__ . "/$csvFile",'w');
fputcsv($csv,['fecha_emision','cuenta_debe','cuenta_haber','monto','descripcion']);
foreach ($facturas as $f) {
    $base = $f['base_imponible'];
    $igv = $f['igv'];
    if($f['moneda']==='USD'){
        $base *= $tipo_cambio;
        $igv *= $tipo_cambio;
    }
    fputcsv($csv, [$f['fecha_emision'],'6001','2001',number_format($base,2,'.',''),"Compra a {$f['nombre_emisor']}"]);
    fputcsv($csv, [$f['fecha_emision'],'IGV_credito','2001',number_format($igv,2,'.',''),"IGV crédito fiscal"]);
}
fclose($csv);

// -----------------------------
// 3️⃣ Mostrar enlaces de descarga
// -----------------------------
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Archivos Generados</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="container py-5">
    <h3>Archivos generados para <?= htmlspecialchars($empresa['razon_social']) ?></h3>
    <ul class="list-group mb-3">
        <li class="list-group-item">
            <a href="<?= $txtFile ?>" target="_blank" class="btn btn-success">
                <i class="fas fa-file-alt"></i> Descargar TXT Libro Electrónico
            </a>
        </li>
        <li class="list-group-item">
            <a href="<?= $csvFile ?>" target="_blank" class="btn btn-primary">
                <i class="fas fa-file-csv"></i> Descargar CSV Asientos Contables
            </a>
        </li>
    </ul>
    <a href="?" class="btn btn-secondary">Volver al listado de empresas</a>
</div>
</body>
</html>
