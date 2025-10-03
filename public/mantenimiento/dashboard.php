<?php
require_once __DIR__ . '/../../Controllers/EmpresaController.php';

if (!isset($_GET['id'])) die("ID de empresa no proporcionado.");

$idEmpresa = intval($_GET['id']);
$controller = new EmpresaController();
$empresa = $controller->obtenerEmpresaPorId($idEmpresa);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard - <?= htmlspecialchars($empresa['razon_social']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body { background: #f4f6f9; }
    .sidebar { min-width: 250px; height: 100vh; }
    .card { border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
</style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="bg-dark text-white p-3 sidebar">
        <h4>📊 Panel</h4>
        <ul class="nav flex-column mt-3">
            <li class="nav-item"><a href="index.php" class="nav-link text-white">← Volver</a></li>
            <li class="nav-item"><a href="#" class="nav-link text-white">Facturas</a></li>
            <li class="nav-item"><a href="#" class="nav-link text-white">Estadísticas</a></li>
            <li class="nav-item"><a href="#" class="nav-link text-white">Documentos</a></li>
        </ul>
    </div>

    <!-- Main -->
    <div class="p-4 flex-grow-1">
        <h2>Dashboard: <?= htmlspecialchars($empresa['razon_social']) ?></h2>
        <p><strong>RUC:</strong> <?= $empresa['ruc'] ?></p>

        <!-- Filtro de fechas -->
        <form id="formFiltro" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha Fin</label>
                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>

        <!-- Resumen -->
        <div class="row g-4 mb-4">
            <div class="col-md-3"><div class="card p-3 text-center"><h5>Ingresos</h5><h3 id="ingresos">-</h3></div></div>
            <div class="col-md-3"><div class="card p-3 text-center"><h5>Egresos</h5><h3 id="egresos">-</h3></div></div>
            <div class="col-md-3"><div class="card p-3 text-center"><h5>IGV Ventas</h5><h3 id="igv_ventas">-</h3></div></div>
            <div class="col-md-3"><div class="card p-3 text-center"><h5>IGV Compras</h5><h3 id="igv_compras">-</h3></div></div>
        </div>

        <!-- Gráfico -->
        <div class="card p-4">
            <h5>Comparativa Ingresos vs Egresos</h5>
            <canvas id="chartFinanzas" height="100"></canvas>
        </div>

        <div id="mensaje" class="mt-3"></div>
    </div>
</div>

<script>
let chart;

async function cargarDatos(fechaInicio='', fechaFin='') {
    try {
        let url = `../../Controllers/ResumenFinancieroController.php?id_empresa=<?= $idEmpresa ?>`;
        if(fechaInicio && fechaFin){
            url += `&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
        }

        const response = await fetch(url);
        const data = await response.json();

        if(data.success && data.data.length>0){
            const resumen = data.data[0];
            document.getElementById("ingresos").innerText = "S/ " + parseFloat(resumen.ingresos).toFixed(2);
            document.getElementById("egresos").innerText = "S/ " + parseFloat(resumen.egresos).toFixed(2);
            document.getElementById("igv_ventas").innerText = "S/ " + parseFloat(resumen.igv_ventas).toFixed(2);
            document.getElementById("igv_compras").innerText = "S/ " + parseFloat(resumen.igv_compras).toFixed(2);

            if(chart) chart.destroy();
            chart = new Chart(document.getElementById("chartFinanzas"), {
                type: "bar",
                data: {
                    labels:["Ingresos","Egresos"],
                    datasets:[{
                        label:"Monto (S/)",
                        data:[resumen.ingresos,resumen.egresos],
                        backgroundColor:["#4CAF50","#F44336"]
                    }]
                },
                options:{ responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
            });

            document.getElementById("mensaje").innerHTML = "";
        } else {
            document.getElementById("mensaje").innerHTML = "<div class='alert alert-warning'>No hay datos para este rango.</div>";
        }

    } catch (error){
        document.getElementById("mensaje").innerHTML = "<div class='alert alert-danger'>Error cargando datos: "+error+"</div>";
    }
}

// Evento de filtro
document.getElementById("formFiltro").addEventListener("submit", e=>{
    e.preventDefault();
    const fi = document.getElementById("fecha_inicio").value;
    const ff = document.getElementById("fecha_fin").value;
    cargarDatos(fi,ff);
});

// Cargar totales por defecto (todo el histórico)
cargarDatos();
</script>
</body>
</html>




