<?php
// Tus credenciales de la aplicación
$apiId = "4c73ca92-21b1-43c9-9bce-d6650babbf4a";       // Reemplaza con tu ID API
$apiKey = "zCqhlFP5CRGWHNBHJoAEXg==";   // Reemplaza con tu Clave API

// RUC de prueba (opcional si el endpoint requiere)
$ruc = "20494384273";

// Endpoint de prueba (verifica que tu API tenga uno de test, si no usa uno real)
$url = "https://api.alibot.solver.com.pe/test";

// Configurar CURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "API-ID: $apiId",
    "API-KEY: $apiKey"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Ejecutar la petición
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Mostrar resultado
echo "<pre>";
echo "HTTP Status: $http_code\n";
echo "Respuesta de la API: ";
print_r($response);
echo "</pre>";
?>
