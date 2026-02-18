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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
          <li><a class="dropdown-item" href="../mantenimiento/sunat-og.php">Descargar</a></li>

          <!-- Submenú Permisos -->
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

          <!-- Submenú SUNAT -->
          <li class="dropdown-submenu">
            <a class="dropdown-item dropdown-toggle" href="#">SUNAT</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="../reportes/libro_contable.php">Libros Electrónicos</a></li>

              <!-- Submenú Compras -->
              <li class="dropdown-submenu">
                <a class="dropdown-item dropdown-toggle" href="#">Compras SIRE </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="../reportes/venta_sire.php">PDF</a></li>
                  <li><a class="dropdown-item" href="../reportes/ventasxml.php">XML</a></li>
                </ul>
              </li>

            <!-- Submenú Ventas -->
              <li class="dropdown-submenu">
                <a class="dropdown-item" href="">Ventas SIRE ▸</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="reportes/ventasiredv.php">PDF</a></li>
                  <li><a class="dropdown-item" href="reportes/ventaserexmlcd.php">XML</a></li>
                </ul>
              </li>

              <li><a class="dropdown-item" href="../reportes/admin_contable.php">Cuadro de cálculo</a></li>
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
                            <button class="btn btn-primary btn-sm" onclick="selectEmpresa(<?= $e['id_empresa'] ?>)">
                                Ver Registro
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>


<!-- MODAL: Selección de tipo (Compras / Ventas) -->
<div class="modal fade" id="modalTipo" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Seleccione tipo de registro</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <button id="btnCompras" class="btn btn-success me-3">Registro Compras</button>
        <button id="btnVentas" class="btn btn-info">Registro Ventas</button>
      </div>
    </div>
  </div>
</div>


<!-- MODAL: Selección de periodo -->
<div class="modal fade" id="modalPeriodo" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Seleccione Periodo</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <select id="selectPeriodo" class="form-select mb-3"></select>
        <button id="btnVerPeriodo" class="btn btn-primary">Ver Reporte</button>
      </div>
    </div>
  </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let empresaId = null;
let tipoRegistro = null;

// Paso 1 → Seleccionar empresa
function selectEmpresa(id) {
    empresaId = id;
    new bootstrap.Modal(document.getElementById('modalTipo')).show();
}

// Paso 2 → Cargar periodos según tipo
document.getElementById('btnCompras').addEventListener('click', () => cargarPeriodos('compras'));
document.getElementById('btnVentas').addEventListener('click', () => cargarPeriodos('ventas'));

function cargarPeriodos(tipo) {
    tipoRegistro = tipo;

    fetch(`../../api/get_periodos.php?empresa=${empresaId}&tipo=${tipo}`)
        .then(res => res.json())
        .then(periodos => {
            let select = document.getElementById('selectPeriodo');
            select.innerHTML = "";

            if (periodos.length === 0) {
                select.innerHTML = `<option>No hay periodos disponibles</option>`;
            } else {

                const meses = {
                    "01": "Enero",
                    "02": "Febrero",
                    "03": "Marzo",
                    "04": "Abril",
                    "05": "Mayo",
                    "06": "Junio",
                    "07": "Julio",
                    "08": "Agosto",
                    "09": "Septiembre",
                    "10": "Octubre",
                    "11": "Noviembre",
                    "12": "Diciembre"
                };

                periodos.forEach(periodo => {
                    let anio = periodo.substring(0, 4);
                    let mes = periodo.substring(4, 6);
                    let nombreMes = meses[mes] ?? "Mes desconocido";

                    let texto = `${anio} - ${nombreMes}`;

                    select.innerHTML += `<option value="${periodo}">${texto}</option>`;
                });
            }

            new bootstrap.Modal(document.getElementById('modalPeriodo')).show();
        })
        .catch(err => console.error("Error al cargar periodos:", err));
}


// Paso 3 → Ver reporte final
document.getElementById('btnVerPeriodo').addEventListener('click', () => {
    let periodo = document.getElementById('selectPeriodo').value;

    if (!periodo) {
        alert("Seleccione un periodo");
        return;
    }

    if (tipoRegistro === "compras") {
        window.location.href = `listado_empresas.php?id_empresa=${empresaId}&tipo=compras&periodo=${periodo}`;
    } else {
        window.location.href = `empresas_ventas.php?id_empresa=${empresaId}&tipo=ventas&periodo=${periodo}`;
    }
});
</script>

</body>
</html>
