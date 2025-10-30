<?php
require_once "../../db/Database.php";

$db = new Database();
$pdo = $db->getConnection();

$ruc = $_GET['ruc'] ?? '';

if (!$ruc) {
    echo "<p style='color:red'>⚠️ Debes indicar un RUC.</p>";
    exit;
}

// Buscar empresa
$stmt = $pdo->prepare("SELECT * FROM empresas WHERE ruc = ?");
$stmt->execute([$ruc]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {
    echo "<p style='color:red'>❌ No se encontró la empresa con RUC $ruc</p>";
    exit;
}

$id_empresa = $empresa['id_empresa'];

// Buscar deudas
$stmt = $pdo->prepare("SELECT * FROM deudas WHERE id_empresa = ?");
$stmt->execute([$id_empresa]);
$deudas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular total de saldos
$total_saldo = array_sum(array_map(fn($d)=>$d['saldo_total'] ?? 0, $deudas));
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestionar Pagos - <?= htmlspecialchars($empresa['razon_social']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
input[readonly] { background-color: #f0f0f0 !important; cursor: not-allowed; }
</style>
</head>
<body class="p-3">
<div class="container-fluid">
<h2>💰 Pagos de: <?= htmlspecialchars($empresa['razon_social']) ?> (RUC: <?= htmlspecialchars($ruc) ?>)</h2>
<p>Total saldos pendientes: <b>S/ <span id="totalSaldo"><?= number_format($total_saldo,2) ?></span></b></p>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle text-center" id="tablaPagos">
    <thead class="table-warning">
        <tr>
            <th>#</th>
            <th>Periodo</th>
            <th>Formulario</th>
            <th>Número de Orden</th>
            <th>Tributo</th>
            <th>Importe</th>
            <th>Interés Capitalizado</th>
            <th>Interés Moratorio</th>
            <th>Pagos</th>
            <th>Saldo Total</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php $n=1; foreach($deudas as $fila): ?>
        <tr data-id="<?= $fila['id'] ?>">
            <td><?= $n++ ?></td>
            <td><?= htmlspecialchars($fila['periodo_tributario']) ?></td>
            <td><?= htmlspecialchars($fila['formulario']) ?></td>
            <td><?= htmlspecialchars($fila['numero_orden']) ?></td>
            <td><?= htmlspecialchars($fila['tributo_multa']) ?></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm" value="<?= $fila['importe_tributaria'] ?>" readonly></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm" value="<?= $fila['interes_capitalizado'] ?>" readonly></td>
            <td><input type="number" step="1" class="form-control form-control-sm" value="<?= $fila['interes_moratorio'] ?>" readonly></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm pago" value="<?= $fila['pagos'] ?>"></td>
            <td><input type="number" step="1" class="form-control form-control-sm saldo" value="<?= $fila['saldo_total'] ?>" readonly></td>
            <td><button class="btn btn-success btn-sm guardarPago">💾 Guardar</button></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<a href="menu_deudas.php" class="btn btn-secondary mt-3">← Volver</a>
</div>

<script>
$(document).ready(function(){

function actualizarSaldo(fila){
    let importe = parseFloat(fila.find('td:eq(5) input').val()) || 0;
    let capitalizado = parseFloat(fila.find('td:eq(6) input').val()) || 0;
    let interes = parseFloat(fila.find('td:eq(7) input').val()) || 0;
    let pago = parseFloat(fila.find('.pago').val()) || 0;
    let saldo = Math.round((importe + capitalizado + interes) - pago);
    fila.find('.saldo').val(saldo);
    return saldo;
}

function actualizarTotal(){
    let total = 0;
    $('#tablaPagos tbody tr').each(function(){
        total += parseFloat($(this).find('.saldo').val()) || 0;
    });
    $('#totalSaldo').text(total.toFixed(2));
}

// Al cambiar el pago
$(document).on('input', '.pago', function(){
    let fila = $(this).closest('tr');
    actualizarSaldo(fila);
    actualizarTotal();
});

// Guardar pago vía AJAX
$(document).on('click', '.guardarPago', function(){
    let fila = $(this).closest('tr');
    let id = fila.data('id');
    let pago = parseFloat(fila.find('.pago').val()) || 0;
    let saldo = parseFloat(fila.find('.saldo').val()) || 0;

    $.ajax({
        url: 'api/guardar_pago.php',
        type: 'POST',
        data: JSON.stringify({ id_deuda: id, monto: pago }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(res){
            if(res.status==='ok'){
                Swal.fire({ icon:'success', title:'Guardado', text:'Pago registrado', timer:1200, showConfirmButton:false });
                fila.find('.saldo').val(saldo);
                actualizarTotal();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        },
        error: function(xhr){
            Swal.fire('Error', 'Error de conexión', 'error');
            console.error(xhr.responseText);
        }
    });
});

});
</script>
</body>
</html>
