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
        session_start();
        $this->verificarSesion();

        // Obtener estadísticas
        $stats = $this->movimientoModel->obtenerEstadisticasGenerales();
        $vehiculos = $this->vehiculoModel->obtenerTodos();
        $stats['total_vehiculos'] = count($vehiculos);

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
