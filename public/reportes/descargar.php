<?php
// 🔹 Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "sistema_contable");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// 🔹 Validar parámetro
if (!isset($_GET['id'])) {
    die("ID de archivo no especificado.");
}

$id_archivo = intval($_GET['id']);

// 🔹 Obtener archivo desde la BD
$sql = "SELECT nombre_archivo, tipo, archivo_binario 
        FROM archivos_factura 
        WHERE id_archivo = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_archivo);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($nombre_archivo, $tipo, $archivo_binario);
    $stmt->fetch();

    // Detectar MIME según el tipo guardado
    $mime = "application/octet-stream";
    if (strtoupper($tipo) === "ZIP") {
        $mime = "application/zip";
    } elseif (strtoupper($tipo) === "PDF") {
        $mime = "application/pdf";
    } elseif (strtoupper($tipo) === "XML") {
        $mime = "application/xml";
    }

    // 🔹 Encabezados para la descarga
    header("Content-Type: $mime");
    header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
    header("Content-Length: " . strlen($archivo_binario));

    echo $archivo_binario;
} else {
    echo "Archivo no encontrado.";
}

$stmt->close();
$conexion->close();
