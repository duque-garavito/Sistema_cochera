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
            $placa = trim($datos['placa']);
            $dni = trim($datos['dni']);
            $tipo_movimiento = $datos['tipo_movimiento'];
            $observaciones = trim($datos['observaciones'] ?? '');

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

            // Obtener precio por día
            $precio_por_dia = $vehiculo['precio_por_dia'] ?: Vehiculo::obtenerPrecioPorTipo($vehiculo['tipo_vehiculo']);

            if ($tipo_movimiento === 'Entrada') {
                return $this->procesarEntrada($vehiculo, $usuario, $precio_por_dia, $observaciones);
            } else {
                return $this->procesarSalida($vehiculo, $precio_por_dia);
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
    private function procesarEntrada($vehiculo, $usuario, $precio_por_dia, $observaciones)
    {
        // Verificar si ya hay un movimiento activo
        $movimiento_activo = $this->movimientoModel->verificarActivo($vehiculo['id']);

        if ($movimiento_activo) {
            return [
                'mensaje' => 'El vehículo ya tiene un registro de entrada activo.',
                'tipo' => 'error'
            ];
        }

        // Registrar entrada
        $this->movimientoModel->registrarEntrada(
            $vehiculo['id'],
            $usuario['id'],
            'Entrada',
            $precio_por_dia
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
    private function procesarSalida($vehiculo, $precio_por_dia)
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
        $precio_total = Movimiento::calcularPrecioTotal(
            $movimiento_activo['fecha_hora_entrada'],
            date('Y-m-d H:i:s'),
            $precio_por_dia
        );

        // Registrar salida
        $this->movimientoModel->registrarSalida($movimiento_activo['id'], $precio_total);

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
