<?php
// listar_empresas.php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../db/Database.php";

try {
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->query("SELECT id_empresa, ruc, razon_social, usuario_sol, clave_sol, api_client_id, api_client_secret, fecha_registro, 
                                 IFNULL(estado, 'ACTIVO') AS estado
                          FROM empresas
                          ORDER BY id_empresa ASC");

    $empresas = $stmt->fetchAll();

    echo json_encode(["success" => true, "data" => $empresas]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Error SQL: " . $e->getMessage()]);
}
