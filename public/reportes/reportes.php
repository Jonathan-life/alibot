<?php
require_once "../../db/Database.php";
$db = new Database();
$pdo = $db->getConnection();

// Leer filtro por RUC
$ruc = $_GET['ruc'] ?? '';

// Construir consulta con o sin filtro
if (!empty($ruc)) {
    $stmt = $pdo->prepare("SELECT * FROM deudas WHERE ruc LIKE :ruc");
    $stmt->execute(['ruc' => "%$ruc%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM deudas");
}

$deudas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Generar Reporte</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="p-4 bg-light">
<div class="container">
  <h2 class="mb-4 text-center text-primary">📊 Generar Reporte de Deudas</h2>

  <!-- 🔍 Filtro por RUC -->
  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label fw-bold">Buscar por RUC:</label>
      <input type="text" name="ruc" value="<?= htmlspecialchars($ruc) ?>" class="form-control" placeholder="Ej. 20494384273">
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">🔍 Buscar</button>
    </div>
    <?php if ($ruc): ?>
      <div class="col-md-2 d-flex align-items-end">
        <a href="reportes.php" class="btn btn-secondary w-100">Limpiar filtro</a>
      </div>
    <?php endif; ?>
  </form>

  <!-- 🧾 Formulario para generar PDF/Excel -->
  <form id="reporteForm" method="POST" action="generar_reporte.php" class="border p-3 bg-white rounded shadow-sm">
    <fieldset>
      <legend class="fw-bold text-success">Selecciona los campos</legend>
      <div class="row">
        <?php
        $campos = [
          "id","ruc","periodo_tributario","formulario","numero_orden","tributo_multa",
          "tipo","fecha_emision","fecha_notificacion","fecha_pagos","fecha_calculos",
          "etapa_basica","importe_deudas","importe_tributaria","interes_capitalizado",
          "interes_moratorio","pagos","saldo_total","interes_diario","interes_acumulado","id_empresa"
        ];
        foreach ($campos as $campo) {
            echo "<div class='col-md-3'><div class='form-check'>
                    <input class='form-check-input' type='checkbox' name='campos[]' value='$campo' checked>
                    <label class='form-check-label'>$campo</label>
                  </div></div>";
        }
        ?>
      </div>
      <input type="hidden" name="ruc" value="<?= htmlspecialchars($ruc) ?>">
    </fieldset>

    <div class="mt-3 text-center">
      <button type="submit" name="tipo" value="pdf" class="btn btn-danger me-2">📄 Generar PDF</button>
      <button type="submit" name="tipo" value="excel" class="btn btn-success">📊 Generar Excel</button>
    </div>
  </form>

  <!-- 👁️ Vista previa -->
  <div class="mt-5">
    <h4 class="mb-3">Vista previa <?= $ruc ? "(filtrado por RUC <b>$ruc</b>)" : "" ?></h4>

    <?php if (count($deudas) > 0): ?>
      <div class="table-responsive shadow-sm">
        <table class="table table-striped table-bordered align-middle">
          <thead class="table-primary">
            <tr>
              <?php foreach (array_keys($deudas[0]) as $col): ?>
                <th><?= htmlspecialchars($col) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($deudas as $row): ?>
              <tr>
                <?php foreach ($row as $val): ?>
                  <td><?= htmlspecialchars($val) ?></td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-warning mt-3">
        <em>No se encontraron resultados.</em>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
