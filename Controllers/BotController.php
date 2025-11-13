<?php
class BotController {
    private $db;
    private $python;
    private $base_dir;

    public function __construct() {
        // Configurar conexión
        $this->db = new mysqli("localhost", "root", "", "sistema_contable");
        if ($this->db->connect_errno) {
            die("Error MySQL: " . $this->db->connect_error);
        }

        // Ruta del ejecutable de Python
        $this->python = "C:\\Users\\Jonathan\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";

        // Ruta base del proyecto
        $this->base_dir = realpath(__DIR__ . '/..');
    }

    public function ejecutarBot($id_empresa, $fecha_inicio, $fecha_fin, $tipo_descarga) {
        // Obtener datos de la empresa
        $stmt = $this->db->prepare("SELECT id_empresa, ruc, usuario_sol, clave_sol, razon_social 
                                    FROM empresas 
                                    WHERE id_empresa=? AND estado='ACTIVO'");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $empresa = $stmt->get_result()->fetch_assoc();

        if (!$empresa) {
            return ["status" => "error", "mensaje" => "Empresa no encontrada."];
        }

        // Preparar rutas
        $bot_dir = $this->base_dir . DIRECTORY_SEPARATOR . "bot";
        $json_file = $bot_dir . DIRECTORY_SEPARATOR . "data.json";
        $log_file = $bot_dir . DIRECTORY_SEPARATOR . "log_" . time() . ".txt";

        $bot_emitidas = $bot_dir . DIRECTORY_SEPARATOR . "ventaselenium.py";
        $bot_recibidas = $bot_dir . DIRECTORY_SEPARATOR . "selenium_bot.py";
        $procesar_script = $bot_dir . DIRECTORY_SEPARATOR . "procesar_archivos.py";

        // Crear JSON
        // Convertir formato de fecha de Y-m-d → d/m/Y
        $fecha_inicio_fmt = date("d/m/Y", strtotime($fecha_inicio));
        $fecha_fin_fmt = date("d/m/Y", strtotime($fecha_fin));

        $data = [
            "empresa" => $empresa,
            "fecha_inicio" => $fecha_inicio_fmt,
            "fecha_fin" => $fecha_fin_fmt
        ];

        file_put_contents($json_file, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        // --- Comandos a ejecutar ---
        $outputs = [];
        $return_codes = [];

        // Seleccionar qué bots ejecutar según tipo_descarga
        $bots = [];
        if ($tipo_descarga === "emitidas") $bots[] = $bot_emitidas;
        elseif ($tipo_descarga === "recibidas") $bots[] = $bot_recibidas;
        elseif ($tipo_descarga === "ambas") $bots = [$bot_emitidas, $bot_recibidas];

        foreach ($bots as $bot) {
            $cmd = "\"{$this->python}\" \"$bot\" \"$json_file\" 2>&1";
            exec($cmd, $out, $ret);
            $outputs[] = implode("\n", $out);
            $return_codes[] = $ret;
            file_put_contents($log_file, "=== " . basename($bot) . " ===\n" . implode("\n", $out) . "\n\n", FILE_APPEND);
        }

        // Procesamiento final
        $cmd_procesar = "\"{$this->python}\" \"$procesar_script\" \"$json_file\" 2>&1";
        exec($cmd_procesar, $out_proc, $ret_proc);
        $outputs[] = implode("\n", $out_proc);
        $return_codes[] = $ret_proc;
        file_put_contents($log_file, "=== procesar_archivos.py ===\n" . implode("\n", $out_proc) . "\n\n", FILE_APPEND);

        // Resultado global
        $todo_ok = !in_array(1, $return_codes);

        return [
            "status" => $todo_ok ? "success" : "error",
            "mensaje" => $todo_ok
                ? "✅ Todo completado para {$empresa['razon_social']}."
                : "❌ Hubo errores durante la ejecución.",
            "log" => $log_file,
            "salida" => $outputs
        ];
    }

    public function cerrarConexion() {
        $this->db->close();
    }
}
?>
