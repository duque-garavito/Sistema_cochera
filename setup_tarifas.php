<?php
require_once __DIR__ . '/app/models/Database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Crear tabla tarifas
    $sql = "CREATE TABLE IF NOT EXISTS tarifas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo_vehiculo VARCHAR(50) NOT NULL UNIQUE,
        precio_base DECIMAL(10, 2) NOT NULL,
        estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "Tabla 'tarifas' creada o ya existe.\n";
    
    // Datos iniciales (según Vehiculo::obtenerPrecioPorTipo)
    $precios_iniciales = [
        ['Auto', 10.00],
        ['Moto', 4.00],
        ['Camioneta', 12.00],
        ['Otro', 8.00]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO tarifas (tipo_vehiculo, precio_base) VALUES (?, ?) ON DUPLICATE KEY UPDATE precio_base = VALUES(precio_base)");
    
    foreach ($precios_iniciales as $p) {
        $stmt->execute($p);
        echo "Tarifa insertada/actualizada: {$p[0]} - S/ {$p[1]}\n";
    }
    
    echo "Migración de tarifas completada.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
