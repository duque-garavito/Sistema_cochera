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
    public function registrarEntrada($vehiculo_id, $usuario_id, $tipo_movimiento, $precio_por_dia, $personal_id, $metodo_pago, $momento_pago)
    {
        try {
            // Si paga al entrar, precio_total es el pago.
            // Si paga al salir, precio_total es el monto pactado (pendiente)
            // Esto permite mostrar el precio con recargo en la lista de activos.
            // La lógica de cálculo de ingresos ignora 'Salida' si no está 'Completado', así que es seguro.
            $precio_total = $precio_por_dia;

            $stmt = $this->pdo->prepare("INSERT INTO movimientos (vehiculo_id, usuario_id, tipo_movimiento, estado, fecha_hora_entrada, personal_id, metodo_pago, momento_pago, precio_total) 
                                   VALUES (?, ?, ?, 'Activo', NOW(), ?, ?, ?, ?)");
            $stmt->execute([$vehiculo_id, $usuario_id, $tipo_movimiento, $personal_id, $metodo_pago, $momento_pago, $precio_total]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error al registrar movimiento: " . $e->getMessage());
        }
    }

    /**
     * Registrar salida de vehículo
     */
    public function registrarSalida($movimiento_id, $precio_total, $personal_id_salida, $metodo_pago = null)
    {
        try {
            // Si metodo_pago es null, no lo actualizamos (asumimos que se pagó al inicio o que ya tiene valor)
            // Pero si se paga a la salida, debemos actualizarlo.
            // Para simplificar: actualizamos el metodo de pago si se nos pasa.
            
            $sql = "UPDATE movimientos 
                    SET fecha_hora_salida = NOW(), precio_total = ?, estado = 'Completado', personal_id_salida = ?";
            
            $params = [$precio_total, $personal_id_salida];

            if ($metodo_pago) {
                $sql .= ", metodo_pago = ?, momento_pago = 'Salida'";
                $params[] = $metodo_pago;
            }

            $sql .= " WHERE id = ?";
            $params[] = $movimiento_id;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
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
            $stmt = $this->pdo->query("SELECT m.*, v.placa, v.tipo_vehiculo, v.precio_por_dia, u.nombre, u.apellido,
                                        p.usuario as personal_nombre,
                                        p_sal.usuario as personal_salida_nombre
                                FROM movimientos m 
                                JOIN vehiculos v ON m.vehiculo_id = v.id 
                                JOIN usuarios u ON m.usuario_id = u.id 
                                LEFT JOIN administradores p ON m.personal_id = p.id
                                LEFT JOIN administradores p_sal ON m.personal_id_salida = p_sal.id
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
            $stmt = $this->pdo->prepare("SELECT m.*, v.placa, v.tipo_vehiculo, u.nombre, u.apellido,
                                        p.usuario as personal_entrada, p_sal.usuario as personal_salida
                                   FROM movimientos m 
                                   JOIN vehiculos v ON m.vehiculo_id = v.id 
                                   JOIN usuarios u ON m.usuario_id = u.id 
                                   LEFT JOIN administradores p ON m.personal_id = p.id
                                   LEFT JOIN administradores p_sal ON m.personal_id_salida = p_sal.id
                                   WHERE DATE(m.fecha_hora_entrada) BETWEEN ? AND ? 
                                   ORDER BY m.fecha_hora_entrada DESC");
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener movimientos: " . $e->getMessage());
        }
    }

    public function obtenerTotalesPorMetodo($fecha)
    {
        try {
            // Sumamos:
            // 1. Pagos hechos a la ENTRADA (momento_pago = 'Entrada') cuya fecha_hora_entrada sea HOY
            // 2. Pagos hechos a la SALIDA (momento_pago = 'Salida') cuya fecha_hora_salida sea HOY y estén Completados

            $sql = "SELECT metodo_pago, SUM(total) as total FROM (
                        SELECT metodo_pago, precio_total as total 
                        FROM movimientos 
                        WHERE momento_pago = 'Entrada' AND DATE(fecha_hora_entrada) = ?
                        
                        UNION ALL
                        
                        SELECT metodo_pago, precio_total as total 
                        FROM movimientos 
                        WHERE momento_pago = 'Salida' AND fecha_hora_salida IS NOT NULL AND DATE(fecha_hora_salida) = ?
                    ) as unidos 
                    GROUP BY metodo_pago";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$fecha, $fecha]);
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener totales: " . $e->getMessage());
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

            // Ingresos del día: Sumar Pagos en Entrada (hoy) + Pagos en Salida (hoy)
            $sql_hoy = "SELECT SUM(total) as total FROM (
                            SELECT precio_total as total FROM movimientos WHERE momento_pago = 'Entrada' AND DATE(fecha_hora_entrada) = CURDATE()
                            UNION ALL
                            SELECT precio_total as total FROM movimientos WHERE momento_pago = 'Salida' AND fecha_hora_salida IS NOT NULL AND DATE(fecha_hora_salida) = CURDATE()
                        ) as ingresos_hoy_table";
            $stmt = $this->pdo->query($sql_hoy);
            $stats['ingresos_hoy'] = $stmt->fetch()['total'] ?? 0;

            // Ingresos del mes: Sumar Pagos en Entrada (este mes) + Pagos en Salida (este mes)
            $sql_mes = "SELECT SUM(total) as total FROM (
                            SELECT precio_total as total FROM movimientos WHERE momento_pago = 'Entrada' AND MONTH(fecha_hora_entrada) = MONTH(CURDATE()) AND YEAR(fecha_hora_entrada) = YEAR(CURDATE())
                            UNION ALL
                            SELECT precio_total as total FROM movimientos WHERE momento_pago = 'Salida' AND fecha_hora_salida IS NOT NULL AND MONTH(fecha_hora_salida) = MONTH(CURDATE()) AND YEAR(fecha_hora_salida) = YEAR(CURDATE())
                        ) as ingresos_mes_table";
            $stmt = $this->pdo->query($sql_mes);
            $stats['ingresos_mes'] = $stmt->fetch()['total'] ?? 0;

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
    /**
     * Obtener ingresos de los últimos días
     */
    public function obtenerIngresosUltimosDias($dias = 7)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT fecha, SUM(total) as total FROM (
                                       SELECT DATE(fecha_hora_entrada) as fecha, precio_total as total 
                                       FROM movimientos 
                                       WHERE momento_pago = 'Entrada' AND fecha_hora_entrada >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                                       
                                       UNION ALL
                                       
                                       SELECT DATE(fecha_hora_salida) as fecha, precio_total as total 
                                       FROM movimientos 
                                       WHERE momento_pago = 'Salida' AND fecha_hora_salida IS NOT NULL AND fecha_hora_salida >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                                   ) as historico
                                   GROUP BY fecha
                                   ORDER BY fecha ASC");
            $stmt->execute([$dias, $dias]);
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener ingresos: " . $e->getMessage());
        }
    }
    /**
     * Obtener resumen de movimientos por días (para historial de caja)
     */
    public function obtenerResumenMovimientosPorDias($dias = 30)
    {
        try {
            $fecha_fin = date('Y-m-d'); // Usar fecha actual de PHP (zona horaria configurada)
            
            // Consulta compleja para sumar entradas y salidas del mismo día por método de pago
            $sql = "SELECT 
                        fecha,
                        SUM(CASE WHEN metodo_pago = 'Efectivo' THEN total ELSE 0 END) as efectivo,
                        SUM(CASE WHEN metodo_pago = 'Yape' THEN total ELSE 0 END) as yape
            FROM (
                SELECT DATE(fecha_hora_entrada) as fecha, precio_total as total, metodo_pago 
                FROM movimientos WHERE momento_pago = 'Entrada' AND fecha_hora_entrada >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                UNION ALL
                SELECT DATE(fecha_hora_salida) as fecha, precio_total as total, metodo_pago 
                FROM movimientos WHERE momento_pago = 'Salida' AND fecha_hora_salida IS NOT NULL AND fecha_hora_salida >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            ) as combinado
            GROUP BY fecha
            ORDER BY fecha DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$dias, $dias]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener resumen por días: " . $e->getMessage());
        }
    }

    /**
     * Obtener horas pico (actividad por hora)
     */
    public function obtenerHorasPico()
    {
        try {
            // Contamos entradas solamente por hora
            $sql = "SELECT hora, COUNT(*) as cantidad FROM (
                        SELECT HOUR(fecha_hora_entrada) as hora FROM movimientos
                    ) as actividades
                    GROUP BY hora
                    ORDER BY hora ASC";
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Retorna [hora => cantidad]
        } catch (PDOException $e) {
            return []; // Retorna vacio en caso de error para no romper dashboard
        }
    }



    /**
     * Obtener totales recaudados por usuario en una fecha específica
     */
    public function obtenerTotalesPorUsuario($fecha)
    {
        try {
            // Unir pagos recibidos en Entrada y en Salida
            $sql = "SELECT nombre_usuario, SUM(total) as total_recaudado FROM (
                        -- Recaudado en ENTRADA
                        SELECT p.usuario as nombre_usuario, m.precio_total as total
                        FROM movimientos m
                        JOIN administradores p ON m.personal_id = p.id
                        WHERE m.momento_pago = 'Entrada' AND DATE(m.fecha_hora_entrada) = ?

                        UNION ALL

                        -- Recaudado en SALIDA
                        SELECT p.usuario as nombre_usuario, m.precio_total as total
                        FROM movimientos m
                        JOIN administradores p ON m.personal_id_salida = p.id
                        WHERE m.momento_pago = 'Salida' AND m.fecha_hora_salida IS NOT NULL AND DATE(m.fecha_hora_salida) = ?
                    ) as ingresos
                    GROUP BY nombre_usuario
                    ORDER BY total_recaudado DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$fecha, $fecha]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener totales por usuario: " . $e->getMessage());
        }
    }
}
