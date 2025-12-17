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
        
        // Debug session
        file_put_contents('C:/xampp/htdocs/Sistema_cochera/debug_session.txt', 
            date('[Y-m-d H:i:s] ') . "ReporteController: Session ID: " . session_id() . " | User ID: " . ($_SESSION['usuario_id'] ?? 'NULL') . "\n", 
            FILE_APPEND
        );

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

    private function verificarSesion()
    {
        if (!isset($_SESSION['usuario_id'])) {
            // Detectar base_url para redirección correcta
            $host = $_SERVER['HTTP_HOST'];
            $base_url = (preg_match('/^localhost:\d+$/', $host)) ? '' : '/Sistema_cochera';
            
            header('Location: ' . $base_url . '/public/index.php/login');
            exit();
        }
    }
}
