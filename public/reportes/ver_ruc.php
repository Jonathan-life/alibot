<?php
require_once "../../db/Database.php";

$db = new Database();
$pdo = $db->getConnection();

$ruc = $_GET["ruc"] ?? '';

if (empty($ruc)) {
    echo "<p style='color:red'>⚠️ No se proporcionó un RUC.</p>";
    exit;
}

// Buscar empresa por RUC
$stmt = $pdo->prepare("SELECT * FROM empresas WHERE ruc = ?");
$stmt->execute([$ruc]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {
    echo "<p style='color:red'>❌ No se encontró una empresa con el RUC $ruc.</p>";
    exit;
}

// Obtener ID de la empresa
$id_empresa = $empresa['id_empresa'] ?? null;

// Validar ID antes de continuar
if (empty($id_empresa)) {
    echo "<p style='color:red'>❌ Error: la empresa no tiene un ID válido.</p>";
    echo "<pre>Depuración empresa:\n";
    var_dump($empresa);
    echo "</pre>";
    exit;
}

// Buscar deudas asociadas a esa empresa
$stmt = $pdo->prepare("SELECT * FROM deudas WHERE id_empresa = ?");
$stmt->execute([$id_empresa]);
$deudas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Evitar error si no hay resultados
if (!$deudas) {
    $deudas = [];
}

// Depuración: mostrar id_empresa
echo "<div style='background:#eef;padding:10px;margin-bottom:10px;border:1px solid #99f'>
🧩 Depuración PHP:<br>
<b>ID Empresa:</b> {$id_empresa}<br>
<b>RUC:</b> {$ruc}<br>
<b>Razon Social:</b> {$empresa['razon_social']}<br>
<b>Cantidad de deudas:</b> " . count($deudas) . "
</div>";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Deudas - Calculadora SUNAT</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    input[readonly] {
      background-color: #f0f0f0 !important;
      cursor: not-allowed;
    }
  </style>
</head>
<body class="p-3">
<div class="container-fluid">
  <button type="button" id="agregarFila" class="btn btn-primary mb-2">+ Nueva Deuda</button>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle text-center" id="tablaDeudas">
      <thead class="table-primary">
      <tr>
        <th>#</th>
        <th>Periodo Tributario</th>
        <th>Formulario / PDT</th>
        <th>Número de Orden</th>
        <th>Tributo o Multa</th>
        <th>Tipo</th>
        <th>Fecha de Emisión</th>
        <th>Fecha de Notificación</th>
        <th>Fecha de Pagos</th>
        <th>Fecha de Cálculos</th>
        <th>Etapa Básica</th>
        <th>Importe Deuda Tributaria</th>
        <th>Interés Capitalizado</th>
        <th title="Calculado automáticamente">Interés Moratorio</th>
        <th>Pagos</th>
        <th title="Calculado automáticamente">Saldo Total</th>
        <th>Acciones</th>
      </tr>
      </thead>

      <tbody>
      <?php $n = 1; foreach ($deudas as $fila): ?>
      <tr data-id="<?= htmlspecialchars($fila['id']) ?>">
          <td><?= $n++ ?></td>
          <td><input class="form-control form-control-sm" name="periodo_tributario" value="<?= htmlspecialchars($fila['periodo_tributario'] ?? '') ?>"></td>
          <td><input class="form-control form-control-sm" name="formulario" value="<?= htmlspecialchars($fila['formulario'] ?? '') ?>"></td>
          <td><input class="form-control form-control-sm" name="numero_orden" value="<?= htmlspecialchars($fila['numero_orden'] ?? '') ?>"></td>
          <td><input class="form-control form-control-sm" name="tributo_multa" value="<?= htmlspecialchars($fila['tributo_multa'] ?? '') ?>"></td>
          <td><input class="form-control form-control-sm" name="tipo" value="<?= htmlspecialchars($fila['tipo'] ?? '') ?>"></td>
          <td><input class="form-control form-control-sm" type="date" name="fecha_emision" value="<?= htmlspecialchars($fila['fecha_emision'] ?? '') ?>"></td>
          <td><input class="form-control form-control-sm" type="date" name="fecha_notificacion" value="<?= htmlspecialchars($fila['fecha_notificacion'] ?? '') ?>"></td>
          <td><input class="form-control form-control-sm" type="date" name="fecha_pagos" value="<?= htmlspecialchars($fila['fecha_pagos'] ?? '') ?>"></td>
          <td><input class="form-control form-control-sm" type="date" name="fecha_calculos" value="<?= htmlspecialchars($fila['fecha_calculos'] ?? date('Y-m-d')) ?>"></td>
          <td><input class="form-control form-control-sm" name="etapa_basica" value="<?= htmlspecialchars($fila['etapa_basica'] ?? '') ?>"></td>
          <td><input class="form-control form-control-sm" type="number" step="0.01" name="importe_tributaria" value="<?= htmlspecialchars($fila['importe_tributaria'] ?? 0) ?>"></td>
          <td><input class="form-control form-control-sm" type="number" step="0.01" name="interes_capitalizado" value="<?= htmlspecialchars($fila['interes_capitalizado'] ?? 0) ?>"></td>
          <td><input class="form-control form-control-sm" type="number" step="1" name="interes_moratorio" value="<?= htmlspecialchars($fila['interes_moratorio'] ?? 0) ?>" readonly></td>
          <td><input class="form-control form-control-sm" type="number" step="0.01" name="pagos" value="<?= htmlspecialchars($fila['pagos'] ?? 0) ?>"></td>
          <td><input class="form-control form-control-sm" type="number" step="1" name="saldo_total" value="<?= htmlspecialchars($fila['saldo_total'] ?? 0) ?>" readonly></td>
          <td><button type="button" class="btn btn-success btn-sm guardar">💾</button></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <a href="admin_contable.php" class="btn btn-secondary mt-3">← Volver</a>
