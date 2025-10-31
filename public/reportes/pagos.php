<?php
require_once "../../db/Database.php";

$db = new Database();
$pdo = $db->getConnection();

$ruc = $_GET['ruc'] ?? '';
$empresa = isset($_GET['empresa']) ? $_GET['empresa'] : null;


if ($ruc) {
    $stmt = $pdo->prepare("SELECT * FROM empresas WHERE ruc = ?");
    $stmt->execute([$ruc]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($empresa) {
        $id_empresa = $empresa['id_empresa'];

        $stmt = $pdo->prepare("SELECT * FROM deudas WHERE id_empresa = ?");
        $stmt->execute([$id_empresa]);
        $deudas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tasa_diaria_sunat = 0.0003374;
        $total_saldo = 0;

        foreach ($deudas as &$d) {
            $importe = floatval($d['importe_tributaria']);
            $interes_capitalizado = floatval($d['interes_capitalizado']);
            $pagos = floatval($d['pagos']);

            $fecha_vencimiento = new DateTime($d['fecha_emision']); 
            $fecha_calculo = !empty($d['fecha_pagos']) ? new DateTime($d['fecha_pagos']) : new DateTime();
            $dias_mora = $fecha_vencimiento->diff($fecha_calculo)->days;

            $interes_moratorio_hoy = round(($importe + $interes_capitalizado) * $tasa_diaria_sunat * $dias_mora, 2);

            $intereses_pendientes = $interes_capitalizado + $interes_moratorio_hoy;
            $capital_pendiente = $importe;
            $pagos_restantes = $pagos;

            if ($pagos_restantes > 0) {
                if ($pagos_restantes >= $intereses_pendientes) {
                    $pagos_restantes -= $intereses_pendientes;
                    $intereses_pendientes = 0;
                    $capital_pendiente -= $pagos_restantes;
                    if ($capital_pendiente < 0) $capital_pendiente = 0;
                } else {
                    $intereses_pendientes -= $pagos_restantes;
                    $pagos_restantes = 0;
                }
            }

            $saldo_total = round($capital_pendiente + $intereses_pendientes, 2);

            $d['dias'] = $dias_mora;
            $d['interes_moratorio'] = $interes_moratorio_hoy;
            $d['interes_pendiente'] = $intereses_pendientes;
            $d['capital_pendiente'] = $capital_pendiente;
            $d['saldo_total'] = $saldo_total;

            $total_saldo += $saldo_total;
        }

    } else {
        $empresa = null;
        $deudas = [];
        $total_saldo = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pagos Tributarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        input[readonly] { background-color: #f0f0f0 !important; cursor: not-allowed; }
    </style>
</head>
<body class="p-3">
<div class="container-fluid">
    <h2 class="mb-3">💰 Gestión de Pagos - ASESCON</h2>

    <!-- Formulario de Búsqueda -->
    <form method="GET" class="mb-4 d-flex align-items-center gap-2">
        <label for="ruc" class="form-label mb-0"><b>Ingrese RUC:</b></label>
        <input type="text" id="ruc" name="ruc" class="form-control w-auto" maxlength="11" value="<?= htmlspecialchars($ruc) ?>" required>
        <button type="submit" class="btn btn-primary">🔍 Buscar</button>
    </form>

    <?php if ($ruc && !$empresa): ?>
        <p style="color:red">❌ No se encontró la empresa con RUC <?= htmlspecialchars($ruc) ?></p>
    <?php elseif ($empresa): ?>

        <h4>Empresa: <?= htmlspecialchars($empresa['razon_social']) ?> (RUC: <?= htmlspecialchars($ruc) ?>)</h4>
        <p>Total saldos pendientes: <b>S/ <span id="totalSaldo"><?= number_format($total_saldo, 2) ?></span></b></p>

        <!-- Tabla de Deudas -->
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-hover align-middle text-center" id="tablaPagos">
                <thead class="table-warning">
                    <tr>
                        <th>✔</th>
                        <th>#</th>
                        <th>Periodo</th>
                        <th>Formulario</th>
                        <th>N° Orden</th>
                        <th>Tributo</th>
                        <th>Importe</th>
                        <th>Días</th>
                        <th>Int. Moratorio</th>
                        <th>Pagos</th>
                        <th>Capital Pendiente</th>
                        <th>Int. Pendiente</th>
                        <th>Saldo Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n = 1; foreach($deudas as $fila): ?>
                    <tr data-id="<?= $fila['id'] ?>">
                        <td><input type="checkbox" class="selectDeuda"></td>
                        <td><?= $n++ ?></td>
                        <td><?= htmlspecialchars($fila['periodo_tributario']) ?></td>
                        <td><?= htmlspecialchars($fila['formulario']) ?></td>
                        <td><?= htmlspecialchars($fila['numero_orden']) ?></td>
                        <td><?= htmlspecialchars($fila['tributo_multa']) ?></td>
                        <td><input type="number" class="form-control form-control-sm" value="<?= $fila['importe_tributaria'] ?>" readonly></td>
                        <td><?= $fila['dias'] ?></td>
                        <td><input type="number" class="form-control form-control-sm" value="<?= $fila['interes_moratorio'] ?>" readonly></td>
                        <td><input type="number" class="form-control form-control-sm pago" value="<?= $fila['pagos'] ?>" readonly></td>
                        <td><input type="number" class="form-control form-control-sm" value="<?= $fila['capital_pendiente'] ?>" readonly></td>
                        <td><input type="number" class="form-control form-control-sm" value="<?= $fila['interes_pendiente'] ?>" readonly></td>
                        <td><input type="number" class="form-control form-control-sm saldo" value="<?= $fila['saldo_total'] ?>" readonly></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Botón para Abrir Modal -->
        <div class="mt-2">
            <button id="btnAbrirModal" class="btn btn-success">💾 Registrar Pago</button>
        </div>

        <a href="admin_contable.php" class="btn btn-secondary mt-3">← Volver</a>

        <!-- Modal para Registrar Pago -->
        <div class="modal fade" id="modalPago" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Registrar Pago</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Fecha de Pago:</label>
                            <input type="date" id="modalFechaPago" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Monto Total a Pagar:</label>
                            <input type="number" id="modalMontoPago" class="form-control" min="0">
                        </div>
                        <div class="mb-3">
                            <label>Modo de Aplicación:</label>
                            <select id="modalModoPago" class="form-select">
                                <option value="total">Total a una deuda</option>
                                <option value="amortizar">Amortizar entre seleccionadas</option>
                            </select>
                        </div>
                        <div id="listaDeudasSeleccionadas" class="mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="btnConfirmarPago" class="btn btn-success">Aplicar Pago</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<!-- Bootstrap JS y jQuery -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Instancia del modal
    let modalPago = new bootstrap.Modal(document.getElementById('modalPago'));

    // Abrir modal al hacer clic en "Registrar Pago"
    $('#btnAbrirModal').click(function() {
        let seleccionadas = $('#tablaPagos tbody .selectDeuda:checked');

        if (seleccionadas.length === 0) {
            Swal.fire('Error', 'Seleccione al menos una deuda', 'error');
            return;
        }

        // Limpiar modal
        $('#modalFechaPago').val('');
        $('#modalMontoPago').val('');
        $('#modalModoPago').val('total');
        $('#listaDeudasSeleccionadas').empty();

        if (seleccionadas.length > 1) {
            let html = '<ul class="list-group">';
            seleccionadas.each(function() {
                let fila = $(this).closest('tr');
                let info = fila.find('td:eq(2)').text() + ' - S/ ' + fila.find('.saldo').val();
                html += `<li class="list-group-item">
                            <input type="radio" name="deudaPrincipal" value="${fila.data('id')}"> ${info}
                         </li>`;
            });
            html += '</ul>';
            $('#listaDeudasSeleccionadas').html(html);
        }

        modalPago.show();
    });

    // Confirmar pago
    $('#btnConfirmarPago').click(function() {
        let fecha = $('#modalFechaPago').val();
        let montoTotal = parseFloat($('#modalMontoPago').val()) || 0;
        let modo = $('#modalModoPago').val();

        if (!fecha) {
            Swal.fire('Error', 'Ingrese la fecha de pago', 'error');
            return;
        }
        if (montoTotal <= 0) {
            Swal.fire('Error', 'Ingrese un monto válido', 'error');
            return;
        }

        // Recopilar deudas seleccionadas
        let deudas = [];
        $('#tablaPagos tbody tr').each(function() {
            if ($(this).find('.selectDeuda').is(':checked')) {
                let fila = $(this);
                let id = fila.data('id');
                let capital = parseFloat(fila.find('td:eq(10) input').val()) || 0;
                let interes = parseFloat(fila.find('td:eq(11) input').val()) || 0;
                let saldo = parseFloat(fila.find('.saldo').val()) || 0;
                deudas.push({ id, capital, interes, saldo });
            }
        });

        if (deudas.length === 0) {
            Swal.fire('Error', 'Seleccione al menos una deuda', 'error');
            return;
        }

        // Si es pago total y hay varias deudas, seleccionar la principal
        if (modo === 'total' && deudas.length > 1) {
            let idPrincipal = $('input[name=deudaPrincipal]:checked').val();
            if (!idPrincipal) {
                Swal.fire('Error', 'Seleccione la deuda a pagar totalmente', 'error');
                return;
            }
            deudas = deudas.filter(d => d.id == idPrincipal);
        }

        // Distribuir pago entre interés y capital
        let montoRestante = montoTotal;
        deudas.forEach(d => {
            let saldoDeuda = d.capital + d.interes;
            let aplicar = Math.min(montoRestante, saldoDeuda);

            if (aplicar >= d.interes) {
                aplicar -= d.interes;
                d.interes = 0;
                d.capital -= aplicar;
                if (d.capital < 0) d.capital = 0;
            } else {
                d.interes -= aplicar;
            }

            d.saldo = d.capital + d.interes;
            montoRestante -= aplicar;
        });

        // Preparar datos para enviar al servidor
        let pagos = deudas.map(d => ({
            id_deuda: d.id,
            monto: (d.capital + d.interes).toFixed(2),
            interes_moratorio: d.interes.toFixed(2),
            capital_pagado: d.capital.toFixed(2),
            saldo_pendiente: d.saldo.toFixed(2),
            fecha_pago: fecha,
            metodo_pago: modo
        }));

        $.ajax({
            url: '/alibot/api/actualizar_pagos.php',
            type: 'POST',
            data: JSON.stringify({ pagos }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'ok') {
                    // Actualizar tabla
                    deudas.forEach(d => {
                        let fila = $('#tablaPagos tbody tr[data-id="' + d.id + '"]');
                        fila.find('td:eq(10) input').val(d.capital.toFixed(2));
                        fila.find('td:eq(11) input').val(d.interes.toFixed(2));
                        fila.find('.saldo').val(d.saldo.toFixed(2));
                        fila.find('.pago').val(0).prop('disabled', true);
                        fila.find('.selectDeuda').prop('checked', false);
                    });

                    // Recalcular saldo total
                    let total = 0;
                    $('#tablaPagos tbody tr').each(function() {
                        total += parseFloat($(this).find('.saldo').val()) || 0;
                    });
                    $('#totalSaldo').text(total.toFixed(2));

                    modalPago.hide();
                    Swal.fire({ icon: 'success', title: 'Pago aplicado', timer: 1200, showConfirmButton: false });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo actualizar la base de datos', 'error');
            }
        });
    });
});
</script>


</body>
</html>
