<?php

require_once __DIR__ . '/../models/Vehiculo.php';
require_once __DIR__ . '/../models/Movimiento.php';
require_once __DIR__ . '/../../config/api_config.php';

/**
 * ApiController
 * Maneja endpoints AJAX y API
 */
class ApiController
{
    private $vehiculoModel;
    private $movimientoModel;

    public function __construct()
    {
        $this->vehiculoModel = new Vehiculo();
        $this->movimientoModel = new Movimiento();
    }

    /**
     * Buscar vehículos (AJAX)
     */
    public function buscar()
    {
        // Prevent HTML error output from breaking JSON
        ini_set('display_errors', 0);
        error_reporting(0);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        // Clean any previous output
        if (ob_get_length()) ob_clean();

        $response = ['success' => false, 'data' => []];

        try {
            if (isset($_GET['termino']) && !empty($_GET['termino'])) {
                $termino = trim($_GET['termino']);
                $resultados = $this->vehiculoModel->obtenerSugerencias($termino);

                foreach ($resultados as $resultado) {
                    $movimiento_activo = $this->movimientoModel->verificarActivo($resultado['id'] ?? null);
                    $tipo_sugerido = $movimiento_activo ? 'Salida' : 'Entrada';

                    $response['data'][] = [
                        'id' => $resultado['id'] ?? null,
                        'placa' => $resultado['placa'] ?? '',
                        'dni' => $resultado['dni'] ?? '',
                        'nombre' => ($resultado['nombre'] ?? '') . ' ' . ($resultado['apellido'] ?? ''),
                        'tipo_vehiculo' => $resultado['tipo_vehiculo'] ?? '',
                        'precio_dia' => Vehiculo::obtenerPrecioPorTipo($resultado['tipo_vehiculo'] ?? 'Otro'),
                        'tipo_movimiento_sugerido' => $tipo_sugerido,
                        'tiene_movimiento_activo' => (bool)$movimiento_activo
                    ];
                }

                $response['success'] = true;
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            error_log("Error in buscar: " . $e->getMessage());
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * Consultar DNI (API externa)
     */
    public function consultarDNI()
    {
        // Prevent HTML error output from breaking JSON
        ini_set('display_errors', 0);
        error_reporting(0);

        $logFile = 'C:/xampp/htdocs/Sistema_cochera/debug_api.txt';
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Request received for DNI lookup\n", FILE_APPEND);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        // Clean any previous output
        if (ob_get_length()) ob_clean();

        $response = ['success' => false, 'data' => []];

        if (!isset($_GET['dni']) || empty($_GET['dni'])) {
            $response['error'] = 'DNI no proporcionado';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }

        $dni = trim($_GET['dni']);
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Processing DNI: $dni\n", FILE_APPEND);

        if (!preg_match('/^[0-9]{8}$/', $dni)) {
            $response['error'] = 'DNI debe tener 8 dígitos';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }

        if (!API_DNI_ENABLED) {
            $response['error'] = 'La consulta automática de API está deshabilitada.';
            $response['debug'] = ['api_enabled' => false];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }

        try {
            $api_data = consultarAPI_DNI($dni);
            
            // Log raw API data
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Raw API Response: " . print_r($api_data, true) . "\n", FILE_APPEND);

            if ($api_data === null) {
                throw new Exception('Error al consultar la API (Respuesta nula)');
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

            if (!empty($nombre) || !empty($apellido)) {
                $response['success'] = true;
                $response['data'] = [
                    'dni' => $dni,
                    'nombre' => trim($nombre),
                    'apellido' => trim($apellido)
                ];
            } else {
                $response['error'] = 'No se encontraron datos para este DNI en la API';
                $response['debug'] = $api_data;
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Error: " . $e->getMessage() . "\n", FILE_APPEND);
        }

        $json_response = json_encode($response, JSON_UNESCAPED_UNICODE);
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Final JSON Response: " . $json_response . "\n", FILE_APPEND);
        
        echo $json_response;
        exit();
    }
}
