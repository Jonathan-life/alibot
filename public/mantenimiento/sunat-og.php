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
    $python = "C:\\Users\\ASUS\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";

    // Base del proyecto, dos niveles arriba de este archivo
    $base_dir = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');

    // Rutas relativas 
    $selenium_script = $base_dir . DIRECTORY_SEPARATOR . "bot" . DIRECTORY_SEPARATOR . "selenium_bot.py";
    $procesador_script = $base_dir . DIRECTORY_SEPARATOR . "bot" . DIRECTORY_SEPARATOR . "procesar_archivos.py";
    $json_file = $base_dir . DIRECTORY_SEPARATOR . "bot" . DIRECTORY_SEPARATOR . "data.json";
    $log_file = $base_dir . DIRECTORY_SEPARATOR . "bot" . DIRECTORY_SEPARATOR . "log_process_" . time() . ".txt";

   
    echo "Python: $python\n";
    echo "Selenium script: $selenium_script\n";
    echo "Procesador script: $procesador_script\n";
    echo "JSON file: $json_file\n";
    echo "Log file: $log_file\n";



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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f6f9; }
        h2 { color: #333; }
        form { background: #fff; padding: 20px; border-radius: 10px; width: 420px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; margin-top: 8px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; transition: border .2s; }
        input:focus, select:focus { border-color: #007bff; outline: none; box-shadow: 0 0 5px rgba(0,123,255,.3); }
        button { margin-top: 20px; padding: 12px; border: none; border-radius: 6px; background: linear-gradient(135deg, #28a745, #218838); color: white; font-weight: bold; cursor: pointer; transition: background .3s; width: 100%; }
        button:hover { background: linear-gradient(135deg, #218838, #1e7e34); }
        button:disabled { opacity: .7; cursor: not-allowed; }
    </style>
</head>
<body>
    <h2>📂 Descargar documentos SUNAT</h2>
    <form method="POST">
        <label>Empresa:</label>
        <select name="id_empresa" required>
            <?php foreach ($empresas as $e): ?>
                <option value="<?= $e['id_empresa'] ?>"><?= htmlspecialchars($e['razon_social']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Fecha inicio:</label>
        <input type="text" name="fecha_inicio" id="fecha_inicio" placeholder="dd/mm/yyyy" required />
        <label>Fecha fin:</label>
        <input type="text" name="fecha_fin" id="fecha_fin" placeholder="dd/mm/yyyy" required />
        <button type="submit" id="btn-descargar">⬇ Descargar</button>
    </form>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Configuración del calendario con opción de escribir manualmente
    flatpickr("#fecha_inicio", { 
        dateFormat: "d/m/Y", 
        allowInput: true 
    });
    flatpickr("#fecha_fin", { 
        dateFormat: "d/m/Y", 
        allowInput: true 
    });

    document.querySelector("form").addEventListener("submit", function(e) {
        e.preventDefault();

        const form = e.target;
        const data = new FormData(form);

        const btn = document.getElementById("btn-descargar");
        btn.disabled = true;
        btn.innerText = "⏳ Procesando...";

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
            btn.innerText = "⬇ Descargar";
        })
        .catch(err => {
            alert("❌ Error al enviar la solicitud: " + err);
            btn.disabled = false;
            btn.innerText = "⬇ Descargar";
        });
    });
</script>

</body>
</html>
