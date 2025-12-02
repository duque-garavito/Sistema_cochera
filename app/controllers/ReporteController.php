<?php

require_once __DIR__ . '/../models/Movimiento.php';

class ReporteController
{
    private $movimientoModel;

    public function __construct()
    {
        $this->movimientoModel = new Movimiento();
    }

    public function index()
    {
        session_start();
        $this->verificarSesion();

        $movimientos = [];
        $fecha_inicio = date('Y-m-d');
        $fecha_fin = date('Y-m-d');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-d');
            $fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
            $movimientos = $this->movimientoModel->obtenerPorFecha($fecha_inicio, $fecha_fin);
        }

        require __DIR__ . '/../views/reportes/index.php';
    }

    private function verificarSesion ()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login');
            exit();
        }
    }
}
