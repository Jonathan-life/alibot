<?php
require_once "../../db/Database.php";

$db = new Database();
$pdo = $db->getConnection();

$ruc = $_GET["ruc"] ?? '';

if (empty($ruc)) {
    echo "<p style='color:red'>⚠️ No se proporcionó un RUC.</p>";
    exit;
}

// Buscar empresa
$stmt = $pdo->prepare("SELECT * FROM empresas WHERE ruc = ?");
$stmt->execute([$ruc]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {
    echo "<p style='color:red'>❌ No se encontró una empresa con el RUC $ruc.</p>";
    exit;
}

$id_empresa = $empresa['id_empresa'] ?? null;

if (empty($id_empresa)) {
    echo "<p style='color:red'>❌ Error: la empresa no tiene un ID válido.</p>";
    echo "<pre>Depuración empresa:\n";
    var_dump($empresa);
    echo "</pre>";
    exit;
}

// Buscar deudas
$stmt = $pdo->prepare("SELECT * FROM deudas WHERE id_empresa = ?");
$stmt->execute([$id_empresa]);
$deudas = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$deudas) $deudas = [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Deudas - Calculadora SUNAT</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Iconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="../style/index.css">
  <style>
    input[readonly] { background-color: #f0f0f0 !important; cursor: not-allowed; }
  </style>
</head>

<body class="p-3">
<div class="container-fluid">

  <button type="button" id="agregarFila" class="btn btn-primary mb-2">+ Nueva Deuda</button>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle text-center" id="tablaDeudas">
      <thead class="table-primary">
      <tr>
        <th><input type="checkbox" id="chkTodos"></th>
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
        <th>Importe</th>
        <th>Interés Cap.</th>
        <th>Interés Mor.</th>
        <th>Pagos</th>
        <th>Saldo Total</th>
        <th>Acción</th>
      </tr>
      </thead>

  <tbody>
      <?php $n = 1; foreach ($deudas as $fila): ?>
      <tr data-id="<?= htmlspecialchars($fila['id']) ?>">
        <td><input type="checkbox" class="chkDeuda"></td>
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
        <td><input class="form-control form-control-sm" type="number" name="importe_tributaria" value="<?= number_format($fila['importe_tributaria'] ?? 0, 2, '.', '') ?>"></td>
        <td><input class="form-control form-control-sm" type="number" name="interes_capitalizado" value="<?= number_format($fila['interes_capitalizado'] ?? 0, 2, '.', '') ?>"></td>
        <td><input class="form-control form-control-sm" type="number" name="interes_moratorio" value="<?= number_format($fila['interes_moratorio'] ?? 0, 2, '.', '') ?>" readonly></td>
        <td><input class="form-control form-control-sm" type="number" name="pagos" value="<?= number_format($fila['pagos'] ?? 0, 2, '.', '') ?>" readonly></td>
        <td><input class="form-control form-control-sm saldo" type="number" name="saldo_total" value="<?= number_format($fila['saldo_total'] ?? 0, 2, '.', '') ?>" readonly></td>
        <td><button type="button" class="btn btn-success btn-sm guardar">💾</button></td>
      </tr>
      <?php endforeach; ?>
    </tbody>

    <!-- TOTAL GENERAL -->
    <tfoot class="table-secondary">
      <tr>
        <th colspan="16" class="text-end">TOTAL GENERAL:</th>
        <th colspan="2" id="totalGeneral">S/ 0.00</th>
      </tr>
    </tfoot>
  </table>
</div>

<div class="d-flex justify-content-between mt-3">
  <a href="admin_contable.php" class="btn btn-secondary">← Volver</a>
  <button class="btn btn-success" id="btnPago">💰 Registrar Pago</button>
</div>
<!-- MODAL DE PAGOS -->
<div class="modal fade" id="modalPago" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Registrar Pago</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label>Fecha de Pago</label>
          <input type="date" id="fechaPago" class="form-control">
        </div>
        <div class="mb-2">
          <label>Monto Total a Pagar</label>
          <input type="number" id="montoPago" class="form-control" min="0">
        </div>
        <div class="mb-2">
          <label>Modo de Aplicación</label>
          <select id="modoPago" class="form-select">
            <option value="total">Total (una deuda)</option>
            <option value="amortizar">Amortizar entre varias</option>
          </select>
        </div>
        <div id="detalleDeudas" class="border rounded p-2 bg-light"></div>
      </div>
      <div class="modal-footer">
        <button id="confirmarPago" class="btn btn-success">Aplicar Pago</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>

<!-- Dependencias necesarias -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const ruc = "<?= $empresa['ruc'] ?>";
const id_empresa = "<?= $empresa['id_empresa'] ?>";

$(function () {

  // === FUNCIÓN: Obtener tasa diaria según rango histórico (SUNAT) ===
  function obtenerTasaDiaria(fechaInicio) {
    const f = new Date(fechaInicio);
    if (isNaN(f)) return 0.0003374;
    if (f < new Date('2020-04-01')) return 0.0003374;
    if (f < new Date('2021-04-01')) return 0.010 / 30;
    return 0.009 / 30;
  }

  // === FUNCIÓN: Calcular días exactos ===
  function calcularDias(fechaInicio, fechaFin) {
    if (!fechaInicio) return 0;
    const inicio = new Date(fechaInicio);
    const fin = fechaFin ? new Date(fechaFin) : new Date();
    inicio.setHours(0, 0, 0, 0);
    fin.setHours(0, 0, 0, 0);
    return Math.max(0, Math.round((fin - inicio) / (1000 * 60 * 60 * 24)) + 1);
  }

  // === FUNCIÓN: Redondear a número entero ===
  const rd = v => {
    const num = parseFloat(v);
    if (isNaN(num)) return 0;
    return Math.round(num);
  };

  // === FUNCIÓN PRINCIPAL: Calcular una fila ===
  function calcularFila(fila, recalcularPorPago = false) {
    let importe = rd(fila.find('[name="importe_tributaria"]').val());
    let capitalizado = rd(fila.find('[name="interes_capitalizado"]').val());
    let pagos = rd(fila.find('[name="pagos"]').val());
    let interes = rd(fila.find('[name="interes_moratorio"]').val());

    if (!recalcularPorPago) {
      let fechaInicio = fila.find('[name="fecha_emision"]').val();
      let fechaFin = fila.find('[name="fecha_calculos"]').val() || new Date().toISOString().split('T')[0];
      let dias = calcularDias(fechaInicio, fechaFin);
      let tasa = obtenerTasaDiaria(fechaInicio);
      interes = rd((importe + capitalizado) * tasa * dias);
    }

    let saldo = rd(importe + capitalizado + interes - pagos);

    fila.find('[name="importe_tributaria"]').val(importe);
    fila.find('[name="interes_capitalizado"]').val(capitalizado);
    fila.find('[name="interes_moratorio"]').val(interes);
    fila.find('[name="pagos"]').val(pagos);
    fila.find('[name="saldo_total"]').val(saldo);

    return { interes, saldo };
  }

  // === FUNCIÓN: Calcular total general ===
  function calcularTotalGeneral() {
    let total = 0;
    $('#tablaDeudas tbody tr').each(function () {
      const saldo = rd($(this).find('[name="saldo_total"]').val());
      total += saldo;
    });
    $('#totalGeneral').text('S/ ' + rd(total));
  }

  // === EVENTOS: Recalcular al editar ===
  $(document).on('input change', 'input:not([readonly]):not([name="pagos"])', function () {
    const fila = $(this).closest('tr');
    calcularFila(fila, false);
    calcularTotalGeneral();
  });

  $(document).on('input change', '[name="pagos"]', function () {
    const fila = $(this).closest('tr');
    calcularFila(fila, true);
    calcularTotalGeneral();
  });

  // === CALCULAR TODO AL CARGAR LA TABLA ===
  setTimeout(() => {
    $('#tablaDeudas tbody tr').each(function () {
      calcularFila($(this), false);
    });
    calcularTotalGeneral();
  }, 200);


  // === AGREGAR NUEVA FILA ===
  $('#agregarFila').click(function () {
    let hoy = new Date().toISOString().split('T')[0];
    let nuevaFila = `
      <tr data-id="">
        <td>Nuevo</td>
        <td><input class="form-control form-control-sm" name="periodo_tributario"></td>
        <td><input class="form-control form-control-sm" name="formulario"></td>
        <td><input class="form-control form-control-sm" name="numero_orden"></td>
        <td><input class="form-control form-control-sm" name="tributo_multa"></td>
        <td><input class="form-control form-control-sm" name="tipo"></td>
        <td><input class="form-control form-control-sm" type="date" name="fecha_emision"></td>
        <td><input class="form-control form-control-sm" type="date" name="fecha_notificacion"></td>
        <td><input class="form-control form-control-sm" type="date" name="fecha_pagos"></td>
        <td><input class="form-control form-control-sm" type="date" name="fecha_calculos" value="${hoy}"></td>
        <td><input class="form-control form-control-sm" name="etapa_basica"></td>
        <td><input class="form-control form-control-sm" type="number" name="importe_tributaria" value="0"></td>
        <td><input class="form-control form-control-sm" type="number" name="interes_capitalizado" value="0"></td>
        <td><input class="form-control form-control-sm" type="number" name="interes_moratorio" value="0" readonly></td>
        <td><input class="form-control form-control-sm" type="number" name="pagos" value="0" readonly></td>
        <td><input class="form-control form-control-sm" type="number" name="saldo_total" value="0" readonly></td>
        <td><button type="button" class="btn btn-success btn-sm guardar">💾</button></td>
      </tr>`;
    $('#tablaDeudas tbody').append(nuevaFila);
  });

  // === GUARDAR FILA (AJAX) ===
  $(document).on('click', '.guardar', function () {
    const fila = $(this).closest('tr');
    const id = fila.data('id') || null;
    const res = calcularFila(fila, false);

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
      interes_moratorio: res.interes,
      pagos: parseFloat(fila.find('[name="pagos"]').val()) || 0,
      saldo_total: res.saldo
    };

    $.ajax({
      url: 'actualizar_deuda_ajax.php',
      type: 'POST',
      data: JSON.stringify(data),
      contentType: 'application/json',
      dataType: 'json',
      success: function (r) {
        if (r.status === 'ok') {
          Swal.fire({ icon: 'success', title: 'Guardado', text: 'Registro actualizado', timer: 1200, showConfirmButton: false });
          if (!id && r.nuevo_id) fila.attr('data-id', r.nuevo_id);
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: r.message });
        }
      },
      error: function (xhr) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión con el servidor.' });
        console.error(xhr.responseText);
      }
    });
  });

  // === PAGOS (mostrar modal) ===
  $('#btnPago').click(function () {
    const seleccionadas = $('.chkDeuda:checked');
    if (seleccionadas.length === 0)
      return Swal.fire('Selecciona al menos una deuda.');

    let html = '<ul>';
    seleccionadas.each(function () {
      const fila = $(this).closest('tr');
      const per = fila.find('[name="periodo_tributario"]').val();
      const saldo = fila.find('[name="saldo_total"]').val();
      html += `<li>${per} — Saldo: S/${saldo}</li>`;
    });
    html += '</ul>';
    $('#detalleDeudas').html(html);

    const modal = new bootstrap.Modal(document.getElementById('modalPago'));
    modal.show();
  });

  // === CONFIRMAR PAGO ===
  $('#confirmarPago').click(function () {
    const fecha = $('#fechaPago').val();
    const monto = parseFloat($('#montoPago').val());
    const modo = $('#modoPago').val();

    if (!fecha || monto <= 0) return Swal.fire('Complete los datos del pago.');

    const deudas = [];
    $('.chkDeuda:checked').each(function () {
      const fila = $(this).closest('tr');
      const id = fila.data('id');
      const saldo = parseFloat(fila.find('[name="saldo_total"]').val());
      const pagos_anteriores = parseFloat(fila.find('[name="pagos"]').val()) || 0;
      deudas.push({ id, fila, saldo, pagos_anteriores });
    });

    let restante = monto;
    deudas.forEach(d => {
      if (modo === 'total' && deudas.length === 1) d.pago = monto;
      else {
        if (restante >= d.saldo) { d.pago = d.saldo; restante -= d.saldo; }
        else { d.pago = restante; restante = 0; }
      }
    });

    deudas.forEach(d => {
      const nuevoPago = d.pagos_anteriores + d.pago;
      const nuevoSaldo = Math.max(0, d.saldo - d.pago);

      // Actualizar solo pagos y saldo en la fila (NO recalcular interés)
      d.fila.find('[name="pagos"]').val(nuevoPago.toFixed(2));
      d.fila.find('[name="saldo_total"]').val(nuevoSaldo.toFixed(2));
      d.fila.find('[name="fecha_pagos"]').val(fecha);

      const data = {
        id: d.id,
        id_empresa,
        ruc,
        pagos: nuevoPago,
        saldo_total: nuevoSaldo,
        fecha_pagos: fecha
      };

      $.ajax({
        url: 'actualizar_deuda_ajax.php',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        dataType: 'json',
        success: function (r) {
          if (r.status !== 'ok') console.error('Error al actualizar deuda:', r.message);
        },
        error: function (xhr) {
          console.error('Error AJAX:', xhr.responseText);
        }
      });
    });

    Swal.fire({
      icon: 'success',
      title: 'Pago aplicado',
      text: 'Los pagos se registraron correctamente.',
      timer: 1500,
      showConfirmButton: false
    });

    const modal = bootstrap.Modal.getInstance(document.getElementById('modalPago'));
    modal.hide();
  });

});
</script>




</body>
</html>
