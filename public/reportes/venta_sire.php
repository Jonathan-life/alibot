<?php
require_once __DIR__ . "/../../Controllers/EmpresaController.php";

$controller = new EmpresaController();
$empresas = $controller->listarEmpresas(); // <- solo empresas activas
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Empresas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="container mt-4">

    <h2 class="mb-4">Empresas Registradas</h2>

    <div class="row">
        <?php foreach ($empresas as $empresa): ?>
            <div class="col-md-4 mb-4">
                <div class="card border-primary shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($empresa['razon_social']) ?></h5>
                        <p class="card-text"><strong>RUC:</strong> <?= htmlspecialchars($empresa['ruc']) ?></p>
                        <a href="facturas_empresa.php?id_empresa=<?= $empresa['id_empresa'] ?>" class="btn btn-sm btn-outline-primary">
                            Ver facturas
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
