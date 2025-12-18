<?php
// Simulate Controller Environment
date_default_timezone_set('America/Lima');
require_once __DIR__ . '/app/models/Database.php';
require_once __DIR__ . '/app/models/Movimiento.php';
require_once __DIR__ . '/app/models/Gasto.php';

session_start();
$_SESSION['usuario_id'] = 1; // Fake login
$_SESSION['rol'] = 'admin';

echo "--- Debug Controller Logic ---\n";
$movimientoModel = new Movimiento();
$gastoModel = new Gasto();

$fecha = '2025-12-16'; // Force the problematic date
echo "Testing Date: $fecha\n";

// 1. Ingresos (Logic from Controller)
$metodos = $movimientoModel->obtenerTotalesPorMetodo($fecha);
print_r($metodos);

$ingreso_efectivo = $metodos['Efectivo'] ?? 0;
$ingreso_yape = $metodos['Yape'] ?? 0;
$total_ingresos = $ingreso_efectivo + $ingreso_yape;

echo "Ingreso Efectivo: $ingreso_efectivo\n";
echo "Ingreso Yape: $ingreso_yape\n";
echo "Total Ingresos: $total_ingresos\n";

// 2. Gastos
$gastos = $gastoModel->obtenerPorFecha($fecha);
$total_gastos = $gastoModel->obtenerTotalPorFecha($fecha);
echo "Total Gastos: $total_gastos\n";

// 3. Top Users
try {
    $top_usuarios = $movimientoModel->obtenerTotalesPorUsuario($fecha);
    echo "Top Usuarios:\n";
    print_r($top_usuarios);
} catch (Exception $e) {
    echo "Error Top Users: " . $e->getMessage() . "\n";
}

// 4. History
try {
    $historial_movimientos = $movimientoModel->obtenerResumenMovimientosPorDias(30);
    echo "First 2 days of history:\n";
    print_r(array_slice($historial_movimientos, 0, 2));
} catch (Exception $e) {
    echo "Error History: " . $e->getMessage() . "\n";
}
