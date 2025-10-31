<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Empresas</title>
  <link rel="stylesheet" href="index.css">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Iconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style/index.css">


</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4 py-3">

  <!-- Contenedor del LOGO -->
  <div class="navbar-logo-container">
    <a class="navbar-brand fw-bold ms-4" href="#">
      <img src="img/logcounting.png" alt="Logo" style="height:60px;">
    </a>
  </div>

  <!-- Botón hamburguesa para responsive -->
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
    <span class="navbar-toggler-icon"></span>
  </button>

  <!-- Contenedor del MENÚ -->
  <div class="navbar-menu-container">
    <ul class="navbar-nav" id="menuNav">
      <li class="nav-item"><a class="nav-link active" href="index.php">Inicio</a></li>

      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button">Mantenimiento</a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="mantenimiento/usuario.php">Usuarios</a></li>
          <li><a class="dropdown-item" href="mantenimiento/empresa.php">Empresas</a></li>
          <li><a class="dropdown-item" href="mantenimiento/sunat-og.php">Descargar</a></li>
          <!-- Submenú Permisos -->
          <li class="dropdown-submenu">
            <a class="dropdown-item" href="#">Permisos</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="permisos/usuario.php">Usuarios</a></li>
              <li><a class="dropdown-item" href="permisos/empresa.php">Empresas</a></li>
            </ul>
          </li>
        </ul>
      </li>

      <li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle" href="#">Reportes</a>
  <ul class="dropdown-menu">
    <!-- Opciones en fila -->
<li class="dropdown-submenu">
  <a class="dropdown-item" href="#">SUNAT</a>
  <ul class="dropdown-menu">
    <li><a class="dropdown-item" href="#">Buzón Electrónico</a></li>
    <li><a class="dropdown-item" href="#">Libros Electrónicos</a></li>

    <!-- Submenú Compras -->
    <li class="dropdown-submenu">
      <a class="dropdown-item" href="#">Compras SIRE ▸</a>
      <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="reportes/venta_sire.php">PDF</a></li>
          <li><a class="dropdown-item" href="reportes/ventasxml.php">XML</a></li>
      </ul>
    </li>

    <!-- Submenú Ventas -->
      <li class="dropdown-submenu">
        <a class="dropdown-item" href="">Ventas SIRE ▸</a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="">PDF</a></li>
          <li><a class="dropdown-item" href="">XML</a></li>
        </ul>
      </li>

      <li><a class="dropdown-item" href="#">Detracciones</a></li>
      <li><a class="dropdown-item" href="reportes/admin_contable.php">Cuadro de calculo</a></li>
    </ul>
  </li>

    <li class="dropdown-submenu">
      <a class="dropdown-item" href="#">SUNAFIL</a>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="#">Inspecciones</a></li>
        <li><a class="dropdown-item" href="#">Multas</a></li>
      </ul>
    </li>
  </ul>
</li>


      <li class="nav-item"><a class="nav-link" href="#">Requerimientos</a></li>
    </ul>
  </div>

  <!-- Contenedor de ICONOS -->
  <div class="navbar-icons-container iconos-navbar d-flex gap-3 ms-auto">
    <a href="#" class="icon-link" title="Cerrar sesión">
      <i class="fas fa-power-off"></i>
    </a>
    <a href="#" class="icon-link" title="Mi perfil">
      <i class="fas fa-user-circle"></i>
    </a>
  </div>

</nav>

<!-- Sección con fondo -->
<div class="header-section">
  <!-- Contenedor general -->
  <div class="container py-5">
    <!-- Header de empresas -->
    <div class="empresas-header">
      <div class="empresas-flex">
        <div>
          <h2 class="empresas-title mb-0">Empresas Registradas</h2>
          <div class="empresas-fecha small" id="fecha-hora"></div>
        </div>

        <div class="contenedorbtn">
          <a href="empresa/registro.php" class="btn-agregar">
            <img src="img/phplusfgfdfill.png" alt="Agregar" class="icono">
            Agregar nuevo
          </a>
        </div>

      </div>
    </div>
  </div>
</div>



<!-- Caja de búsqueda -->
<div class="container-search">
  <div class="search-box-wrapper py-3">
    <div class="input-group rounded-pill border p-1 mx-auto" style="max-width: 1237px;">
      <span class="input-group-text border-0 bg-transparent">
        <i class="fas fa-search text-muted"></i>
      </span>
      <input type="text" class="form-control border-0" placeholder="Buscar por Nº de RUC">
    </div>
  </div>
