<?php
require_once __DIR__ . '/../../Controllers/EmpresaController.php';
$empresaController = new EmpresaController();

// Obtener lista de empresas
$empresas = $empresaController->listarEmpresas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Seleccionar Empresa</title>
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
<div class="container mt-5">
    <h2 class="mb-4 text-center">Seleccione una Empresa</h2>

    <?php if (empty($empresas)): ?>
        <div class="alert alert-warning">No se encontraron empresas registradas.</div>
    <?php else: ?>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>RUC</th>
                    <th>Razón Social</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empresas as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['id_empresa']) ?></td>
                        <td><?= htmlspecialchars($e['ruc']) ?></td>
                        <td><?= htmlspecialchars($e['razon_social']) ?></td>
                        <td>
                            <a href="listado_empresas?id_empresa=<?= $e['id_empresa'] ?>" 
                               class="btn btn-primary btn-sm">
                               Ver Registro de Compras
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
