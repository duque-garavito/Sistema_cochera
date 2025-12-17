<?php

require_once __DIR__ . '/Database.php';

class Gasto
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function crear($descripcion, $monto, $usuario_id)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO gastos (descripcion, monto, usuario_id, fecha_hora) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$descripcion, $monto, $usuario_id]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error al registrar gasto: " . $e->getMessage());
        }
    }

    public function obtenerPorFecha($fecha)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT g.*, u.usuario as registrado_por 
                                   FROM gastos g
                                   JOIN administradores u ON g.usuario_id = u.id
                                   WHERE DATE(g.fecha_hora) = ?
                                   ORDER BY g.fecha_hora DESC");
            $stmt->execute([$fecha]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener gastos: " . $e->getMessage());
        }
    }

    public function obtenerTotalPorFecha($fecha)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM gastos WHERE DATE(fecha_hora) = ?");
            $stmt->execute([$fecha]);
            return $stmt->fetch()['total'];
        } catch (PDOException $e) {
            throw new Exception("Error al obtener total de gastos: " . $e->getMessage());
        }
    }
    public function obtenerResumenGastosPorDias($dias = 30)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT DATE(fecha_hora) as fecha, SUM(monto) as total
                                   FROM gastos 
                                   WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                                   GROUP BY DATE(fecha_hora)
                                   ORDER BY fecha DESC");
            $stmt->execute([$dias]);
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Retorna [fecha => total]
        } catch (PDOException $e) {
            throw new Exception("Error al obtener resumen de gastos: " . $e->getMessage());
        }
    }
}
