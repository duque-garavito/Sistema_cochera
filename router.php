<?php
/**
 * Router script for PHP built-in server
 * Usage: php -S localhost:3000 router.php
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

file_put_contents(__DIR__ . '/debug_router.txt', date('[Y-m-d H:i:s] ') . "Router hit: $uri\n", FILE_APPEND);

// Si el archivo existe (ej: /public/css/style.css), servirlo directamente
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Enrutar todo lo demás a public/index.php
require_once __DIR__ . '/public/index.php';
