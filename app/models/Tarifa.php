<?php

require_once __DIR__ . '/Database.php';

class Tarifa
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todas las tarifas activas
     */
    public function obtenerTodas()
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM tarifas WHERE estado = 'Activo' ORDER BY precio_base ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener tarifas: " . $e->getMessage());
        }
    }

    /**
     * Obtener tarifa por ID
     */
    public function obtenerPorId($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM tarifas WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener tarifa: " . $e->getMessage());
        }
    }

    /**
     * Crear nueva tarifa
     */
    public function crear($tipo, $precio)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO tarifas (tipo_vehiculo, precio_base) VALUES (?, ?)");
            $stmt->execute([$tipo, $precio]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error al crear tarifa: " . $e->getMessage());
        }
    }

    /**
     * Actualizar tarifa
     */
    public function actualizar($id, $tipo, $precio)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE tarifas SET tipo_vehiculo = ?, precio_base = ? WHERE id = ?");
            $stmt->execute([$tipo, $precio, $id]);
            return true;
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar tarifa: " . $e->getMessage());
        }
    }

    /**
     * Eliminar tarifa (Soft delete)
     */
    public function eliminar($id)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE tarifas SET estado = 'Inactivo' WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        } catch (PDOException $e) {
            throw new Exception("Error al eliminar tarifa: " . $e->getMessage());
        }
    }
}
