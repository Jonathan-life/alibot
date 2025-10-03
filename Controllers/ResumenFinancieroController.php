<?php
require_once __DIR__ . '/../db/Database.php';

header("Content-Type: application/json");

$idEmpresa   = isset($_GET['id_empresa']) ? intval($_GET['id_empresa']) : 0;
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fechaFin    = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

try {
    $db = (new Database())->getConnection();
    // Condición de fechas: si no hay fechas, muestra todo
    $condFecha = "";
    $params = [$idEmpresa];
    if($fechaInicio && $fechaFin){
        $condFecha = "AND fecha_emision BETWEEN ? AND ?";
        $params[] = $fechaInicio;
        $params[] = $fechaFin;
    }

    // Ingresos (ventas)
    $sqlVentas = "SELECT IFNULL(SUM(importe_total),0) AS ingresos,
                         IFNULL(SUM(igv),0) AS igv_ventas
                  FROM facturas
                  WHERE id_empresa = ? AND origen = 'VENTA' $condFecha";
    $stmt = $db->prepare($sqlVentas);
    $stmt->execute($params);
    $ventas = $stmt->fetch();

    // Egresos (compras)
    $sqlCompras = "SELECT IFNULL(SUM(importe_total),0) AS egresos,
                          IFNULL(SUM(igv),0) AS igv_compras
                   FROM facturas
                   WHERE id_empresa = ? AND origen = 'COMPRA' $condFecha";
    $stmt = $db->prepare($sqlCompras);
    $stmt->execute($params);
    $compras = $stmt->fetch();

    // Resultado
    $data = [
        "ingresos"    => $ventas['ingresos'],
        "egresos"     => $compras['egresos'],
        "igv_ventas"  => $ventas['igv_ventas'],
        "igv_compras" => $compras['igv_compras']
    ];

    echo json_encode(["success" => true, "data" => [$data]]);

} catch (Exception $e) {
    echo json_encode(["success"=>false, "message"=>$e->getMessage()]);
}
