<?php

/**
 * Routing System
 * Define las rutas de la aplicación y sus controladores
 */

$routes = [
    '/' => ['controller' => 'MovimientoController', 'method' => 'index'],
    '/movimientos' => ['controller' => 'MovimientoController', 'method' => 'index'],
    '/vehiculos' => ['controller' => 'VehiculoController', 'method' => 'index'],
    '/dashboard' => ['controller' => 'DashboardController', 'method' => 'index'],
    '/reportes' => ['controller' => 'ReporteController', 'method' => 'index'],
    '/login' => ['controller' => 'AuthController', 'method' => 'login'],
    '/logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    
    // Personal Routes
    '/personal' => ['controller' => 'PersonalController', 'method' => 'index'],
    '/personal/crear' => ['controller' => 'PersonalController', 'method' => 'crear'],
    '/caja' => ['controller' => 'CajaController', 'method' => 'index'],
    '/caja/detalle' => ['controller' => 'CajaController', 'method' => 'detalle'],
    
    // Tarifas
    '/tarifas' => ['controller' => 'TarifaController', 'method' => 'index'],
    '/tarifas/guardar' => ['controller' => 'TarifaController', 'method' => 'guardar'],
    '/tarifas/eliminar' => ['controller' => 'TarifaController', 'method' => 'eliminar'],
    
    // API endpoints
    '/api/buscar' => ['controller' => 'ApiController', 'method' => 'buscar'],
    '/api/consultar-dni' => ['controller' => 'ApiController', 'method' => 'consultarDNI'],
];

/**
 * Procesar ruta solicitada
 */
function processRoute($uri, $routes)
{
    // Limpiar query string
    $uri = strtok($uri, '?');
    
    // Buscar ruta exacta
    if (isset($routes[$uri])) {
        $route = $routes[$uri];
        $controllerName = $route['controller'];
        $method = $route['method'];
        
        // Cargar controlador
        $controllerFile = __DIR__ . '/app/controllers/' . $controllerName . '.php';
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            
            $controller = new $controllerName();
            $controller->$method();
            return true;
        }
    }
    
    // Ruta no encontrada
    http_response_code(404);
    echo "404 - Página no encontrada";
    return false;
}