</div>


<div class="container-table">
  <table class="tabla" id="tablaEmpresas">
    <thead>
      <tr>
        <th>N°</th>
        <th>RUC</th>
        <th>RAZÓN SOCIAL</th>
        <th>ESTADO</th>
        <th>ACCIÓN</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>
<!-- Modal de Confirmación -->
<div class="modal fade" id="modalConfirmarEliminacion" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalLabel">Confirmar eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        ¿Estás seguro de que deseas eliminar esta empresa?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Sí, eliminar</button>
      </div>
    </div>
  </div>
</div>

<script>
const tbody = document.querySelector(".tabla tbody");

fetch("/alibot/api/listar_empresas.php")
  .then(res => res.json())
  .then(res => {
    if (res.success) {
      tbody.innerHTML = ""; // limpiar tabla
      res.data.forEach((empresa, index) => {
        tbody.innerHTML += `
          <tr>
            <td>${index + 1}</td>
            <td>${empresa.ruc}</td>
            <td>${empresa.razon_social}</td>
            <td><span class="badge-${empresa.estado.toLowerCase()}">${empresa.estado}</span></td>
            <td>
              <div class="acciones" style="position: relative;">
              <button class="btn-accionar btn-borrar" data-id="${empresa.id_empresa}" title="Eliminar">
                <img src="img/basurero.png" alt="Eliminar" class="icono-btn">
              </button>
                <button class="btn-accionar btn-menu" title="Más opciones">
                  <img src="img/opcciones.png" alt="Opciones" class="icono-btn">
                </button>
                <div class="menu-opciones">
                  <a href="#">Desactivar</a>
                  <a href="#">Descargar</a>
                </div>
              </div>
            </td>

          </tr>
        `;
      });
    } else {
      console.error(res.error);
    }
  })
  .catch(err => console.error(err));

</script>



<script>
  function actualizarFechaHora() {
    const ahora = new Date();
    const opciones = {
      day: '2-digit', month: '2-digit', year: 'numeric',
      hour: '2-digit', minute: '2-digit'
    };
    document.getElementById("fecha-hora").textContent =
      ahora.toLocaleString('es-PE', opciones);
  }

  actualizarFechaHora();          // primera ejecución
  setInterval(actualizarFechaHora, 60000); // actualizar cada minuto
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const tabla = document.querySelector(".tabla");

  // Escucha todos los clics en la tabla
  tabla.addEventListener("click", function (e) {
    // Si se hizo clic en un botón .btn-menu
    if (e.target.closest(".btn-menu")) {
      e.stopPropagation();
      const btn = e.target.closest(".btn-menu");
      const acciones = btn.closest(".acciones");
      const menu = acciones.querySelector(".menu-opciones");

      // Cerrar todos los otros menús
      document.querySelectorAll(".menu-opciones").forEach(m => {
        if (m !== menu) m.style.display = "none";
      });

      // Mostrar/ocultar el menú actual
      menu.style.display = (menu.style.display === "block") ? "none" : "block";
    }
  });

  // Cerrar el menú si se hace clic fuera
  window.addEventListener("click", () => {
    document.querySelectorAll(".menu-opciones").forEach(menu => {
      menu.style.display = "none";
    });
  });
});
</script>
<script>
let empresaIdParaEliminar = null;

document.addEventListener("click", function (e) {
  if (e.target.closest(".btn-borrar")) {
    const btn = e.target.closest(".btn-borrar");
    empresaIdParaEliminar = btn.getAttribute("data-id");

    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminacion'));
    modal.show();
  }
});

document.getElementById("btnConfirmarEliminar").addEventListener("click", function () {
  if (!empresaIdParaEliminar) return;

  fetch("/alibot/api/eliminar_empresa.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({ id_empresa: empresaIdParaEliminar })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      // Refrescar tabla o quitar fila
      location.reload(); // O puedes eliminar solo la fila desde JS
    } else {
      alert("Error al eliminar: " + data.error);
    }
  })
  .catch(err => {
    console.error("Error al eliminar:", err);
    alert("Ocurrió un error al intentar eliminar.");
  });
});
</script>



  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>





















