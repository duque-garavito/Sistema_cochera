<?php

require_once __DIR__ . '/../models/Vehiculo.php';
require_once __DIR__ . '/../models/Movimiento.php';

class DashboardController
{
    private $vehiculoModel;
    private $movimientoModel;

    public function __construct()
    {
        $this->vehiculoModel = new Vehiculo();
        $this->movimientoModel = new Movimiento();
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->verificarSesion();

        try {
            // Obtener estadísticas
            $stats = $this->movimientoModel->obtenerEstadisticasGenerales();
            $vehiculos = $this->vehiculoModel->obtenerTodos();
            $stats['total_vehiculos'] = count($vehiculos);

            // Datos para gráficos
            $vehiculos_por_tipo = $this->vehiculoModel->obtenerConteoPorTipo();
            $ingresos_semanales = $this->movimientoModel->obtenerIngresosUltimosDias(7);
        } catch (Exception $e) {
            // En caso de error, inicializar variables vacías para evitar errores en la vista
            $stats = [
                'total_vehiculos' => 0,
                'movimientos_activos' => 0,
                'movimientos_hoy' => 0,
                'ingresos_hoy' => 0,
                'ingresos_mes' => 0
            ];
            $vehiculos_por_tipo = [];
            $ingresos_semanales = [];
            $error = "Error al cargar datos del dashboard: " . $e->getMessage();
        }

        require __DIR__ . '/../views/dashboard/index.php';
    }

    private function verificarSesion()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login');
            exit();
        }
    }
}
