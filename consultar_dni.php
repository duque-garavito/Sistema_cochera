<?php
// Deshabilitar mostrar errores para evitar HTML en la respuesta JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

require_once __DIR__ . '/../../config/database.php';
require_once '../config/api_config.php';

$response = ['success' => false, 'data' => []];

if ($_GET && isset($_GET['dni']) && !empty($_GET['dni'])) {
    $dni = trim($_GET['dni']);

    // Validar formato de DNI
    if (!preg_match('/^[0-9]{8}$/', $dni)) {
        $response['error'] = 'DNI debe tener 8 dígitos';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Verificar si la API está habilitada
    if (!API_DNI_ENABLED) {
        $response['error'] = 'La consulta automática de API está deshabilitada. Puedes ingresar los datos manualmente.';
        $response['debug'] = [
            'api_enabled' => false,
            'message' => 'Para activar la API, edita config/api_config.php y cambia API_DNI_ENABLED a true'
        ];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    try {
        // Consultar la API externa
        $api_data = consultarAPI_DNI($dni);

        if ($api_data === null) {
            // Intentar obtener más información del error
            $error_msg = 'Error al consultar la API. Verifica la configuración o tu conexión.';

            // Verificar si la URL es accesible
            if (!function_exists('curl_init')) {
                $error_msg = 'cURL no está habilitado en el servidor. Por favor, contacta al administrador.';
            } elseif (!API_DNI_ENABLED) {
                $error_msg = 'La consulta de API está deshabilitada en la configuración.';
            } else {
                // Verificar conectividad básica
                $api_base = rtrim(API_DNI_URL, '/');
                $test_url = parse_url($api_base);
                if (empty($test_url['host'])) {
                    $error_msg = 'La URL de la API no está configurada correctamente.';
                }
            }

            throw new Exception($error_msg);
        }

        // Verificar si la respuesta contiene un error de la API
        if (isset($api_data['error']) || isset($api_data['message'])) {
            $error_msg = $api_data['error'] ?? $api_data['message'] ?? 'La API devolvió un error';
            throw new Exception($error_msg);
        }

        // Si la API tiene estructura {success: true, data: {...}}, extraer los datos del objeto 'data'
        $datos = $api_data;
        if (isset($api_data['success']) && isset($api_data['data']) && is_array($api_data['data'])) {
            $datos = $api_data['data'];
        }

        // Extraer datos según el formato de tu API
        $nombre = '';
        $apellido = '';

        // Formato 1: nombres y apellidos separados
        if (isset($datos['nombres'])) {
            $nombre = $datos['nombres'];
        } elseif (isset($datos['nombre'])) {
            $nombre = $datos['nombre'];
        } elseif (isset($datos['primerNombre']) || isset($datos['primer_nombre'])) {
            $nombre = trim(($datos['primerNombre'] ?? $datos['primer_nombre'] ?? '') . ' ' . ($datos['segundoNombre'] ?? $datos['segundo_nombre'] ?? ''));
        }

        // Formato 2: apellidos separados (soporta camelCase y snake_case)
        if (isset($datos['apellidos'])) {
            $apellido = $datos['apellidos'];
        } elseif (isset($datos['apellido'])) {
            $apellido = $datos['apellido'];
        } elseif (isset($datos['apellidoPaterno']) || isset($datos['apellido_paterno'])) {
            $apellido = trim(
                ($datos['apellidoPaterno'] ?? $datos['apellido_paterno'] ?? '') . ' ' . 
                ($datos['apellidoMaterno'] ?? $datos['apellido_materno'] ?? '')
            );
        }

        // Formato 3: nombre completo (separar)
        if (empty($apellido) && isset($datos['nombreCompleto'])) {
            $partes = explode(' ', trim($datos['nombreCompleto']), 3);
            if (count($partes) >= 2) {
                // Asumir que los primeros dos son apellidos
                $apellido = $partes[0] . (isset($partes[1]) ? ' ' . $partes[1] : '');
                $nombre = isset($partes[2]) ? $partes[2] : '';
            }
        }

        // Formato 4: estructura personalizada de tu API
        // Agregar aquí otros formatos según la respuesta de tu API

        if (!empty($nombre) || !empty($apellido)) {
            $response['success'] = true;
            $response['data'] = [
                'dni' => $dni,
                'nombre' => trim($nombre),
                'apellido' => trim($apellido)
            ];
        } else {
            $response['error'] = 'No se encontraron datos para este DNI en la API';
            $response['debug'] = $api_data; // Para debugging
        }
    } catch (Exception $e) {
        $response['error'] = $e->getMessage();

        // Agregar información de debug si está disponible
        if (!API_DNI_ENABLED) {
            $response['debug'] = [
                'api_enabled' => false,
                'message' => 'La consulta de API está deshabilitada. Puedes activarla en config/api_config.php'
            ];
        } else {
            $response['debug'] = [
                'api_enabled' => true,
                'api_url' => API_DNI_URL,
                'has_token' => !empty(API_DNI_TOKEN),
                'curl_available' => function_exists('curl_init'),
                'message' => 'Verifica la configuración de la API o tu conexión a internet'
            ];
        }
    }
} else {
    $response['error'] = 'DNI no proporcionado';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit();
