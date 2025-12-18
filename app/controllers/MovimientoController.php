<?php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Vehiculo.php';
require_once __DIR__ . '/../models/Movimiento.php';

/**
 * MovimientoController
 * Maneja el registro de entrada y salida de vehículos
 */
class MovimientoController
{
    private $usuarioModel;
    private $vehiculoModel;
    private $movimientoModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->vehiculoModel = new Vehiculo();
        $this->movimientoModel = new Movimiento();
    }

    /**
     * Página principal de registro de movimientos
     */
    public function index()
    {
        session_start();
        $this->verificarSesion();

        $mensaje = '';
        $tipo_mensaje = '';

        // Procesar formulario si es POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->procesarMovimiento($_POST);
            $mensaje = $resultado['mensaje'];
            $tipo_mensaje = $resultado['tipo'];
        }

        // Obtener vehículos activos
        $vehiculos_activos = $this->movimientoModel->obtenerActivos();

        // Cargar vista
        require __DIR__ . '/../views/movimientos/index.php';
    }

    /**
     * Procesar registro de entrada o salida
     */
    private function procesarMovimiento($datos)
    {
        try {
            // DEBUG: Loggear datos recibidos
            file_put_contents('debug_post.txt', print_r($datos, true));

            $placa = trim($datos['placa']);
            $dni = trim($datos['dni']);
            $tipo_movimiento = $datos['tipo_movimiento'];
            $observaciones = trim($datos['observaciones'] ?? '');
            
            // Nuevos campos de pago
            $metodo_pago = $datos['metodo_pago'] ?? 'Efectivo';
            $momento_pago = $datos['momento_pago'] ?? 'Salida'; // Por defecto paga a la salida

            // Verificar si el vehículo existe
            $vehiculo = $this->vehiculoModel->buscarPorPlaca($placa);

            if (!$vehiculo) {
                return [
                    'mensaje' => 'Vehículo no encontrado. Por favor registre el vehículo primero.',
                    'tipo' => 'error'
                ];
            }

            // Verificar si el DNI coincide
            $usuario = $this->usuarioModel->buscarPorDNI($dni);

            if (!$usuario || $vehiculo['usuario_id'] != $usuario['id']) {
                return [
                    'mensaje' => 'El DNI no coincide con el propietario registrado del vehículo.',
                    'tipo' => 'error'
                ];
            }

            // Obtener precio por día base
            $precio_por_dia = $vehiculo['precio_por_dia'] ?: Vehiculo::obtenerPrecioPorTipo($vehiculo['tipo_vehiculo']);

            // Aplicar recargo por feriado si fue seleccionado
            if (!empty($datos['es_feriado']) && $datos['es_feriado'] == '1') {
                $tipo = $vehiculo['tipo_vehiculo'];
                $aumento = 0;

                switch ($tipo) {
                    case 'Moto': $aumento = 2; break;
                    case 'Auto': $aumento = 4; break;
                    case 'Camioneta': $aumento = 6; break;
                    default: $aumento = 8; break; // Otro
                }
                
                $precio_por_dia += $aumento;
            }

            // Verificar si está en lista negra (solo para entradas)
            if ($tipo_movimiento === 'Entrada' && !empty($vehiculo['en_lista_negra'])) {
                return [
                    'mensaje' => '⛔ ALERTA: Este vehículo está en la LISTA NEGRA. No se permite el ingreso.',
                    'tipo' => 'error'
                ];
            }

            if ($tipo_movimiento === 'Entrada') {
                return $this->procesarEntrada($vehiculo, $usuario, $precio_por_dia, $observaciones, $metodo_pago, $momento_pago);
            } else {
                return $this->procesarSalida($vehiculo, $precio_por_dia, $metodo_pago);
            }
        } catch (Exception $e) {
            return [
                'mensaje' => 'Error: ' . $e->getMessage(),
                'tipo' => 'error'
            ];
        }
    }

    /**
     * Procesar entrada de vehículo
     */
    private function procesarEntrada($vehiculo, $usuario, $precio_por_dia, $observaciones, $metodo_pago, $momento_pago)
    {
        // Verificar si ya hay un movimiento activo
        $movimiento_activo = $this->movimientoModel->verificarActivo($vehiculo['id']);

        if ($movimiento_activo) {
            return [
                'mensaje' => 'El vehículo ya tiene un registro de entrada activo.',
                'tipo' => 'error'
            ];
        }

        // Registrar entrada (con ID del personal de turno)
        $personal_id = $_SESSION['usuario_id'] ?? null;
        
        $this->movimientoModel->registrarEntrada(
            $vehiculo['id'],
            $usuario['id'],
            'Entrada',
            $precio_por_dia,
            $personal_id,
            $metodo_pago,
            $momento_pago
        );

        // Actualizar precio en vehículo si no tiene
        if (!$vehiculo['precio_por_dia']) {
            $this->vehiculoModel->actualizarPrecio($vehiculo['id'], $precio_por_dia);
        }

        return [
            'mensaje' => '🚗 Entrada registrada exitosamente para ' . $vehiculo['placa'] . ' - Precio: S/ ' . number_format($precio_por_dia, 2) . ' por día',
            'tipo' => 'success'
        ];
    }

    /**
     * Procesar salida de vehículo
     */
    private function procesarSalida($vehiculo, $precio_por_dia, $metodo_pago)
    {
        // Buscar movimiento activo
        $movimiento_activo = $this->movimientoModel->verificarActivo($vehiculo['id']);

        if (!$movimiento_activo) {
            return [
                'mensaje' => 'No hay registro de entrada activo para este vehículo.',
                'tipo' => 'error'
            ];
        }

        // Calcular precio total
        
        // Priorizar el precio pactado a la entrada si existe
        // Si se registró con recargo (Feriado), ese precio está guardado en precio_total
        $precio_final_calculo = ($movimiento_activo['precio_total'] > 0) 
                                ? $movimiento_activo['precio_total'] 
                                : $precio_por_dia;

        // Si ya pagó a la entrada, mantenemos el precio original sin recalcular por días/horas
        if ($movimiento_activo['momento_pago'] === 'Entrada') {
            $precio_total = $movimiento_activo['precio_total'];
        } else {
            // Si paga a la salida, calculamos según el tiempo transcurrido
            $precio_total = Movimiento::calcularPrecioTotal(
                $movimiento_activo['fecha_hora_entrada'],
                date('Y-m-d H:i:s'),
                $precio_final_calculo
            );
        }

        // Registrar salida
        $personal_id_salida = $_SESSION['usuario_id'] ?? null;
        
        // Si el momento de pago original fue 'Entrada', no sobreescribimos el método de pago
        // Pero si es 'Salida', usamos el método seleccionado ahora
        $metodo_pago_final = ($movimiento_activo['momento_pago'] === 'Entrada') ? null : $metodo_pago;

        $this->movimientoModel->registrarSalida(
            $movimiento_activo['id'], 
            $precio_total, 
            $personal_id_salida,
            $metodo_pago_final
        );

        return [
            'mensaje' => '🚪 Salida registrada exitosamente para ' . $vehiculo['placa'] . ' - Total a pagar: S/ ' . number_format($precio_total, 2),
            'tipo' => 'success'
        ];
    }

    /**
     * Verificar sesión activa
     */
    private function verificarSesion()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login');
            exit();
        }
    }
}
