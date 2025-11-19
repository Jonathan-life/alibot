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
</head>
<body class="p-4 bg-light">
<div class="container">
  <h2 class="mb-4 text-center text-primary">📊 Generar Reporte de Deudas</h2>

  <!-- 🔍 Filtro por RUC -->
  <form id="buscarForm" method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label fw-bold">Buscar por RUC:</label>
      <input id="inputRuc" type="text" name="ruc" value="<?= htmlspecialchars($ruc) ?>" maxlength="11" class="form-control" placeholder="Ej. 20494384273">
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">🔍 Buscar</button>
    </div>
    <?php if ($ruc): ?>
      <div class="col-md-2 d-flex align-items-end">
        <a href="reportes.php" class="btn btn-secondary w-100">Limpiar</a>
      </div>
    <?php endif; ?>
  </form>

  <!-- Mostrar botones solo si se encontró RUC -->
  <?php if ($ruc && count($deudas) > 0): ?>
  <div id="resultadoBox" class="text-center border p-4 bg-white rounded shadow-sm">
      <h5 class="text-success fw-bold mb-3">✅ RUC encontrado: <?= htmlspecialchars($ruc) ?></h5>

      <form method="POST" action="generar_reporte.php">
          <input type="hidden" name="ruc" value="<?= htmlspecialchars($ruc) ?>">

          <button type="submit" name="tipo" value="pdf" class="btn btn-danger me-2">📄 Generar PDF</button>
          <button type="submit" name="tipo" value="excel" class="btn btn-success">📊 Generar Excel</button>
      </form>
  </div>

  <!-- 📋 TABLA DE RESULTADOS -->
  <div class="mt-4">
      <table class="table table-bordered table-striped bg-white shadow-sm">
          <thead class="table-primary">
              <tr>
                  <th>RUC</th>
                  <th>Periodo Tributario</th>
                  <th>Formulario</th>
                  <th>N° Orden</th>
                  <th>Tributo / Multa</th>
                  <th>Tipo</th>
                  <th>Fecha Emisión</th>
                  <th>Fecha Notificación</th>
                  <th>Fecha Pagos</th>
                  <th>Fecha Cálculos</th>
                  <th>Etapa Básica</th>
                  <th>Importe Deuda</th>
                  <th>Importe Tributario</th>
                  <th>Interés Capitalizado</th>
                  <th>Interés Moratorio</th>
                  <th>Pagos</th>
                  <th><b>Saldo Total</b></th>
              </tr>
          </thead>

          <tbody>
          <?php foreach ($deudas as $fila): ?>
              <tr>
                  <td><?= $fila['ruc'] ?></td>
                  <td><?= $fila['periodo_tributario'] ?></td>
                  <td><?= $fila['formulario'] ?></td>
                  <td><?= $fila['numero_orden'] ?></td>
                  <td><?= $fila['tributo_multa'] ?></td>
                  <td><?= $fila['tipo'] ?></td>
                  <td><?= $fila['fecha_emision'] ?></td>
                  <td><?= $fila['fecha_notificacion'] ?></td>
                  <td><?= $fila['fecha_pagos'] ?></td>
                  <td><?= $fila['fecha_calculos'] ?></td>
                  <td><?= $fila['etapa_basica'] ?></td>
                  <td><?= number_format($fila['importe_deudas'], 2) ?></td>
                  <td><?= number_format($fila['importe_tributaria'], 2) ?></td>
                  <td><?= number_format($fila['interes_capitalizado'], 2) ?></td>
                  <td><?= number_format($fila['interes_moratorio'], 2) ?></td>
                  <td><?= number_format($fila['pagos'], 2) ?></td>
                  <td><strong><?= number_format($fila['saldo_total'], 2) ?></strong></td>
              </tr>
          <?php endforeach; ?>
          </tbody>
      </table>
  </div>

  <?php elseif ($ruc && count($deudas) == 0): ?>
    <div class="alert alert-warning text-center">⚠ No se encontraron resultados para este RUC.</div>
  <?php endif; ?>

</div>

<script>
document.getElementById("inputRuc").addEventListener("input", function(){
    if(this.value.length === 11){
        document.getElementById("buscarForm").submit();
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
