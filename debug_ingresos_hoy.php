<?php
// Configurar zona horaria (Perú)
date_default_timezone_set('America/Lima');
require_once __DIR__ . '/app/models/Database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "<h1>Debug Ingresos Hoy (" . date('Y-m-d') . ")</h1>";
    
    // 1. Raw Movements for Today (Entry OR Exit)
    echo "<h2>Todos los movimientos de hoy (Entrada O Salida)</h2>";
    $sql = "SELECT m.id, v.placa, m.fecha_hora_entrada, m.fecha_hora_salida, m.momento_pago, m.precio_total, m.estado, m.metodo_pago 
            FROM movimientos m
            LEFT JOIN vehiculos v ON m.vehiculo_id = v.id
            WHERE DATE(m.fecha_hora_entrada) = CURDATE() OR DATE(m.fecha_hora_salida) = CURDATE()";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rows)) {
        echo "No hay movimientos hoy.<br>";
    } else {
        echo "<table border='1' cellspacing='0' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Placa</th><th>Entrada</th><th>Salida</th><th>Momento Pago</th><th>Precio</th><th>Estado</th><th>Metodo</th><th>Status Calculation</th></tr>";
        
        $sum_manual = 0;
        
        foreach ($rows as $r) {
            $contribuye = 0;
            $razon = "";
            
            // Logic replication
            $is_entry_payment = ($r['momento_pago'] === 'Entrada' && strpos($r['fecha_hora_entrada'], date('Y-m-d')) === 0);
            $is_exit_payment = ($r['momento_pago'] === 'Salida' && $r['estado'] === 'Completado' && $r['fecha_hora_salida'] && strpos($r['fecha_hora_salida'], date('Y-m-d')) === 0);
            
            if ($is_entry_payment) {
                $contribuye = $r['precio_total'];
                $razon = "Pago en Entrada";
            } elseif ($is_exit_payment) {
                $contribuye = $r['precio_total'];
                $razon = "Pago en Salida";
            } else {
                $razon = "NO SUMA (Entrada: " . ($r['momento_pago'] === 'Entrada' ? 'SI' : 'NO') . ", Salida: " . ($r['momento_pago'] === 'Salida' ? 'SI' : 'NO') . ")";
            }
            
            $sum_manual += $contribuye;
            
            echo "<tr>";
            echo "<td>{$r['id']}</td>";
            echo "<td>{$r['placa']}</td>";
            echo "<td>{$r['fecha_hora_entrada']}</td>";
            echo "<td>{$r['fecha_hora_salida']}</td>";
            echo "<td>{$r['momento_pago']}</td>";
            echo "<td>{$r['precio_total']}</td>";
            echo "<td>{$r['estado']}</td>";
            echo "<td>{$r['metodo_pago']}</td>";
            echo "<td style='color:" . ($contribuye > 0 ? 'green' : 'red') . "'>{$razon} (+{$contribuye})</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<h3>Suma Manual Calculada: S/ " . number_format($sum_manual, 2) . "</h3>";
    }
    
    // 2. Current Query Result
    echo "<h2>Resultado de la Query Actual del Modelo</h2>";
    $sql_hoy = "SELECT SUM(total) as total FROM (
                    SELECT precio_total as total FROM movimientos WHERE momento_pago = 'Entrada' AND DATE(fecha_hora_entrada) = CURDATE()
                    UNION ALL
                    SELECT precio_total as total FROM movimientos WHERE momento_pago = 'Salida' AND estado = 'Completado' AND DATE(fecha_hora_salida) = CURDATE()
                ) as ingresos_hoy_table";
    $stmt = $pdo->query($sql_hoy);
    $res = $stmt->fetch();
    echo "<h3>Query Devuelve: S/ " . number_format($res['total'] ?? 0, 2) . "</h3>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
