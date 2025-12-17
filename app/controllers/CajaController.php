<?php

require_once __DIR__ . '/../models/Movimiento.php';
require_once __DIR__ . '/../models/Gasto.php';

class CajaController
{
    private $movimientoModel;
    private $gastoModel;

    public function __construct()
    {
        $this->movimientoModel = new Movimiento();
        $this->gastoModel = new Gasto();
    }

    public function index()
    {
        session_start();
        $this->verificarSesionAdmin();

        $fecha = $_POST['fecha'] ?? date('Y-m-d');
        
        // 1. Ingresos
        $metodos = $this->movimientoModel->obtenerTotalesPorMetodo($fecha);
        $ingreso_efectivo = $metodos['Efectivo'] ?? 0;
        $ingreso_yape = $metodos['Yape'] ?? 0;
        $total_ingresos = $ingreso_efectivo + $ingreso_yape;

        // 2. Gastos (Solo afectan al efectivo usualmente, pero mostramos global)
        $gastos = $this->gastoModel->obtenerPorFecha($fecha);
        $total_gastos = $this->gastoModel->obtenerTotalPorFecha($fecha);

        // 3. Top Usuarios (Recaudadores)
        $top_usuarios = $this->movimientoModel->obtenerTotalesPorUsuario($fecha);

        // 4. Balance

        // 3. Balance
        $balance_neto = $total_ingresos - $total_gastos;
        
        // Si hay POST de gasto
        $mensaje = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registrar_gasto') {
            try {
                $descripcion = $_POST['descripcion'];
                $monto = $_POST['monto'];
                $this->gastoModel->crear($descripcion, $monto, $_SESSION['usuario_id']);
                
                // Recargar página para evitar reenvío y actualizar datos
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit;
            } catch (Exception $e) {
                $mensaje = $e->getMessage();
            }
        }

        // 4. Historial (30 días)
        $historial_movimientos = $this->movimientoModel->obtenerResumenMovimientosPorDias(30);
        $historial_gastos = $this->gastoModel->obtenerResumenGastosPorDias(30);

        // Fusionar datos
        $historial = [];
        
        // Procesar movimientos
        foreach ($historial_movimientos as $mov) {
            $fecha = $mov['fecha'];
            if (!isset($historial[$fecha])) {
                $historial[$fecha] = ['fecha' => $fecha, 'efectivo' => 0, 'yape' => 0, 'gastos' => 0];
            }
            $historial[$fecha]['efectivo'] = $mov['efectivo'];
            $historial[$fecha]['yape'] = $mov['yape'];
        }

        // Procesar gastos
        foreach ($historial_gastos as $fecha => $monto) {
            if (!isset($historial[$fecha])) {
                $historial[$fecha] = ['fecha' => $fecha, 'efectivo' => 0, 'yape' => 0, 'gastos' => 0];
            }
            $historial[$fecha]['gastos'] = $monto;
        }

        // Ordenar por fecha descendente
        krsort($historial);

        require __DIR__ . '/../views/caja/index.php';
    }

    public function detalle()
    {
        session_start();
        $this->verificarSesionAdmin();

        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        
        // Reutilizamos lógica de totales
        $metodos = $this->movimientoModel->obtenerTotalesPorMetodo($fecha);
        $ingreso_efectivo = $metodos['Efectivo'] ?? 0;
        $ingreso_yape = $metodos['Yape'] ?? 0;
        $total_ingresos = $ingreso_efectivo + $ingreso_yape;
        $total_gastos = $this->gastoModel->obtenerTotalPorFecha($fecha);
        $balance = $total_ingresos - $total_gastos;

        // Obtener detalles completos
        // Usamos obtenerPorFecha del MovimientoModel que ya trae todo (Entrada y Salida info)
        // Pero necesitamos filtrar para que coincida con la lógica de CAJA (pagos del día)
        // Por simplicidad, mostraremos TODOS los movimientos que tuvieron actividad ese día (Entrada O Salida)
        // O mejor, una lista específica de "Transacciones" sería ideal, pero usaremos el reporte existente como base
        $movimientos = $this->movimientoModel->obtenerPorFecha($fecha, $fecha);
        
        // Gastos detallados
        $gastos = $this->gastoModel->obtenerPorFecha($fecha);

        require __DIR__ . '/../views/caja/detalle.php';
    }

    private function verificarSesionAdmin()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login');
            exit();
        }
        if ($_SESSION['rol'] !== 'admin') {
            http_response_code(403);
            die("<h1>Acceso Denegado</h1><p>Solo los administradores pueden ver la caja.</p>");
        }
    }
}
