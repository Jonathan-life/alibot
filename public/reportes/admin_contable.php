<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Deudas - Panel Principal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body {
      background-color: #f8f9fa;
    }
    .card {
      transition: transform .2s, box-shadow .2s;
    }
    .card:hover {
      transform: scale(1.03);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
  </style>
</head>
<body>
<div class="container py-5">
  <h1 class="text-center mb-4">💼 Panel de Gestión de Deudas</h1>
  
  <div class="row g-4">
    <!-- Crear nueva deuda -->
    <div class="col-md-4">
      <div class="card text-center border-success">
        <div class="card-body">
          <h5 class="card-title text-success">➕ Crear Nueva Deuda</h5>
          <p class="card-text">Registra una nueva deuda tributaria con todos sus detalles.</p>
          <a href="buscar_ruc.php" class="btn btn-success">Ir a Crear</a>
        </div>
      </div>
    </div>

    <!-- Buscar/editar deuda -->
    <div class="col-md-4">
      <div class="card text-center border-primary">
        <div class="card-body">
          <h5 class="card-title text-primary">🔍 Buscar +</h5>
          <p class="card-text">Busca deudas por RUC, periodo o formulario, y edita sus datos.</p>
          <a href="reportes.php" class="btn btn-primary">Ir a Buscar</a>
        </div>
      </div>
    </div>

  </div>

  <footer class="text-center mt-5 text-muted">
    <small>© <?php echo date('Y'); ?> Sistema de Gestión de Deudas Tributarias</small>
  </footer>
</div>
</body>
</html>
