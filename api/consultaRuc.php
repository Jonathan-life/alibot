<?php
header("Content-Type: application/json; charset=utf-8");

if (!isset($_GET['ruc'])) {
    echo json_encode(["error" => "Falta el parámetro RUC"]);
    exit;
}

$ruc = trim($_GET['ruc']);

// URL para consulta extendida
$url = "https://api.decolecta.com/v1/sunat/ruc/full?numero=" . $ruc;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer sk_11811.yyYsci6katJjLjASSLauYChZQ1NUxIyb"
]);

// Solo para desarrollo local si hay problemas de SSL
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
if ($response === false) {
    echo json_encode(["error" => curl_error($ch)]);
    curl_close($ch);
    exit;
}
curl_close($ch);

echo $response;
