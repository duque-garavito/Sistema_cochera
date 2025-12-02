<?php

/**
 * Configuración de APIs externas
 */

// Configuración de la API de DNI
// 
// ⚠️ IMPORTANTE: 
// - La API está DESHABILITADA por defecto para evitar errores
// - Para activarla, cambia API_DNI_ENABLED a true
// - La mayoría de APIs requieren un token de autenticación
// - Si no tienes token o no quieres usar la API, déjala deshabilitada
//
// 📖 Ver CONFIGURAR_API.md para más información

define('API_DNI_URL', 'https://apiperu.dev/api/dni'); // Cambiar por la URL real de tu API
define('API_DNI_ENABLED', true); // Activar/desactivar la consulta a la API (false = desactivado, true = activado)
define('API_DNI_TIMEOUT', 10); // Timeout en segundos
define('API_DNI_TOKEN', '99051b1bfcffcb7e7ae07c6cdfb8506956ae281162353f8408ffe0a4cd2c0e8f'); // Token de autenticación (ejemplo: 'tu-token-aqui')

/**
 * Función para obtener los headers de la API
 */
function getAPI_DNI_Headers()
{
    $headers = [
        'Content-Type: application/json',
        'User-Agent: Sistema-Cochera/1.0'
    ];

    // Agregar token de autorización si está configurado
    if (!empty(API_DNI_TOKEN)) {
        $headers[] = 'Authorization: Bearer ' . API_DNI_TOKEN;
    }

    return $headers;
}

// Por compatibilidad, mantener la constante (sin token)
define('API_DNI_HEADERS', [
    'Content-Type: application/json',
    'User-Agent: Sistema-Cochera/1.0'
]);

/**
 * Función para consultar la API de DNI
 */
function consultarAPI_DNI($dni)
{
    if (!API_DNI_ENABLED) {
        return null;
    }

    // Construir URL correctamente
    $api_url = rtrim(API_DNI_URL, '/') . '/' . $dni;

    // Usar cURL en lugar de file_get_contents para mejor control de errores
    $ch = curl_init();

    // Obtener headers (incluye token si está configurado)
    $headers = getAPI_DNI_Headers();

    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => API_DNI_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false, // Solo para desarrollo
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Sistema-Cochera/1.0'
    ]);

    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);

    curl_close($ch);

    // Verificar errores de cURL
    if ($result === false || !empty($curl_error)) {
        error_log("Error cURL al consultar API DNI: " . $curl_error);
        return null;
    }

    // Verificar código HTTP
    if ($http_code !== 200) {
        error_log("HTTP Error al consultar API DNI: " . $http_code);
        return null;
    }

    $data = json_decode($result, true);

    // Verificar si el JSON es válido
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error JSON al consultar API DNI: " . json_last_error_msg());
        return null;
    }

    return $data;
}
