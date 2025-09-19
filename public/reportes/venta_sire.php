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

    // Obtener datos de la empresa
    $stmt = $mysqli->prepare("SELECT id_empresa, ruc, usuario_sol, clave_sol, razon_social 
                              FROM empresas 
                              WHERE id_empresa=? AND estado='ACTIVO'");
    $stmt->bind_param("i", $id_empresa);
    $stmt->execute();
    $empresa = $stmt->get_result()->fetch_assoc();

    if (!$empresa) {
        echo "❌ Empresa no encontrada.";
        exit;
    }

    // Ejecutable de Python, sin ruta fija, para que use el que esté en PATH
    $python = "C:\\Users\\SENATI\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";

    // Base del proyecto, dos niveles arriba de este archivo
    $base_dir = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');

    // Rutas relativas desde base_dir
    $selenium_script = $base_dir . DIRECTORY_SEPARATOR . "bot" . DIRECTORY_SEPARATOR . "selenium_bot.py";
    $procesador_script = $base_dir . DIRECTORY_SEPARATOR . "bot" . DIRECTORY_SEPARATOR . "procesar_archivos.py";
    $json_file = $base_dir . DIRECTORY_SEPARATOR . "bot" . DIRECTORY_SEPARATOR . "data.json";
    $log_file = $base_dir . DIRECTORY_SEPARATOR . "bot" . DIRECTORY_SEPARATOR . "log_process_" . time() . ".txt";

   
    echo "Python: $python\n";
    echo "Selenium script: $selenium_script\n";
    echo "Procesador script: $procesador_script\n";
    echo "JSON file: $json_file\n";
    echo "Log file: $log_file\n";

    // Ejemplo para ejecutar selenium_bot.py
    // $command = escapeshellcmd("$python $selenium_script");
    // exec($command, $output, $return_var);

    // Crear JSON
    $json_data = [
        "empresa" => $empresa,
        "fecha_inicio" => $fecha_inicio,
        "fecha_fin" => $fecha_fin
    ];
    file_put_contents($json_file, json_encode($json_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

    // Ejecutar primer script: selenium_bot.py (descarga)
    $cmd1 = "\"$python\" \"$selenium_script\" \"$json_file\" 2>&1";
    exec($cmd1, $output1, $return1);
    file_put_contents($log_file, "=== selenium_bot.py output ===\n" . implode("\n", $output1) . "\n\n", FILE_APPEND);

    // Si tuvo éxito, ejecutar procesamiento
    if ($return1 === 0) {
        $cmd2 = "\"$python\" \"$procesador_script\" \"$json_file\" 2>&1";
        exec($cmd2, $output2, $return2);
        file_put_contents($log_file, "=== procesar_archivos.py output ===\n" . implode("\n", $output2) . "\n\n", FILE_APPEND);
    } else {
        $return2 = 1; // indicar fallo en procesamiento pues la descarga falló
        $output2 = ["No se ejecutó procesar_archivos.py porque selenium_bot.py falló."];
    }

    // Mostrar al usuario
    if ($return1 === 0 && $return2 === 0) {
        echo "✅ Todo completado para " . htmlspecialchars($empresa["razon_social"]) . ". Revisa el log en <strong>$log_file</strong>.";
    } else {
        echo "❌ Hubo errores durante el proceso.<br>";
        echo "<pre>" . htmlspecialchars(implode("\n", $output1 ?? []) . "\n" . implode("\n", $output2 ?? [])) . "</pre>";
        echo "Revisa el log en <strong>$log_file</strong>.";
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
    <meta charset="UTF-8" />
    <title>Descargar documentos SUNAT</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h2 { color: #333; }
        form { background: #f9f9f9; padding: 20px; border-radius: 8px; width: 400px; }
        label { display: block; margin-top: 10px; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc; }
        button { margin-top: 20px; padding: 10px 15px; border: none; border-radius: 4px; background: #28a745; color: white; cursor: pointer; }
        button:hover { background: #218838; }
        pre { background: #eee; padding: 10px; border-radius: 6px; margin-top: 20px; white-space: pre-wrap; word-wrap: break-word; }
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
        <input type="text" name="fecha_inicio" placeholder="dd/mm/yyyy" required />
        <label>Fecha fin:</label>
        <input type="text" name="fecha_fin" placeholder="dd/mm/yyyy" required />
        <button type="submit" id="btn-descargar">Descargar</button>
    </form>

    <script>
    document.querySelector("form").addEventListener("submit", function(e) {
        e.preventDefault();

        const form = e.target;
        const data = new FormData(form);

        const btn = document.getElementById("btn-descargar");
        btn.disabled = true;
        btn.innerText = "Procesando...";

        fetch(form.action || window.location.href, {
            method: "POST",
            body: data
        })
        .then(res => res.text())
        .then(html => {
            const div = document.createElement("div");
            div.innerHTML = html;
            document.body.appendChild(div);

            btn.disabled = false;
            btn.innerText = "Descargar";
        })
        .catch(err => {
            alert("❌ Error al enviar la solicitud: " + err);
            btn.disabled = false;
            btn.innerText = "Descargar";
        });
    });
    </script>
</body>
</html>
