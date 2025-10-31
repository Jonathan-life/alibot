<?php
require_once __DIR__ . '/../../Controllers/BotController.php';
$controller = new BotController();

// Si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_empresa = intval($_POST["id_empresa"]);
    $fecha_inicio = $_POST["fecha_inicio"];
    $fecha_fin = $_POST["fecha_fin"];
    $tipo_descarga = $_POST["tipo_descarga"];

    $resultado = $controller->ejecutarBot($id_empresa, $fecha_inicio, $fecha_fin, $tipo_descarga);

    if ($resultado["status"] === "success") {
        echo '<div class="alert alert-success mt-3 text-center">'
            . $resultado["mensaje"] .
            ' Revisa el log en <strong>' . htmlspecialchars($resultado["log"]) . '</strong>.</div>';
    } else {
        echo '<div class="alert alert-danger mt-3">'
            . $resultado["mensaje"] .
            '<br><pre class="bg-light p-3 rounded border">' . htmlspecialchars(implode("\n", $resultado["salida"])) . '</pre>' .
            'Revisa el log en <strong>' . htmlspecialchars($resultado["log"]) . '</strong>.</div>';
    }
    exit;
}

// Si no hay POST, mostrar el formulario
$mysqli = new mysqli("localhost", "root", "", "sistema_contable");
$result = $mysqli->query("SELECT id_empresa, razon_social FROM empresas WHERE estado='ACTIVO'");
$empresas = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Descargar documentos SUNAT</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .container {
      max-width: 600px;
      margin-top: 60px;
    }
    .card {
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .btn-primary {
      background: linear-gradient(135deg, #007bff, #0056b3);
      border: none;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #0056b3, #00408a);
    }
  </style>
</head>
<body>

<div class="container">
  <div class="card p-4">
    <h3 class="text-center mb-4">📂 Descargar documentos SUNAT</h3>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Empresa</label>
        <select name="id_empresa" class="form-select" required>
          <option value="">Seleccione una empresa</option>
          <?php foreach ($empresas as $e): ?>
              <option value="<?= $e['id_empresa'] ?>"><?= htmlspecialchars($e['razon_social']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Tipo de descarga</label>
        <select name="tipo_descarga" class="form-select" required>
          <option value="recibidas">Recibidas (Compras)</option>
          <option value="emitidas">Emitidas (Ventas)</option>
        </select>
      </div>

      <div class="row mb-3">
        <div class="col">
          <label class="form-label">Fecha inicio</label>
          <input type="date" name="fecha_inicio" class="form-control" required>
        </div>
        <div class="col">
          <label class="form-label">Fecha fin</label>
          <input type="date" name="fecha_fin" class="form-control" required>
        </div>
      </div>

      <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">⬇ Descargar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
