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

  <style>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: white;
    color: #222;
  }


  /* Sección con fondo */
  .header-section {
    background: url('img/fondytoali.png') no-repeat center center;
    background-size: cover;
    color: white;
    padding-bottom: 60px;
  }

  /* Navbar */
  .navbar-custom {
    background: transparent !important; 
    padding-top: 25px;   /* baja más el menú */
    padding-bottom: 15px;
  }




  /* Botón Agregar nuevo */
  .header-section .btn {
    font-weight: bold;
    background: rgba(255,255,255,0.9);
    color: #333;
    border: none;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    transition: 0.3s ease;
  }
  .header-section .btn:hover {
    background: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
  }

  /* Caja de búsqueda */
  .search-box-wrapper {
    background: #ffffffff;
    padding: 15px 0;
  }
  .navbar-brand{
    padding-left: 49px;
  }
  /* Contenedor de iconos */
.iconos-navbar {
  display: flex;
  align-items: center;
  margin-right: 40px;
  gap: 20px; /* espacio entre íconos */
  font-size: 25px; /* tamaño de íconos */
}

/* Estilo de los enlaces de íconos */
.iconos-navbar .icon-link {
  color: #8A8C8F;            /* color normal */
  text-decoration: none;   /* sin subrayado */
  transition: 0.3s ease;
}

/* Hover en íconos */
.iconos-navbar .icon-link:hover {
  color: #3966EC;          /* dorado al pasar */
  transform: scale(1.2);   /* efecto zoom */
}





/* Contenedor principal */
.empresas-header {
  max-width: 1300px;
  margin-top: 50px;
}

