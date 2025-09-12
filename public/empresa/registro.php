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
    padding-bottom: 50px;
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








/* Contenedor del menú */
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




/* === BARRA LATERAL (Pasos) === */
/* === CONTENEDOR DEL SIDEBAR === */
.sidebar-container {
  display: flex;            /* por si quieres agregar más dentro */
  justify-content: flex-start;
  margin-left: 20px;
}

.sidebar {
  width: 600px;
  padding: 60px;
  border-radius: 50px;
  background: #f1f5f9;
}

.sidebar-title {
  font-size: 20px;
  font-weight: bold;
  margin-bottom: 20px;
  color: #333;
}

.sidebar ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar li {
  display: flex;
  align-items: center;
  margin-bottom: 54px;
  font-size: 18px;
  color: #64748b;
}

.sidebar li.active {
  color: #2563eb;
  font-weight: bold;
}

.sidebar li::before {
  content: "";
  display: inline-block;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #e2e8f0;
  margin-right: 22px;
  text-align: center;
  line-height: 28px;
}

.sidebar li.active::before {
  background: #2563eb;
  color: #fff;
}



/* === SIDEBAR === */




.main-container {
  display: grid;
  grid-template-columns: 400px 1fr; /* Sidebar fijo + contenido flexible */
  gap: 40px;                        /* espacio entre ambos */
  align-items: flex-start;           /* que inicien arriba */
  padding: 40px;
}

/* === CONTENEDOR DEL CONTENIDO === */
.content-container {
  background: #ffffff;
  border-radius: 16px;
  padding: 40px;
  display: flex;
  flex-direction: column;
}

/* === TÍTULO === */
.content-container h2,
.content-container h3 {
  color: #2563eb;             /* azul */
  font-weight: 700;           /* negrita */
  letter-spacing: 0.5px;      /* un poco más de separación */
  margin-bottom: 20px;
  text-shadow: 0 1px 2px rgba(37, 99, 235, 0.25); /* relieve sutil */
}

/* === FORMULARIO === */
.form-group {
  display: flex;
  gap: 20px;
  width: 100%;
  margin-bottom: 24px;
}

.form-group input {
  flex: 1;
  padding: 18px 22px 15px;
  border: 2px solid #93c5fd;   /* azul claro en reposo */
  border-radius: 10px;
  font-size: 15px;
  color: #1e293b;
  outline: none;
  transition: border 0.25s, box-shadow 0.25s, transform 0.15s;
}

/* Efecto al enfocar */
.form-group input:focus {
  border-color: #2563eb;  /* azul fuerte */
  box-shadow: 0 0 8px rgba(37, 99, 235, 0.35);
  transform: scale(1.01); /* leve realce */
}


/* === SERVICIOS (switches) === */
.services {
  margin: 30px 0;
}

.services label {
  display: flex;
  align-items: center;
  margin-bottom: 14px;
  font-size: 15px;
  font-weight: 500;
  color: #334155;
  cursor: pointer;
}

.services input[type="checkbox"] {
  appearance: none;
  width: 42px;
  height: 22px;
  background: #e2e8f0;
  border-radius: 22px;
  position: relative;
  margin-right: 12px;
  transition: background 0.3s;
}

.services input[type="checkbox"]::before {
  content: "";
  position: absolute;
  top: 3px;
  left: 3px;
  width: 16px;
  height: 16px;
  background: #ffffff;
  border-radius: 50%;
  transition: transform 0.3s;
}

.services input[type="checkbox"]:checked {
  background: #2563eb;
}

.services input[type="checkbox"]:checked::before {
  transform: translateX(20px);
}

/* === BOTONES === */
.actions {
  display: flex;
  gap: 20px;
  margin-top: 30px;
}

button {
  flex: 1;
  padding: 14px;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.3s;
}

button.primary {
  background: #2563eb;
  color: #fff;
}

button.primary:hover {
  background: #1d4ed8;
}

button.secondary {
  background: #64748b;
  color: #fff;
}

button.secondary:hover {
  background: #475569;
}

/* === ILUSTRACIÓN DEL ROBOT === */
.illustration {
  text-align: center;
  margin-top: 20px;
}

.illustration img {
  max-width: 180px;
}

