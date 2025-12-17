<?php

/**
 * Front Controller - Punto de entrada único
 * Todas las peticiones pasan por aquí
 */

// Debugging: Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar zona horaria (Perú)
date_default_timezone_set('America/Lima');

// Definir constantes
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

// Cargar sistema de routing
require_once ROOT_PATH . '/routes.php';

// Obtener la URI solicitada
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalizar script_name y request_uri
$script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Lógica para extraer la ruta relativa
$path = $request_uri;

// 1. Si la URL incluye el nombre del script (ej: /public/index.php/vehiculos)
if (strpos($request_uri, $script_name) === 0) {
    $path = substr($request_uri, strlen($script_name));
}
// 2. Si la URL incluye el directorio del script (ej: /public/vehiculos)
elseif (strpos($request_uri, dirname($script_name)) === 0) {
    $path = substr($request_uri, strlen(dirname($script_name)));
}
// 3. Si la URL incluye el directorio raíz del proyecto (ej: /Sistema_cochera/api/...)
elseif (strpos($request_uri, dirname(dirname($script_name))) === 0) {
    $path = substr($request_uri, strlen(dirname(dirname($script_name))));
}

// Limpiar la ruta
$path = '/' . ltrim($path, '/');

// Debug routing
file_put_contents('C:/xampp/htdocs/Sistema_cochera/debug_routing.txt', date('[Y-m-d H:i:s] ') . "Request URI: $request_uri | Processed Path: $path\n", FILE_APPEND);

// Procesar la ruta
processRoute($path, $routes);
