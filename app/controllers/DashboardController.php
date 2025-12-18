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
            $ingresos_raw = $this->movimientoModel->obtenerIngresosUltimosDias(30);
            
            // Rellenar días faltantes con 0 (últimos 30 días)
            $ingresos_semanales = [];
            for ($i = 29; $i >= 0; $i--) {
                $fecha = date('Y-m-d', strtotime("-$i days"));
                $ingresos_semanales[$fecha] = isset($ingresos_raw[$fecha]) ? $ingresos_raw[$fecha] : 0;
            }

            // Datos Horas Pico (5am a 10pm)
            $horas_pico_raw = $this->movimientoModel->obtenerHorasPico();
            $horas_pico = [];
            for ($i = 5; $i <= 22; $i++) {
                $horas_pico[$i] = isset($horas_pico_raw[$i]) ? $horas_pico_raw[$i] : 0;
            }

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
            $horas_pico = array_fill(0, 24, 0); // Inicializar vacio
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
