<?php
// Configuración conexión
$host = '127.0.0.1'; // o 'localhost'
$port = 3306;        // puerto MySQL
$db   = 'alibot';
$user = 'root';      // usuario MySQL
$pass = '';          // contraseña MySQL
$charset = 'utf8mb4';

// DSN (data source name)
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // NO imprimir nada aquí
} catch (PDOException $e) {
    // Aquí puedes devolver error JSON o terminar script
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}
