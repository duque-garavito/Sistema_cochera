<?php
// Configurar zona horaria (Perú) - SAME AS PUBLIC/INDEX.PHP
date_default_timezone_set('America/Lima');

require_once __DIR__ . '/app/models/Database.php';
require_once __DIR__ . '/app/models/Movimiento.php';

echo "--- Debug Caja V2 ---\n";
echo "PHP Timezone: " . date_default_timezone_get() . "\n";
echo "PHP Date (Y-m-d H:i:s): " . date('Y-m-d H:i:s') . "\n";

try {
    $mov = new Movimiento();
    $pdo = Database::getInstance()->getConnection();

    // 1. Check Raw Data Count
    echo "\n[RAW DATA CHECK]\n";
    $stm = $pdo->query("SELECT DATE(fecha_hora_entrada) as fecha, COUNT(*) as count FROM movimientos GROUP BY DATE(fecha_hora_entrada)");
    foreach($stm->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo "Fecha Entrada: {$row['fecha']} - Count: {$row['count']}\n";
    }

    // 2. Check Specific Dates
    $fechas_to_check = ['2025-12-16', '2025-12-17'];

    foreach ($fechas_to_check as $f) {
        echo "\n-------------------------------------------------\n";
        echo "Checking Date: $f\n";
        
        // Manual Query Logic
        $sql = "SELECT medico, SUM(total) as total FROM (
                    SELECT metodo_pago as medico, precio_total as total 
                    FROM movimientos 
                    WHERE momento_pago = 'Entrada' AND DATE(fecha_hora_entrada) = '$f'
                    UNION ALL
                    SELECT metodo_pago as medico, precio_total as total 
                    FROM movimientos 
                    WHERE momento_pago = 'Salida' AND estado = 'Completado' AND DATE(fecha_hora_salida) = '$f'
                ) as u GROUP BY medico";
        
        $stmt = $pdo->query($sql);
        $res_manual = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Manual Query Result:\n";
        print_r($res_manual);

        // Model Method
        echo "Model obtainTotalesPorMetodo('$f'):\n";
        try {
            $res_model = $mov->obtenerTotalesPorMetodo($f);
            print_r($res_model);
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    }

} catch (Exception $e) {
    echo "General Error: " . $e->getMessage();
}
