<?php
// ========================
// 1. OBTENER TOKEN
// ========================
$clientId = "79776d57-7f59-46ec-8137-7057ed35b9a6";
$clientSecret = "NureXmMJWDzLdmCoe321Sw==";
$ruc = "20494384273";
$usuarioSOL = "08258794";
$claveSOL = "MinESE2023";

$urlToken = "https://api-seguridad.sunat.gob.pe/v1/clientessol/$clientId/oauth2/token/";

$body = http_build_query([
  "grant_type"    => "password",
  "scope"         => "https://api-cpe.sunat.gob.pe",
  "client_id"     => $clientId,
  "client_secret" => $clientSecret,
  "username"      => "$ruc$usuarioSOL",
  "password"      => $claveSOL
]);

$ch = curl_init($urlToken);
curl_setopt_array($ch, [
  CURLOPT_POST           => true,
  CURLOPT_POSTFIELDS     => $body,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER     => ["Content-Type: application/x-www-form-urlencoded"]
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if (!isset($data["access_token"])) {
    die("Error obteniendo token: $response");
}
$token = $data["access_token"];

// ========================
// 2. DESCARGAR PROPUESTA RVIE
// ========================
// Periodo tributario (ejemplo: Agosto 2025 = 202508)
$periodo = "202508"; 

$urlPropuesta = "https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvie/propuesta/$periodo";

$ch = curl_init($urlPropuesta);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER     => [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
  ]
]);
$respProp = curl_exec($ch);
curl_close($ch);

$dataProp = json_decode($respProp, true);
if (!isset($dataProp["ticket"])) {
    die("Error obteniendo propuesta: $respProp");
}
$ticket = $dataProp["ticket"];
echo "Ticket generado: $ticket\n";

// ========================
// 3. CONSULTAR ESTADO DEL TICKET
// ========================
$urlEstado = "https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvie/estado/$ticket";

do {
    $ch = curl_init($urlEstado);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER     => [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
      ]
    ]);
    $respEstado = curl_exec($ch);
    curl_close($ch);

    $dataEstado = json_decode($respEstado, true);
    $estado = $dataEstado["estado"] ?? "Pendiente";

    echo "Estado ticket: $estado\n";

    if ($estado !== "Terminado") {
        sleep(3); // Espera 3 segundos y vuelve a consultar
    }

} while ($estado !== "Terminado");

// ========================
// 4. DESCARGAR ARCHIVO FINAL
// ========================
$urlArchivo = "https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvie/archivo/$ticket";

$ch = curl_init($urlArchivo);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER     => [
    "Authorization: Bearer $token"
  ]
]);
$archivo = curl_exec($ch);
curl_close($ch);

// Guardamos el ZIP en disco
$file = "ventas_rvie_$periodo.zip";
file_put_contents($file, $archivo);

echo "Archivo de ventas descargado: $file\n";
?>
