<?php
require_once __DIR__ . '/../../Controllers/EmpresaController.php';
$controller = new EmpresaController();
$empresas = $controller->listarEmpresas();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Empresas Registradas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  <h2>Empresas Registradas</h2>
  <table class="table table-hover">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>RUC</th>
        <th>Razón Social</th>
        <th>Estado</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($empresas as $i => $empresa): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= htmlspecialchars($empresa['ruc']) ?></td>
          <td><?= htmlspecialchars($empresa['razon_social']) ?></td>
          <td>
            <span class="badge bg-<?= ($empresa['estado'] === 'ACTIVO') ? 'success' : 'secondary' ?>">
              <?= htmlspecialchars($empresa['estado'] ?? 'Desconocido') ?>
            </span>
          </td>
          <td>
            <a href="dashboard.php?id=<?= urlencode($empresa['id_empresa']) ?>" class="btn btn-sm btn-primary">Ver Dashboard</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