</div>

<script>
  // Variables globales definidas desde PHP
  const ruc = "<?= $empresa['ruc'] ?>";
  const id_empresa = "<?= $empresa['id_empresa'] ?>";
</script>

<script>
$(document).ready(function () {

function calcularDias(fechaInicio, fechaFin) {
  if (!fechaInicio) return 0;
  const inicio = new Date(fechaInicio);
  const fin = fechaFin ? new Date(fechaFin) : new Date();
  inicio.setHours(0, 0, 0, 0);
  fin.setHours(0, 0, 0, 0);
  return Math.max(0, Math.round((fin - inicio) / (1000 * 60 * 60 * 24)));
}

function calcularFila(fila) {
  let importe = parseFloat(fila.find('input[name="importe_tributaria"]').val()) || 0;
  let capitalizado = parseFloat(fila.find('input[name="interes_capitalizado"]').val()) || 0;
  let pagos = parseFloat(fila.find('input[name="pagos"]').val()) || 0;
  let tasaDiaria = 0.0003374;

  let fechaInicio = fila.find('input[name="fecha_emision"]').val();
  let fechaCalculos = fila.find('input[name="fecha_calculos"]').val() || new Date().toISOString().split('T')[0];
  let dias = calcularDias(fechaInicio, fechaCalculos);

  let interesCalculado = (importe + capitalizado) * tasaDiaria * dias;
  interesCalculado = Math.round(interesCalculado); // 🔹 redondeo sin decimales

  let saldoTotal = (importe + capitalizado + interesCalculado) - pagos;
  saldoTotal = Math.round(saldoTotal); // 🔹 redondeo sin decimales

  fila.find('input[name="interes_moratorio"]').val(interesCalculado);
  fila.find('input[name="saldo_total"]').val(saldoTotal);

  return { interesCalculado, saldoTotal };
}

$(document).on('input change', 'input:not([readonly])', function () {
  calcularFila($(this).closest('tr'));
});


$('#agregarFila').click(function () {
  let fechaHoy = new Date().toISOString().split('T')[0];
  let filaNueva = `<tr data-id="">
      <td>Nuevo</td>
      <td><input class="form-control form-control-sm" name="periodo_tributario"></td>
      <td><input class="form-control form-control-sm" name="formulario"></td>
      <td><input class="form-control form-control-sm" name="numero_orden"></td>
      <td><input class="form-control form-control-sm" name="tributo_multa"></td>
      <td><input class="form-control form-control-sm" name="tipo"></td>
      <td><input class="form-control form-control-sm" type="date" name="fecha_emision"></td>
      <td><input class="form-control form-control-sm" type="date" name="fecha_notificacion"></td>
      <td><input class="form-control form-control-sm" type="date" name="fecha_pagos"></td>
      <td><input class="form-control form-control-sm" type="date" name="fecha_calculos" value="${fechaHoy}"></td>
      <td><input class="form-control form-control-sm" name="etapa_basica"></td>
      <td><input class="form-control form-control-sm" type="number" step="0.01" name="importe_tributaria" value="0"></td>
      <td><input class="form-control form-control-sm" type="number" step="0.01" name="interes_capitalizado" value="0"></td>
      <td><input class="form-control form-control-sm" type="number" name="interes_moratorio" value="0" readonly></td>
      <td><input class="form-control form-control-sm" type="number" step="0.01" name="pagos" value="0"></td>
      <td><input class="form-control form-control-sm" type="number" name="saldo_total" value="0" readonly></td>
      <td><button type="button" class="btn btn-success btn-sm guardar">💾</button></td>
    </tr>`;
  $('#tablaDeudas tbody').append(filaNueva);
});

$(document).on('click', '.guardar', function () {
  const fila = $(this).closest('tr');
  const id = fila.data('id') || null;
  const resultado = calcularFila(fila);

  const data = {
    id,
    ruc,
    id_empresa,
    periodo_tributario: fila.find('[name="periodo_tributario"]').val(),
    formulario: fila.find('[name="formulario"]').val(),
    numero_orden: fila.find('[name="numero_orden"]').val(),
    tributo_multa: fila.find('[name="tributo_multa"]').val(),
    tipo: fila.find('[name="tipo"]').val(),
    fecha_emision: fila.find('[name="fecha_emision"]').val(),
    fecha_notificacion: fila.find('[name="fecha_notificacion"]').val(),
    fecha_pagos: fila.find('[name="fecha_pagos"]').val(),
    fecha_calculos: fila.find('[name="fecha_calculos"]').val(),
    etapa_basica: fila.find('[name="etapa_basica"]').val(),
    importe_tributaria: parseFloat(fila.find('[name="importe_tributaria"]').val()) || 0,
    interes_capitalizado: parseFloat(fila.find('[name="interes_capitalizado"]').val()) || 0,
    interes_moratorio: parseFloat(fila.find('[name="interes_moratorio"]').val()) || 0,
    pagos: parseFloat(fila.find('[name="pagos"]').val()) || 0,
    saldo_total: resultado.saldoTotal
  };

  console.log("📤 Enviando datos AJAX (JSON):", data);

  $.ajax({
    url: 'actualizar_deuda_ajax.php',
    type: 'POST',
    data: JSON.stringify(data),       // ✅ Enviar JSON puro
    contentType: 'application/json',  // ✅ Indicar que es JSON
    processData: false,               // ✅ No procesar
    dataType: 'json',                 // ✅ Esperar JSON de respuesta
    success: function (res) {
      console.log("📥 Respuesta servidor:", res);
      if (res.status === 'ok') {
        Swal.fire({ icon: 'success', title: 'Guardado', text: 'Registro actualizado', timer: 1200, showConfirmButton: false });
        if (!id && res.nuevo_id) fila.attr('data-id', res.nuevo_id);
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
      }
    },
    error: function (xhr) {
      console.error("❌ Error AJAX:", xhr.responseText);
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión con el servidor.' });
    }
  });
});


});
</script>
</body>
</html>