/* Flexbox para título y botón */
.empresas-flex {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Título */
.empresas-title {
  font-weight: 700;
  font-size: 1.9rem;
  color: #ffffffff;
}

/* Fecha y hora */
.empresas-fecha {
  color: #ffffffff;
  font-size: 0.9rem;
  margin-top: 5px;
  
}

/* Botón personalizado */
.btn-agregar {
  font-weight: 600;
  background: #ffffff;
  color: #ffffffff;
  border: 2px solid #ddd;
  border-radius: 50px;
  padding: 8px 24px;
  transition: all 0.3s ease;

  cursor: pointer;
}

.btn-agregar:hover {
  background: #f8f9fa;
  color: #ffffffff;
  border-color: #bbb;
}

/* Responsive */
@media (max-width: 768px) {
  .empresas-flex {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .btn-agregar {
    align-self: stretch;
    text-align: center;
  }
}

/* Navbar */
.navbar-custom {
  background: transparent !important; 
  padding-top: 25px;
  padding-bottom: 15px;
}

/* Menú centrado en la navbar con fondo azul */
.navbar-nav {
  display: flex !important;
  flex-direction: row !important;
  justify-content: center !important;
  align-items: center;
  gap: 35px;
  margin-right: 200px;
  padding: 5px 50px;
  width: auto;
  
  font-size: 18px;
  background: #3966EC;   /* azul */
  border-radius: 10px;
}

/* Links */
.navbar-custom .nav-link,
.navbar-custom .navbar-brand {
  color: white !important;
  font-weight: bold;
}

.navbar-custom .nav-link:hover {
  color: #e5e5e5 !important;
}

/* 🔹 Dropdown PRIMER NIVEL (ej: Reportes → SUNAT, SUNAFIL en columna vertical) */
.dropdown > .dropdown-menu {
  display: flex;
  flex-direction: column;   /* 👈 vertical */
  justify-content: flex-start;
  padding: 8px 0;
  border-radius: 8px;
  border: none;
  background: #fff;
  opacity: 0;
  margin-top: 3px;
  transform: translateY(10px);
  transition: all 0.3s ease;
  pointer-events: none;
  position: absolute;
  left: 60%;
  transform: translateX(-50%) translateY(10px);
  min-width: 220px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.dropdown:hover > .dropdown-menu {
  opacity: 1;
  transform: translateX(-50%) translateY(0);
  pointer-events: auto;
}

/* Estilos de ítems */
.dropdown-menu .dropdown-item {
  color: #000;
  font-weight: 500;
  white-space: nowrap;
  padding: 8px 15px;
  transition: all 0.2s ease; 
}

.dropdown-menu .dropdown-item:hover {
  background: #3966EC;
  color: #FFFFFF;
  border-radius: 4px;
}

/* 🔹 SUBMENÚS internos (ej: SUNAT → Buzón, Casilla a la derecha) */
.dropdown-submenu {
  position: relative;
}

.dropdown-submenu > .dropdown-menu {
  display: flex;
  flex-direction: column;   /* vertical */
  position: absolute;
  top: 0;
  left: 100%;              /* 👈 aparece al lado derecho */
  min-width: 200px;
  padding: 8px 0;
  border-radius: 6px;
  background: #fff;
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
  opacity: 0;
  transform: translateX(10px);
  transition: all 0.3s ease;
  pointer-events: none;
}

.dropdown-submenu:hover > .dropdown-menu {
  opacity: 1;
  transform: translateX(0);
  pointer-events: auto;
}

/* Botón Agregar nuevo */
.header-section .btn {
  font-weight: bold;
  background: rgba(255,255,255,0.9);
  color: #333;
  border: none;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  transition: 0.3s ease;
}
.header-section .btn:hover {
  background: white;
  box-shadow: 0 4px 12px rgba(0,0,0,0.25);
}

/* Caja de búsqueda */
.search-box-wrapper {
  background: #fff;
  padding: 15px 0;
}

.navbar-brand {
  padding-left: 49px;
}


/* Contenedor general */
.contenedorbtn {
  display: flex;
  justify-content: center;   /* Centrado horizontal */
  align-items: center;       /* Centrado vertical */
  border-radius: 8px;        /* Bordes opcionales */
  box-sizing: border-box;
}

/* Botón estilo enlace */
.btn-agregar {
  display: inline-flex;
  align-items: center;
  gap: .8rem;
  margin-right: 30px;
  padding: .5rem 1rem;
  border: 1px solid #ffffffff;
  background: #3966EC;
  color: #ffffffff;
  margin-top: 25px;
  font: 600 17px/1.1 system-ui, sans-serif;
  border-radius: 15px;
  cursor: pointer;
  text-decoration: none; /* 🔹 Aquí quitas el subrayado */
  transition: transform .08s ease, box-shadow .15s ease, background .15s ease;
}


.btn-agregar:hover {
  background: #3458c4ff;
  box-shadow: 0 4px 14px rgba(255, 255, 255, 1);
}

.btn-agregar:active {
  transform: translateY(1px);
}

.icono {
  width: 20px;
  height: 20px;
  object-fit: contain;
}








.container-table {
  max-width: 1240px;
  margin: 10px auto;
  border-radius: 12px;
}

.tabla {
  width: 100%;
  border-collapse: collapse;
  border-radius: 10px;
  overflow: hidden;
}

.tabla thead {
  background: #000000ff;
}

.tabla th {
  text-align: left;
  padding: 12px 15px;
  font-size: 14px;
  font-weight: 600;
  color: #ffffffff;
}

.tabla td {
  padding: 12px 15px;
  font-size: 14px;
  border-top: 1px solid #eee;
  color: #444;
}

/* Badge de estado */
.badge-activo {
  background: #000;
  color: #fff;
  font-weight: 600;
  font-size: 12px;
  padding: 4px 10px;
  border-radius: 6px;
  display: inline-block;
}

/* Acciones */
.acciones {
  display: flex;
  gap: 8px;
  position: relative; /* 👈 ahora el menú se posiciona respecto a este div */
}

.acciones button {
  border: none;
  background: none;
  cursor: pointer;
  font-size: 16px;
}

.menu-opciones {
  display: none;
  position: absolute;
  right: 0;
  bottom: 70%;
  left: auto;        /* mejor que 50px, evita cortar el menú */
  background: #fff;
  border: 1px solid #ccc;
  border-radius: 8px;
  min-width: 150px;
  box-shadow: 0px 4px 8px rgba(0,0,0,0.25);
  max-height: 300px; /* permite scroll si hay muchas opciones */
  overflow-y: auto;  /* activa scroll vertical */
  z-index: 9999;
}

.menu-opciones a {
  display: block;
  padding: 8px 12px;
  text-decoration: none;
  font-size: 14px;
  color: #333;
}

.menu-opciones a:hover {
  background: #f5f5f5;
}


/* Contenedor dl menú */
.navbar-menu-container {
  flex-grow: 1;                     /* Ocupa el espacio disponible entre logo e iconos */
  display: flex;
  justify-content: center;         /* Centra el menú horizontalmente */
  align-items: center;
  margin-right: 25px;
}

.navbar-icons-container .icon-link i {
  font-size: 1.8rem;  /* Puedes ajustar el valor según lo grande que los quieras */
}

</style>
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
          <!-- Submenú Permisos -->
          <li class="dropdown-submenu">
            <a class="dropdown-item" href="#">Permisos</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="permisos/usuario.php">Usuarios</a></li>
              <li><a class="dropdown-item" href="permisos/empresa.php">Empresas</a></li>
            </ul>
          </li>
          <li><a class="dropdown-item" href="mantenimiento/notificacion.php">Notificación</a></li>
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
    <li><a class="dropdown-item" href="#">Casilla Electrónica</a></li>

    <!-- Submenú Compras -->
    <li class="dropdown-submenu">
      <a class="dropdown-item" href="#">Compras SIRE ▸</a>
      <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#">PDF</a></li>
          <li><a class="dropdown-item" href="#">XML</a></li>
      </ul>
    </li>

    <!-- Submenú Ventas -->
      <li class="dropdown-submenu">
        <a class="dropdown-item" href="">Ventas SIRE ▸</a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="reportes/venta_sire.php">PDF</a></li>
          <li><a class="dropdown-item" href="#">XML</a></li>
        </ul>
      </li>

      <li><a class="dropdown-item" href="#">Detracciones</a></li>
      <li><a class="dropdown-item" href="#">Recibos por honorario</a></li>
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

<script>
const tbody = document.querySelector(".tabla tbody");

fetch("/alibot-api/api/listar_empresas.php")
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
              <div class="acciones">
                <button class="btn-accionar btn-borrar" title="Eliminar">
                  <img src="img/basurero.png" alt="Eliminar" class="icono-btn">
                </button>
                <button class="btn-accionar btn-menu" title="Más opciones">
                  <img src="img/opcciones.png" alt="Opciones" class="icono-btn">
                </button>
                <div class="menu-opciones">
                  <a href="#">Editar</a>
                  <a href="#">Ver detalles</a>
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
  document.querySelectorAll(".btn-menu").forEach(btn => {
    btn.addEventListener("click", function (e) {
      e.stopPropagation();

      const acciones = this.closest(".acciones"); // 👈 ahora busca .acciones
      const menu = acciones.querySelector(".menu-opciones");

      // Cerrar otros menús
      document.querySelectorAll(".menu-opciones").forEach(m => {
        if (m !== menu) m.style.display = "none";
      });

      // Toggle
      menu.style.display = (menu.style.display === "block") ? "none" : "block";
    });
  });

  // Cerrar si clic fuera
  window.addEventListener("click", () => {
    document.querySelectorAll(".menu-opciones").forEach(menu => {
      menu.style.display = "none";
    });
  });
});
</script>



  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>