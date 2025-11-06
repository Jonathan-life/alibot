<?php
require_once __DIR__ . '/../../Controllers/EmpresaController.php';

$controller = new EmpresaController();
$empresas = $controller->listarEmpresas(); // solo empresas activas
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Empresas Registradas</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Iconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="../style/index.css">
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4 py-3">
  <!-- Logo -->
  <div class="navbar-logo-container">
    <a class="navbar-brand fw-bold ms-4" href="#">
      <img src="../img/logcounting.png" alt="Logo" style="height:60px;">
    </a>
  </div>

  <!-- Botón responsive -->
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
    <span class="navbar-toggler-icon"></span>
  </button>

  <!-- Menú principal -->
  <div class="collapse navbar-collapse" id="menuNav">
    <ul class="navbar-nav ms-auto">
      <li class="nav-item"><a class="nav-link active" href="../index.php">Inicio</a></li>

      <!-- Mantenimiento -->
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

      <!-- Reportes -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Reportes</a>
        <ul class="dropdown-menu">
          <!-- SUNAT -->
          <li class="dropdown-submenu">
            <a class="dropdown-item dropdown-toggle" href="#">SUNAT</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Buzón Electrónico</a></li>
              <li><a class="dropdown-item" href="../reportes/libro_contable.php">Libros Electrónicos</a></li>

              <!-- Compras SIRE -->
              <li class="dropdown-submenu">
                <a class="dropdown-item dropdown-toggle" href="#">Compras SIRE</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="../reportes/venta_sire.php">PDF</a></li>
                  <li><a class="dropdown-item" href="../reportes/ventasxml.php">XML</a></li>
                </ul>
              </li>

              <!-- Ventas SIRE -->
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
      <a href="#" class="icon-link" title="Cerrar sesión"><i class="fas fa-power-off"></i></a>
      <a href="#" class="icon-link" title="Mi perfil"><i class="fas fa-user-circle"></i></a>
    </div>
  </div>
</nav>

<!-- Contenido principal -->
<div class="container mt-4">
  <h2 class="mb-4">Empresas Registradas</h2>

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

  <!-- Botón volver -->
  <a href="../index.php" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
