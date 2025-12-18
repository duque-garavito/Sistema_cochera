<?php
// Configurar zona horaria (Perú)
date_default_timezone_set('America/Lima');
require_once __DIR__ . '/app/models/Database.php';

try {
    $pdo = Database::getInstance()->getConnection();

    echo "PHP Time: " . date('Y-m-d H:i:s') . "\n";
    
    $stmt = $pdo->query("SELECT NOW() as db_time, CURDATE() as db_date, @@global.time_zone as global_tz, @@session.time_zone as session_tz");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    print_r($row);
    
    echo "\nComparison:\n";
    if (date('Y-m-d') !== $row['db_date']) {
        echo "MISMATCH DETECTED! PHP says " . date('Y-m-d') . " but DB says " . $row['db_date'] . "\n";
    } else {
        echo "Dates match.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
