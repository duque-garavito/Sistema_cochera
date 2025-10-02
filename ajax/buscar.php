<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'data' => []];

if ($_GET && isset($_GET['termino']) && !empty($_GET['termino'])) {
    $termino = trim($_GET['termino']);
    $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'placa';
    
    try {
        $resultados = obtenerSugerencias($termino);
        
        foreach ($resultados as $resultado) {
            // Verificar si tiene movimiento activo
            $movimiento_activo = verificarMovimientoActivo($resultado['id'] ?? null);
            $tipo_sugerido = $movimiento_activo ? 'Salida' : 'Entrada';
            
            $response['data'][] = [
                'id' => $resultado['id'],
                'placa' => $resultado['placa'],
                'dni' => $resultado['dni'],
                'nombre' => $resultado['nombre'] . ' ' . $resultado['apellido'],
                'tipo_vehiculo' => $resultado['tipo_vehiculo'],
                'precio_dia' => obtenerPrecioPorTipo($resultado['tipo_vehiculo']),
                'tipo_movimiento_sugerido' => $tipo_sugerido,
                'tiene_movimiento_activo' => (bool)$movimiento_activo
            ];
        }
        
        $response['success'] = true;
        
    } catch (Exception $e) {
        $response['error'] = $e->getMessage();
    }
}

echo json_encode($response);
?>
