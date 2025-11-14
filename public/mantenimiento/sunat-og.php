<?php
require_once __DIR__ . '/../../Controllers/BotController.php';
$controller = new BotController();

$response = null;

// Si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_empresa   = intval($_POST["id_empresa"]);
    $fecha_inicio = $_POST["fecha_inicio"];
    $fecha_fin    = $_POST["fecha_fin"];
    $tipo_descarga = $_POST["tipo_descarga"];

    $response = $controller->ejecutarBot($id_empresa, $fecha_inicio, $fecha_fin, $tipo_descarga);
}

// Obtener empresas
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

  <!-- SWEETALERT -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body {
      background-color: #f0f2f5;
    }
    .container {
      max-width: 600px;
      margin-top: 60px;
    }
    .card {
      border-radius: 14px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
    .btn-primary {
      background: linear-gradient(135deg, #007bff, #0056b3);
      border: none;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #0056b3, #003f82);
    }
  </style>
</head>

<body>

<div class="container">
  <div class="card p-4">
    <h3 class="text-center mb-4">📂 Descargar documentos SUNAT</h3>

    <form method="POST" id="form-descarga">
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

<script>
// ⚠ Mostrar alerta mientras descarga
document.getElementById("form-descarga").addEventListener("submit", function() {
    Swal.fire({
        title: "Descargando...",
        html: "Por favor espera, esto puede tardar unos minutos.",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
});
</script>

<?php if ($response !== null): ?>
<script>
document.addEventListener("DOMContentLoaded", () => {

    // 🔥 Evita reenviar el formulario al recargar
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    <?php if ($response["status"] === "success"): ?>

        Swal.fire({
            title: "✔ Descarga completada",
            text: "Los documentos se descargaron correctamente.",
            icon: "success",
            confirmButtonText: "Aceptar"
        });

    <?php else: ?>

        Swal.fire({
            title: "❌ Error al descargar",
            html: `<?= htmlspecialchars($response["mensaje"]) ?>`,
            icon: "error",
            confirmButtonText: "Cerrar"
        });

    <?php endif; ?>
});
</script>
<?php endif; ?>

</body>
</html>
