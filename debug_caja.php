<?php
require_once __DIR__ . '/app/models/Database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    
    $today = date('Y-m-d');
    echo "Fecha de hoy (PHP): $today \n\n";

    // 1. Check raw movements for today
    echo "--- Últimos 5 Movimientos ---\n";
    $stmt = $pdo->query("SELECT id, tipo_movimiento, momento_pago, metodo_pago, fecha_hora_entrada, fecha_hora_salida, precio_total, estado 
                         FROM movimientos ORDER BY id DESC LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        print_r($r);
    }

    // 2. Test the Query Logic directly
    echo "\n--- Test Query Totals ---\n";
    
    // Entrada query
    $sql1 = "SELECT metodo_pago, SUM(precio_total) as total 
             FROM movimientos 
             WHERE momento_pago = 'Entrada' AND DATE(fecha_hora_entrada) = '$today'
             GROUP BY metodo_pago";
    echo "Query Entrada: $sql1\n";
    $stmt = $pdo->query($sql1);
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    // Salida query
    $sql2 = "SELECT metodo_pago, SUM(precio_total) as total 
             FROM movimientos 
             WHERE momento_pago = 'Salida' AND estado = 'Completado' AND DATE(fecha_hora_salida) = '$today'
             GROUP BY metodo_pago";
    echo "Query Salida: $sql2\n";
    $stmt = $pdo->query($sql2);
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
