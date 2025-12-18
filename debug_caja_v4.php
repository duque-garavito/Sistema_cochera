<?php
// Configurar zona horaria (Perú)
date_default_timezone_set('America/Lima');
require_once __DIR__ . '/app/models/Database.php';

try {
    $pdo = Database::getInstance()->getConnection();

    echo "--- MOVIMIENTOS 16/12 and 17/12 ---\n";
    $sql = "SELECT id, fecha_hora_entrada, fecha_hora_salida, metodo_pago, momento_pago, precio_total, personal_id 
            FROM movimientos 
            WHERE DATE(fecha_hora_entrada) >= '2025-12-16' 
               OR DATE(fecha_hora_salida) >= '2025-12-16'
            ORDER BY id ASC";
    
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $r) {
        print_r($r);
    }
    
    echo "\n--- GASTOS 16/12 and 17/12 ---\n";
    $sql = "SELECT * FROM gastos WHERE DATE(fecha_hora) >= '2025-12-16'";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $r) {
        print_r($r);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
