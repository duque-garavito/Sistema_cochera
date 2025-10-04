<?php
require_once 'config/database.php';

/**
 * Función para registrar un nuevo usuario
 */
function registrarUsuario($dni, $nombre, $apellido, $telefono = null, $email = null) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (dni, nombre, apellido, telefono, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$dni, $nombre, $apellido, $telefono, $email]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        throw new Exception("Error al registrar usuario: " . $e->getMessage());
    }
}

/**
 * Función para registrar un nuevo vehículo
 */
function registrarVehiculo($placa, $tipo_vehiculo, $marca = null, $modelo = null, $color = null, $usuario_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO vehiculos (placa, tipo_vehiculo, marca, modelo, color, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$placa, $tipo_vehiculo, $marca, $modelo, $color, $usuario_id]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        throw new Exception("Error al registrar vehículo: " . $e->getMessage());
    }
}

/**
 * Función para buscar usuario por DNI
 */
function buscarUsuarioPorDNI($dni) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE dni = ?");
        $stmt->execute([$dni]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        throw new Exception("Error al buscar usuario: " . $e->getMessage());
    }
}

/**
 * Función para buscar vehículo por placa
 */
function buscarVehiculoPorPlaca($placa) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT v.*, u.nombre, u.apellido FROM vehiculos v 
                              LEFT JOIN usuarios u ON v.usuario_id = u.id 
                              WHERE v.placa = ?");
        $stmt->execute([$placa]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        throw new Exception("Error al buscar vehículo: " . $e->getMessage());
    }
}

/**
 * Función para obtener todos los vehículos registrados
 */
function obtenerTodosLosVehiculos() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT v.*, u.nombre, u.apellido, u.dni 
                            FROM vehiculos v 
                            LEFT JOIN usuarios u ON v.usuario_id = u.id 
                            ORDER BY v.placa");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        throw new Exception("Error al obtener vehículos: " . $e->getMessage());
    }
}

/**
 * Función para obtener movimientos por rango de fechas
 */
function obtenerMovimientosPorFecha($fecha_inicio, $fecha_fin) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT m.*, v.placa, v.tipo_vehiculo, v.precio_por_dia, u.nombre, u.apellido 
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
 * Función para obtener estadísticas del día
 */
