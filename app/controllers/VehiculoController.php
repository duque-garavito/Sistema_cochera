<?php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Vehiculo.php';

/**
 * VehiculoController
 * Maneja la gestión de vehículos
 */
class VehiculoController
{
    private $usuarioModel;
    private $vehiculoModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->vehiculoModel = new Vehiculo();
    }

    public function index()
    {
        session_start();
        $this->verificarSesion();

        $mensaje = '';
        $tipo_mensaje = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
            if ($_POST['accion'] === 'registrar_vehiculo') {
                $resultado = $this->registrar($_POST);
                $mensaje = $resultado['mensaje'];
                $tipo_mensaje = $resultado['tipo'];
            } elseif ($_POST['accion'] === 'alternar_bloqueo') {
                $resultado = $this->alternarBloqueo($_POST);
                $mensaje = $resultado['mensaje'];
                $tipo_mensaje = $resultado['tipo'];
            }
        }

        $vehiculos = $this->vehiculoModel->obtenerTodos();

        require __DIR__ . '/../views/vehiculos/index.php';
    }

    private function alternarBloqueo($datos)
    {
        try {
            $id = $datos['vehiculo_id'];
            $estado = $datos['nuevo_estado'];

            $this->vehiculoModel->toggleListaNegra($id, $estado);

            $accion = $estado ? 'bloqueado' : 'desbloqueado';
            return [
                'mensaje' => "Vehículo $accion exitosamente.",
                'tipo' => 'success'
            ];
        } catch (Exception $e) {
            return [
                'mensaje' => $e->getMessage(),
                'tipo' => 'error'
            ];
        }
    }

    private function registrar($datos)
    {
        try {
            $placa = strtoupper(trim($datos['placa']));
            $tipo_vehiculo = $datos['tipo_vehiculo'];
            $marca = trim($datos['marca'] ?? '');
            $modelo = trim($datos['modelo'] ?? '');
            $color = trim($datos['color'] ?? '');
            $dni = trim($datos['dni']);
            $nombre = trim($datos['nombre']);
            $apellido = trim($datos['apellido']);
            $telefono = trim($datos['telefono'] ?? '');

            // Validaciones
            if (!Vehiculo::validarPlaca($placa)) {
                throw new Exception("Formato de placa inválido");
            }

            if (!Usuario::validarDNI($dni)) {
                throw new Exception("DNI debe tener 8 dígitos");
            }

            if ($this->vehiculoModel->buscarPorPlaca($placa)) {
                throw new Exception("La placa ya está registrada");
            }

            // Buscar o crear usuario
            $usuario = $this->usuarioModel->buscarPorDNI($dni);
            if (!$usuario) {
                $usuario_id = $this->usuarioModel->crear($dni, $nombre, $apellido, $telefono, null);
            } else {
                $usuario_id = $usuario['id'];
            }

            // Registrar vehículo
            $vehiculo_id = $this->vehiculoModel->crear($placa, $tipo_vehiculo, $marca, $modelo, $color, $usuario_id);

            // Actualizar precio
            $precio_por_dia = Vehiculo::obtenerPrecioPorTipo($tipo_vehiculo);
            $this->vehiculoModel->actualizarPrecio($vehiculo_id, $precio_por_dia);

            return [
                'mensaje' => 'Vehículo registrado exitosamente - Precio: S/ ' . number_format($precio_por_dia, 2) . ' por día',
                'tipo' => 'success'
            ];
        } catch (Exception $e) {
            return [
                'mensaje' => $e->getMessage (),
                'tipo' => 'error'
            ];
        }
    }

    private function verificarSesion()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login');
            exit();
        }
    }
}
