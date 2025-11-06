<?php
require_once __DIR__ . "/../../Controllers/EmpresaController.php";

$controller = new EmpresaController();
$empresas = $controller->listarEmpresas(); // solo empresas activas
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Empresas</title>

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

      <!-- Inicio -->
      <li class="nav-item">
        <a class="nav-link active" href="../index.php">Inicio</a>
      </li>

      <!-- Mantenimiento -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Mantenimiento</a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="../mantenimiento/usuario.php">Usuarios</a></li>
          <li><a class="dropdown-item" href="../mantenimiento/empresa.php">Empresas</a></li>
          <li><a class="dropdown-item" href="../mantenimiento/sunat-og">Descargar</a></li>

          <!-- Submenú Permisos -->
          <li class="dropdown-submenu">
            <a class="dropdown-item dropdown-toggle" href="#">Permisos</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="../permisos/usuario">Usuarios</a></li>
              <li><a class="dropdown-item" href="../permisos/empresa">Empresas</a></li>
            </ul>
          </li>
        </ul>
      </li>

      <!-- Reportes -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Reportes</a>
        <ul class="dropdown-menu">

          <!-- Submenú SUNAT -->
          <li class="dropdown-submenu">
            <a class="dropdown-item dropdown-toggle" href="#">SUNAT</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Buzón Electrónico</a></li>
              <li><a class="dropdown-item" href="../reportes/libro_contable">Libros Electrónicos</a></li>

              <!-- Submenú Compras -->
              <li class="dropdown-submenu">
                <a class="dropdown-item dropdown-toggle" href="#">Compras SIRE </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="../reportes/venta_sire">PDF</a></li>
                  <li><a class="dropdown-item" href="../reportes/ventasxml">XML</a></li>
                </ul>
              </li>

              <!-- Submenú Ventas -->
              <li class="dropdown-submenu">
                <a class="dropdown-item dropdown-toggle" href="#">Ventas SIRE </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#">PDF</a></li>
                  <li><a class="dropdown-item" href="#">XML</a></li>
                </ul>
              </li>

              <li><a class="dropdown-item" href="../reportes/admin_contable">Cuadro de cálculo</a></li>
            </ul>
          </li>

          <!-- Submenú SUNAFIL -->

        </ul>
      </li>

      <!-- Requerimientos -->
      <li class="nav-item">
        <a class="nav-link" href="#">Requerimientos</a>
      </li>

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

<!-- Contenido principal -->
<div class="container mt-5">
  <h2 class="text-center mb-4">Empresas Registradas</h2>

  <div class="row">
    <?php foreach ($empresas as $empresa): ?>
      <div class="col-md-4 mb-4">
        <div class="card border-primary shadow-sm">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($empresa['razon_social']) ?></h5>
            <p class="card-text">
              <strong>RUC:</strong> <?= htmlspecialchars($empresa['ruc']) ?>
            </p>
            <a href="facturas_empresa.php?id_empresa=<?= $empresa['id_empresa'] ?>" class="btn btn-sm btn-outline-primary">
              Ver facturas con razon social fa-arrow-left
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  </div>

  <!-- Botón Volver -->
  <div class="text-center mt-4">
    <a href="../index.php" class="btn btn-secondary">
      <i class="fa fa-arrow-left"></i> Volver
    </a>
  </div>
</div>


<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