.illustration .bubble {
  background: #e0f2fe;
  color: #0369a1;
  padding: 12px 16px;
  border-radius: 12px;
  font-size: 13px;
  margin-top: 10px;
  display: inline-block;
  max-width: 220px;
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4 py-3">

  <!-- Contenedor del LOGO -->
  <div class="navbar-logo-container">
    <a class="navbar-brand fw-bold ms-4" href="#">
      <img src="../img/logcounting.png" alt="Logo" style="height:60px;">
    </a>
  </div>

  <!-- Botón hamburguesa para responsive -->
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
    <span class="navbar-toggler-icon"></span>
  </button>

  <!-- Contenedor del MENÚ -->
  <div class="navbar-menu-container">
    <ul class="navbar-nav" id="menuNav">
 <a class="nav-link active" href="../../public/index.php">Inicio</a>


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
        <a class="dropdown-item" href="#">Ventas SIRE ▸</a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#">PDF</a></li>
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




<div class="main-container">
  
  <!-- Sidebar -->
  <div class="container sidebar-container">
    <div class="sidebar">
      <h3 class="sidebar-title">Registrar empresa</h3>
      <ul>
        <li class="active">N° de RUC</li>
        <li>Razón Social</li>
        <li>Usuario Sol</li>
        <li>Clave Sol</li>
        <li>ID API Sunat</li>
        <li>Clave API Sunat</li>
      </ul>
    </div>
  </div>

<!-- Contenido del Formulario -->
<div class="container content-container">
  <div class="content">
    <h2>Completa tus datos</h2>
    <form id="empresaForm">
      
    <div class="form-group">
    <input type="text" id="ruc" name="ruc" placeholder="Ingresar RUC" data-step="1">
    <input type="text" id="razonSocial" name="razonSocial" placeholder="Razón Social" data-step="2" readonly>
  </div>

  <div class="form-group">
    <input type="text" name="usuarioSol" placeholder="Usuario Sol*" data-step="3">
    <input type="password" name="claveSol" placeholder="Clave Sol*" data-step="4">
  </div>

  <div class="form-group">
    <input type="text" name="apiClientId" placeholder="ID API Sunat" data-step="5">
    <input type="password" name="apiClientSecret" placeholder="Clave API Sunat" data-step="6">
  </div>


      <!-- Ilustración -->
      <div class="illustration">
        <img src="../img/robot.png" alt="Robot asistente">
        <div class="bubble">
          Las credenciales serán validadas y protegidas antes de permitir el acceso. 
          Recibirás un correo de confirmación una vez aprobadas.
        </div>
      </div>

      <!-- Botones -->
      <div class="actions">
        <button type="submit" class="primary">Registrar empresa</button>
        <button type="button" class="secondary">Regresar</button>
      </div>

    </form>
  </div>
</div>

<script>


const API_BASE = '/alibot-api/api';

document.getElementById("ruc").addEventListener("blur", function() {
  let ruc = this.value.trim();
  if (ruc.length === 11) {
    fetch(`${API_BASE}/consultaRuc.php?ruc=${ruc}`)
    .then(res => {
      if (!res.ok) throw new Error("Error al consultar el RUC");
      return res.json();
    })
    .then(data => {
      // Según lo que te devuelve el “full”, puede tener más campos
      if (data.razonSocial || data.razon_social || data.nombre) {
        document.getElementById("razonSocial").value = data.razonSocial 
          ?? data.razon_social 
          ?? data.nombre;
      } else {
        document.getElementById("razonSocial").value = "No encontrado";
      }
    })
    .catch(err => {
      console.error(err);
      document.getElementById("razonSocial").value = "Error en consulta";
    });
  }
});



document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("empresaForm");
  const steps = document.querySelectorAll(".sidebar li");

  form.querySelectorAll("input").forEach(input => {
    input.addEventListener("input", () => {
      const stepIndex = parseInt(input.dataset.step, 10) - 1;

      if (input.value.trim() !== "") {
        steps[stepIndex].classList.add("active");
      } else {
        steps[stepIndex].classList.remove("active");
      }
    });
  });
});
</script>
<script>
document.getElementById("empresaForm").addEventListener("submit", function(e) {
  e.preventDefault(); // Evitar recargar la página

  let formData = {
    ruc: document.getElementById("ruc").value,
    razonSocial: document.getElementById("razonSocial").value,
    usuarioSol: this.usuarioSol.value,
    claveSol: this.claveSol.value,
    apiClientId: this.apiClientId.value,
    apiClientSecret: this.apiClientSecret.value
  };

  fetch("/alibot-api/api/empresa.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(formData)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert("✅ Empresa registrada correctamente");
      this.reset();
    } else {
      alert("⚠️ Error: " + data.error);
    }
  })
  .catch(err => {
    console.error(err);
    alert("❌ Error al conectar con el servidor");
  });
});
</script>


  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>