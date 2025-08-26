<?php
$clientId = "79776d57-7f59-46ec-8137-7057ed35b9a6";
$clientSecret = "NureXmMJWDzLdmCoe321Sw==";
$ruc = "20494384273";
$usuarioSOL = "08258794";
$claveSOL = "MinESE2023";
$url = "https://api-seguridad.sunat.gob.pe/v1/clientessol/$clientId/oauth2/token/";

$body = http_build_query([
  "grant_type"    => "password",
  "scope"         => "https://api-cpe.sunat.gob.pe",
  "client_id"     => $clientId,
  "client_secret" => $clientSecret,
  "username"      => "$ruc$usuarioSOL",
  "password"      => $claveSOL
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_POST           => true,
  CURLOPT_POSTFIELDS     => $body,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER     => ["Content-Type: application/x-www-form-urlencoded"]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Respuesta: $response\n";
?>
