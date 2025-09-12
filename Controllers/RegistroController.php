<?php
require_once __DIR__ . "/../db/Database.php";

class RegistroController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function registrar() {
        header("Content-Type: application/json; charset=utf-8");

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            echo json_encode(["error" => "No se enviaron datos"]);
            return;
        }

        // Validar obligatorios
        if (empty($data["ruc"]) || empty($data["razonSocial"]) || empty($data["usuarioSol"]) || empty($data["claveSol"])) {
            echo json_encode(["error" => "Faltan campos obligatorios"]);
            return;
        }

        try {
            $sql = "INSERT INTO empresas 
                        (ruc, razon_social, usuario_sol, clave_sol, api_client_id, api_client_secret) 
                    VALUES 
                        (:ruc, :razon_social, :usuario_sol, :clave_sol, :api_client_id, :api_client_secret)";
            
            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(":ruc", $data["ruc"]);
            $stmt->bindParam(":razon_social", $data["razonSocial"]);
            $stmt->bindParam(":usuario_sol", $data["usuarioSol"]);
            $stmt->bindParam(":clave_sol", $data["claveSol"]);
            
            // Campos opcionales (pueden ser null)
            $apiClientId     = $data["apiClientId"]     ?? null;
            $apiClientSecret = $data["apiClientSecret"] ?? null;

            $stmt->bindParam(":api_client_id", $apiClientId);
            $stmt->bindParam(":api_client_secret", $apiClientSecret);

            if ($stmt->execute()) {
                echo json_encode([
                    "success" => true,
                    "message" => "Empresa registrada correctamente"
                ]);
            } else {
                echo json_encode(["error" => "Error al registrar empresa"]);
            }
        } catch (PDOException $e) {
            echo json_encode(["error" => "Error SQL: " . $e->getMessage()]);
        }
    }
}
