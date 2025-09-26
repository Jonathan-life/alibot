<?php
require_once __DIR__ . '/../db/Database.php';

header('Content-Type: application/json');

// Leer datos JSON del cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id_empresa'])) {
    echo json_encode(["success" => false, "error" => "ID no proporcionado"]);
    exit;
}

$id = intval($data['id_empresa']);

// Conexión con PDO
$db = new Database();
$conn = $db->getConnection();

try {
    $stmt = $conn->prepare("DELETE FROM empresas WHERE id_empresa = :id");
    $stmt->bindValue(":id", $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "No se pudo eliminar"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
