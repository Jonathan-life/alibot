<?php
// Evitar límite de tiempo
set_time_limit(0);

// Conexión MySQL
$mysqli = new mysqli("localhost", "root", "", "sistema_contable");
if ($mysqli->connect_errno) {
    die("Error de conexión MySQL: " . $mysqli->connect_error);
}

// Si enviaron el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_empresa = intval($_POST["id_empresa"]);
    $fecha_inicio = $_POST["fecha_inicio"];
    $fecha_fin = $_POST["fecha_fin"];

    // Obtener credenciales de la empresa
    $stmt = $mysqli->prepare("SELECT ruc, usuario_sol, clave_sol, razon_social 
                              FROM empresas 
                              WHERE id_empresa=? AND estado='ACTIVO'");
    $stmt->bind_param("i", $id_empresa);
    $stmt->execute();
    $empresa = $stmt->get_result()->fetch_assoc();

    if ($empresa) {
        // Rutas absolutas
        $python = "C:\\Users\\ASUS\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";
        $script = "C:\\wamp64\\www\\alibot-api\\bot\\selenium_bot.py";
        $json_file = "C:\\wamp64\\www\\alibot-api\\bot\\data.json"; // Archivo temporal
        $log = "C:\\wamp64\\www\\alibot-api\\bot\\logs.txt";

        // Guardar datos en archivo JSON
        file_put_contents($json_file, json_encode([
            "empresa" => $empresa,
            "fecha_inicio" => $fecha_inicio,
            "fecha_fin" => $fecha_fin
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        // Ejecutar script Python en background (Windows)
        // start /B permite ejecutar en segundo plano
        $cmd = "start /B \"\" \"$python\" \"$script\" \"$json_file\" > \"$log\" 2>&1";
        exec($cmd);

        echo "✅ Descarga iniciada para " . htmlspecialchars($empresa["razon_social"]) . ". Revisa el log en <strong>$log</strong>.";
    } else {
        echo "❌ Empresa no encontrada.";
    }
    exit;
}

// Si no hay POST, mostrar formulario
$result = $mysqli->query("SELECT id_empresa, razon_social FROM empresas WHERE estado='ACTIVO'");
$empresas = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Descargar documentos SUNAT</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h2 { color: #333; }
        form { background: #f9f9f9; padding: 20px; border-radius: 8px; width: 400px; }
        label { display: block; margin-top: 10px; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc; }
        button { margin-top: 20px; padding: 10px 15px; border: none; border-radius: 4px; background: #28a745; color: white; cursor: pointer; }
        button:hover { background: #218838; }
    </style>
</head>
<body>
    <h2>Descargar documentos SUNAT</h2>
    <form method="POST">
        <label>Empresa:</label>
        <select name="id_empresa" required>
            <?php foreach ($empresas as $e): ?>
                <option value="<?= $e['id_empresa'] ?>"><?= htmlspecialchars($e['razon_social']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Fecha inicio:</label>
        <input type="text" name="fecha_inicio" placeholder="dd/mm/yyyy" required>
        <label>Fecha fin:</label>
        <input type="text" name="fecha_fin" placeholder="dd/mm/yyyy" required>
        <button type="submit">Descargar</button>
    </form>
</body>
</html>
