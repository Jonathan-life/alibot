<?php
require_once __DIR__ . '/../../Controllers/EmpresaController.php';

if (!isset($_GET['id'])) {
    die("ID de empresa no proporcionado.");
}

$idEmpresa = intval($_GET['id']);
$controller = new EmpresaController();
$empresa = $controller->obtenerEmpresaPorId($idEmpresa);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - <?= htmlspecialchars($empresa['razon_social']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/styles.css" rel="stylesheet">
</head>
<body>
  <div class="d-flex">
    <div class="bg-dark text-white p-3" style="min-width: 250px; height: 100vh;">
      <h4>Panel</h4>
      <ul class="nav flex-column">
        <li class="nav-item"><a href="index.php" class="nav-link text-white">← Volver</a></li>
        <li class="nav-item"><a href="#" class="nav-link text-white">Facturas</a></li>
        <li class="nav-item"><a href="#" class="nav-link text-white">Estadísticas</a></li>
        <li class="nav-item"><a href="#" class="nav-link text-white">Documentos</a></li>
      </ul>
    </div>

    <div class="p-4 flex-grow-1">
      <h2>Dashboard: <?= htmlspecialchars($empresa['razon_social']) ?></h2>
      <p><strong>RUC:</strong> <?= $empresa['ruc'] ?></p>

      <!-- Aquí puedes cargar stats con JS/AJAX -->
      <div id="stats">
        <p>Cargando estadísticas...</p>
      </div>
    </div>
  </div>

  <script src="js/dashboard.js"></script>
</body>
</html>
