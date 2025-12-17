<?php
require_once __DIR__ . '/app/models/Database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    echo "Conexión exitosa. Iniciando actualización... \n";

    // 1. Agregar columna 'en_lista_negra' a la tabla 'vehiculos' si no existe
    try {
        $pdo->query("SELECT en_lista_negra FROM vehiculos LIMIT 1");
        echo "Columna 'en_lista_negra' ya existe en 'vehiculos'. \n";
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE vehiculos ADD COLUMN en_lista_negra TINYINT(1) DEFAULT 0");
        echo "Columna 'en_lista_negra' agregada a 'vehiculos'. \n";
    }

    // 2. Agregar columna 'personal_id' a la tabla 'movimientos' si no existe
    // Nota: Usamos 'personal_id' para distinguir del 'usuario_id' (que parece ser el dueño/conductor en el sistema actual)
    // OJO: En el código actual de MovimientoController:
    // $this->movimientoModel->registrarEntrada($vehiculo['id'], $usuario['id'], ...
    // El segundo parámetro $usuario['id'] es el DUEÑO/CONDUCTOR.
    // Necesitamos un NUEVO campo para quien registra (el admin/personal de turno).
    
    try {
        $pdo->query("SELECT personal_id FROM movimientos LIMIT 1");
        echo "Columna 'personal_id' ya existe en 'movimientos'. \n";
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE movimientos ADD COLUMN personal_id INT NULL");
        // Opcional: Agregar FK si la tabla usuarios es la que contiene al personal
        // $pdo->exec("ALTER TABLE movimientos ADD CONSTRAINT fk_movimientos_personal FOREIGN KEY (personal_id) REFERENCES usuarios(id)");
        echo "Columna 'personal_id' agregada a 'movimientos'. \n";
    }

    echo "Actualización de base de datos completada correctamente.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
