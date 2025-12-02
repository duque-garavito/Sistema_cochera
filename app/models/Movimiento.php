<?php

require_once __DIR__ . '/Database.php';

/**
 * Movimiento Model
 * Maneja todas las operaciones relacionadas con movimientos de vehículos
 */
class Movimiento
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Registrar entrada de vehículo
     */
    public function registrarEntrada($vehiculo_id, $usuario_id, $tipo_movimiento, $precio_por_dia)
    {
        try {
            // Nota: precio_por_dia está en la tabla vehiculos, no en movimientos
            $stmt = $this->pdo->prepare("INSERT INTO movimientos (vehiculo_id, usuario_id, tipo_movimiento, estado, fecha_hora_entrada) 
                                   VALUES (?, ?, ?, 'Activo', NOW())");
            $stmt->execute([$vehiculo_id, $usuario_id, $tipo_movimiento]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error al registrar movimiento: " . $e->getMessage());
        }
    }

    /**
     * Registrar salida de vehículo
     */
    public function registrarSalida($movimiento_id, $precio_total)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE movimientos 
                                   SET fecha_hora_salida = NOW(), precio_total = ?, estado = 'Completado' 
                                   WHERE id = ?");
            $stmt->execute([$precio_total, $movimiento_id]);
        } catch (PDOException $e) {
            throw new Exception("Error al registrar salida: " . $e->getMessage());
        }
    }

    /**
     * Obtener movimientos activos
     */
    public function obtenerActivos()
    {
        try {
            $stmt = $this->pdo->query("SELECT m.*, v.placa, v.tipo_vehiculo, u.nombre, u.apellido 
                                FROM movimientos m 
                                JOIN vehiculos v ON m.vehiculo_id = v.id 
                                JOIN usuarios u ON m.usuario_id = u.id 
                                WHERE m.estado = 'Activo' 
                                ORDER BY m.fecha_hora_entrada DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener movimientos activos: " . $e->getMessage());
        }
    }

    /**
     * Obtener movimientos por rango de fechas
     */
    public function obtenerPorFecha($fecha_inicio, $fecha_fin)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT m.*, v.placa, v.tipo_vehiculo, u.nombre, u.apellido 
                                   FROM movimientos m 
                                   JOIN vehiculos v ON m.vehiculo_id = v.id 
                                   JOIN usuarios u ON m.usuario_id = u.id 
                                   WHERE DATE(m.fecha_hora_entrada) BETWEEN ? AND ? 
                                   ORDER BY m.fecha_hora_entrada DESC");
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener movimientos: " . $e->getMessage());
        }
    }

    /**
     * Verificar si un vehículo tiene movimiento activo
     */
    public function verificarActivo($vehiculo_id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM movimientos WHERE vehiculo_id = ? AND estado = 'Activo'");
            $stmt->execute([$vehiculo_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error al verificar movimiento activo: " . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas del día
     */
    public function obtenerEstadisticasDia($fecha = null)
    {
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }

        try {
            $stmt = $this->pdo->prepare("SELECT 
                                    COUNT(*) as total_movimientos,
                                    SUM(CASE WHEN tipo_movimiento = 'Entrada' THEN 1 ELSE 0 END) as entradas,
                                    SUM(CASE WHEN tipo_movimiento = 'Salida' THEN 1 ELSE 0 END) as salidas
                                  FROM movimientos 
                                  WHERE DATE(fecha_hora_entrada) = ?");
            $stmt->execute([$fecha]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener estadísticas: " . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas generales
     */
    public function obtenerEstadisticasGenerales()
    {
        try {
            $stats = [];

            // Movimientos activos
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM movimientos WHERE estado = 'Activo'");
            $stats['movimientos_activos'] = $stmt->fetch()['total'];

            // Total de movimientos hoy
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM movimientos WHERE DATE(fecha_hora_entrada) = CURDATE()");
            $stats['movimientos_hoy'] = $stmt->fetch()['total'];

            // Ingresos del día
            $stmt = $this->pdo->query("SELECT COALESCE(SUM(precio_total), 0) as total FROM movimientos WHERE DATE(fecha_hora_salida) = CURDATE()");
            $stats['ingresos_hoy'] = $stmt->fetch()['total'];

            // Ingresos del mes
            $stmt = $this->pdo->query("SELECT COALESCE(SUM(precio_total), 0) as total FROM movimientos WHERE MONTH(fecha_hora_salida) = MONTH(CURDATE()) AND YEAR(fecha_hora_salida) = YEAR(CURDATE())");
            $stats['ingresos_mes'] = $stmt->fetch()['total'];

            return $stats;
        } catch (PDOException $e) {
            throw new Exception("Error al obtener estadísticas generales: " . $e->getMessage());
        }
    }

    /**
     * Calcular precio total basado en días
     */
    public static function calcularPrecioTotal($fecha_inicio, $fecha_fin, $precio_por_dia)
    {
        if (!$fecha_inicio) return 0;

        $inicio = new DateTime($fecha_inicio);
        $fin = $fecha_fin ? new DateTime($fecha_fin) : new DateTime();

        // Si es el mismo día, cobrar 1 día
        if ($inicio->format('Y-m-d') === $fin->format('Y-m-d')) {
            return $precio_por_dia;
        }

        // Calcular días completos
        $diferencia = $fin->diff($inicio);
        $dias_completos = $diferencia->days;

        if ($diferencia->days >= 1) {
            $dias_a_cobrar = $diferencia->days + 1;
        } else {
            $dias_a_cobrar = 2;
        }

        return $dias_a_cobrar * $precio_por_dia;
    }

    /**
     * Formatear fecha y hora
     */
    public static function formatearFechaHora($fecha)
    {
        if (!$fecha) return '-';
        return date('d/m/Y H:i:s', strtotime($fecha));
    }

    /**
     * Calcular tiempo transcurrido
     */
    public static function calcularTiempoTranscurrido($fecha_inicio, $fecha_fin = null)
    {
        if (!$fecha_inicio) return '-';

        $inicio = new DateTime($fecha_inicio);
        $fin = $fecha_fin ? new DateTime($fecha_fin) : new DateTime();

        $intervalo = $inicio->diff($fin);

        $tiempo = '';
        if ($intervalo->h > 0) {
            $tiempo .= $intervalo->h . 'h ';
        }
        if ($intervalo->i > 0) {
            $tiempo .= $intervalo->i . 'm ';
        }
        if ($intervalo->s > 0) {
            $tiempo .= $intervalo->s . 's';
        }

        return trim($tiempo) ?: '0s';
    }
}