function obtenerEstadisticasDia($fecha = null) {
    global $pdo;
    if (!$fecha) {
        $fecha = date('Y-m-d');
    }
    
    try {
        $stmt = $pdo->prepare("SELECT 
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
 * Función para validar formato de placa
 */
function validarPlaca($placa) {
    // Remover espacios y convertir a mayúsculas
    $placa = strtoupper(str_replace(' ', '', $placa));
    
    // Patrones más flexibles para diferentes formatos de placas:
    // - ABC123 (3 letras + 3 números)
    // - ABC-123 (3 letras + guión + 3 números)
    // - AB1234 (2 letras + 4 números)
    // - A12345 (1 letra + 5 números)
    // - 123ABC (3 números + 3 letras)
    // - ABC12D (letras y números mixtos)
    $patrones = [
        '/^[A-Z]{3}[-]?[0-9]{3}$/',     // ABC123 o ABC-123
        '/^[A-Z]{2}[-]?[0-9]{4}$/',     // AB1234 o AB-1234
        '/^[A-Z][-]?[0-9]{5}$/',        // A12345 o A-12345
        '/^[0-9]{3}[-]?[A-Z]{3}$/',     // 123ABC o 123-ABC
        '/^[A-Z0-9]{6}$/',              // Cualquier combinación de 6 caracteres
        '/^[A-Z0-9]{7}$/',              // Cualquier combinación de 7 caracteres
        '/^[A-Z0-9]{8}$/'               // Cualquier combinación de 8 caracteres
    ];
    
    foreach ($patrones as $patron) {
        if (preg_match($patron, $placa)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Función para validar formato de DNI
 */
function validarDNI($dni) {
    // DNI peruano: 8 dígitos
    return preg_match('/^[0-9]{8}$/', $dni);
}

/**
 * Función para formatear fecha y hora
 */
function formatearFechaHora($fecha) {
    if (!$fecha) return '-';
    return date('d/m/Y H:i:s', strtotime($fecha));
}

/**
 * Función para calcular tiempo transcurrido
 */
function calcularTiempoTranscurrido($fecha_inicio, $fecha_fin = null) {
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
 * Función para obtener precio por tipo de vehículo (POR DÍA)
 */
function obtenerPrecioPorTipo($tipo_vehiculo) {
    $precios = [
        'Auto' => 10.00,
        'Moto' => 4.00,
        'Camioneta' => 12.00,
        'Otro' => 8.00
    ];
    
    return $precios[$tipo_vehiculo] ?? 8.00;
}

/**
 * Función para calcular precio total basado en días (tarifa diaria)
 */
function calcularPrecioTotal($fecha_inicio, $fecha_fin, $precio_por_dia) {
    if (!$fecha_inicio) return 0;
    
    $inicio = new DateTime($fecha_inicio);
    $fin = $fecha_fin ? new DateTime($fecha_fin) : new DateTime();
    
    // Calcular diferencia en días
    $diferencia = $fin->diff($inicio);
    
    // Si es el mismo día, cobrar 1 día
    if ($inicio->format('Y-m-d') === $fin->format('Y-m-d')) {
        return $precio_por_dia;
    }
    
    // Calcular días completos + 1 (porque se cobra por día completo)
    $dias_completos = $diferencia->days;
    
    // Si pasó al menos un día completo, cobrar días completos + 1
    // Si no pasó un día completo pero cambió de día, cobrar 2 días
    if ($diferencia->days >= 1) {
        $dias_a_cobrar = $diferencia->days + 1;
    } else {
        // Si cambió de día pero no pasó 24 horas completas, cobrar 2 días
        $dias_a_cobrar = 2;
    }
    
    return $dias_a_cobrar * $precio_por_dia;
}

/**
 * Función para buscar vehículo por placa o DNI
 */
function buscarVehiculoPorPlacaODNI($termino) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT v.*, u.nombre, u.apellido, u.dni 
                              FROM vehiculos v 
                              LEFT JOIN usuarios u ON v.usuario_id = u.id 
                              WHERE v.placa LIKE ? OR u.dni LIKE ?");
        $termino_busqueda = '%' . $termino . '%';
        $stmt->execute([$termino_busqueda, $termino_busqueda]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        throw new Exception("Error al buscar vehículo: " . $e->getMessage());
    }
}

/**
 * Función para verificar si un vehículo tiene movimiento activo
 */
function verificarMovimientoActivo($vehiculo_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM movimientos WHERE vehiculo_id = ? AND estado = 'Activo'");
        $stmt->execute([$vehiculo_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        throw new Exception("Error al verificar movimiento activo: " . $e->getMessage());
    }
}

/**
 * Función para obtener sugerencias de búsqueda
 */
function obtenerSugerencias($termino) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT DISTINCT v.placa, u.dni, u.nombre, u.apellido, v.tipo_vehiculo
                              FROM vehiculos v 
                              LEFT JOIN usuarios u ON v.usuario_id = u.id 
                              WHERE v.placa LIKE ? OR u.dni LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ?
                              LIMIT 10");
        $termino_busqueda = '%' . $termino . '%';
        $stmt->execute([$termino_busqueda, $termino_busqueda, $termino_busqueda, $termino_busqueda]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        throw new Exception("Error al obtener sugerencias: " . $e->getMessage());
    }
}

/**
 * Función para verificar si el usuario está logueado
 */
function verificarLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Función para obtener estadísticas de días más ocupados
 */
function obtenerDiasMasOcupados($limite = 7) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT DATE(fecha_hora_entrada) as fecha, COUNT(*) as total_movimientos
                              FROM movimientos 
                              WHERE fecha_hora_entrada >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                              GROUP BY DATE(fecha_hora_entrada)
                              ORDER BY total_movimientos DESC
                              LIMIT ?");
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        throw new Exception("Error al obtener estadísticas de días: " . $e->getMessage());
    }
}

/**
 * Función para obtener estadísticas de horas pico
 */
function obtenerHorasPico($tipo = 'entrada') {
    global $pdo;
    try {
        $campo = $tipo == 'entrada' ? 'fecha_hora_entrada' : 'fecha_hora_salida';
        $stmt = $pdo->prepare("SELECT HOUR({$campo}) as hora, COUNT(*) as total
                              FROM movimientos 
                              WHERE {$campo} IS NOT NULL
                              GROUP BY HOUR({$campo})
                              ORDER BY hora");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        throw new Exception("Error al obtener estadísticas de horas: " . $e->getMessage());
    }
}

/**
 * Función para obtener estadísticas de tipos de vehículos
 */
function obtenerEstadisticasTiposVehiculos() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT v.tipo_vehiculo, COUNT(*) as total, 
                            SUM(CASE WHEN m.estado = 'Activo' THEN 1 ELSE 0 END) as activos,
                            AVG(v.precio_por_dia) as precio_promedio
                            FROM vehiculos v 
                            LEFT JOIN movimientos m ON v.id = m.vehiculo_id
                            GROUP BY v.tipo_vehiculo
                            ORDER BY total DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        throw new Exception("Error al obtener estadísticas de tipos: " . $e->getMessage());
    }
}

/**
 * Función para obtener ingresos por día
 */
function obtenerIngresosPorDia($dias = 7) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT DATE(fecha_hora_salida) as fecha, 
                              SUM(precio_total) as total_ingresos,
                              COUNT(*) as total_salidas
                              FROM movimientos 
                              WHERE fecha_hora_salida >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                              AND precio_total > 0
                              GROUP BY DATE(fecha_hora_salida)
                              ORDER BY fecha DESC");
        $stmt->execute([$dias]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        throw new Exception("Error al obtener ingresos: " . $e->getMessage());
    }
}

/**
 * Función para obtener estadísticas generales del dashboard
 */
function obtenerEstadisticasGenerales() {
    global $pdo;
    try {
        $stats = [];
        
        // Total de vehículos registrados
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM vehiculos");
        $stats['total_vehiculos'] = $stmt->fetch()['total'];
        
        // Vehículos activos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM movimientos WHERE estado = 'Activo'");
        $stats['vehiculos_activos'] = $stmt->fetch()['total'];
        
        // Total de movimientos hoy
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM movimientos WHERE DATE(fecha_hora_entrada) = CURDATE()");
        $stats['movimientos_hoy'] = $stmt->fetch()['total'];
        
        // Ingresos del día
        $stmt = $pdo->query("SELECT COALESCE(SUM(precio_total), 0) as total FROM movimientos WHERE DATE(fecha_hora_salida) = CURDATE()");
        $stats['ingresos_hoy'] = $stmt->fetch()['total'];
        
        // Ingresos del mes
        $stmt = $pdo->query("SELECT COALESCE(SUM(precio_total), 0) as total FROM movimientos WHERE MONTH(fecha_hora_salida) = MONTH(CURDATE()) AND YEAR(fecha_hora_salida) = YEAR(CURDATE())");
        $stats['ingresos_mes'] = $stmt->fetch()['total'];
        
        return $stats;
    } catch (PDOException $e) {
        throw new Exception("Error al obtener estadísticas generales: " . $e->getMessage());
    }
}
?>
