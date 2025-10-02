<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Procesar formulario de entrada/salida
$mensaje = '';
$tipo_mensaje = '';

if ($_POST) {
    $placa = trim($_POST['placa']);
    $dni = trim($_POST['dni']);
    $tipo_movimiento = $_POST['tipo_movimiento'];
    $observaciones = trim($_POST['observaciones']);
    
    try {
        // Verificar si el vehículo existe
        $stmt = $pdo->prepare("SELECT v.*, u.nombre, u.apellido FROM vehiculos v 
                              LEFT JOIN usuarios u ON v.usuario_id = u.id 
                              WHERE v.placa = ?");
        $stmt->execute([$placa]);
        $vehiculo = $stmt->fetch();
        
        if (!$vehiculo) {
            $mensaje = "Vehículo no encontrado. Por favor registre el vehículo primero.";
            $tipo_mensaje = "error";
        } else {
            // Verificar si el DNI coincide
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE dni = ?");
            $stmt->execute([$dni]);
            $usuario = $stmt->fetch();
            
            if (!$usuario || $vehiculo['usuario_id'] != $usuario['id']) {
                $mensaje = "El DNI no coincide con el propietario registrado del vehículo.";
                $tipo_mensaje = "error";
            } else {
                // Obtener precio por día del vehículo
                $precio_por_dia = $vehiculo['precio_por_dia'] ?: obtenerPrecioPorTipo($vehiculo['tipo_vehiculo']);
                
                if ($tipo_movimiento == 'Entrada') {
                    // Verificar si ya hay un movimiento activo
                    $stmt = $pdo->prepare("SELECT * FROM movimientos WHERE vehiculo_id = ? AND estado = 'Activo'");
                    $stmt->execute([$vehiculo['id']]);
                    $movimiento_activo = $stmt->fetch();
                    
                    if ($movimiento_activo) {
                        $mensaje = "El vehículo ya tiene un registro de entrada activo.";
                        $tipo_mensaje = "error";
                    } else {
                        // Registrar entrada
                        $stmt = $pdo->prepare("INSERT INTO movimientos (vehiculo_id, usuario_id, tipo_movimiento, fecha_hora_entrada, observaciones) VALUES (?, ?, 'Entrada', NOW(), ?)");
                        $stmt->execute([$vehiculo['id'], $usuario['id'], $observaciones]);
                        
                        // Actualizar precio en la tabla vehiculos si no tiene
                        if (!$vehiculo['precio_por_dia']) {
                            $stmt = $pdo->prepare("UPDATE vehiculos SET precio_por_dia = ? WHERE id = ?");
                            $stmt->execute([$precio_por_dia, $vehiculo['id']]);
                        }
                        
                        $mensaje = "🚗 Entrada registrada exitosamente para " . $vehiculo['placa'] . " - Precio: S/ " . number_format($precio_por_dia, 2) . " por día";
                        $tipo_mensaje = "success";
                    }
                } else {
                    // Registrar salida
                    $stmt = $pdo->prepare("SELECT * FROM movimientos WHERE vehiculo_id = ? AND estado = 'Activo' ORDER BY fecha_hora_entrada DESC LIMIT 1");
                    $stmt->execute([$vehiculo['id']]);
                    $movimiento_activo = $stmt->fetch();
                    
                    if (!$movimiento_activo) {
                        $mensaje = "No hay registro de entrada activo para este vehículo.";
                        $tipo_mensaje = "error";
                    } else {
                        // Calcular precio total
                        $precio_total = calcularPrecioTotal($movimiento_activo['fecha_hora_entrada'], date('Y-m-d H:i:s'), $precio_por_dia);
                        
                        // Actualizar salida con precio
                        $stmt = $pdo->prepare("UPDATE movimientos SET fecha_hora_salida = NOW(), estado = 'Finalizado', precio_total = ? WHERE id = ?");
                        $stmt->execute([$precio_total, $movimiento_activo['id']]);
                        
                        $mensaje = "🚪 Salida registrada exitosamente para " . $vehiculo['placa'] . " - Total a pagar: S/ " . number_format($precio_total, 2);
                        $tipo_mensaje = "success";
                    }
                }
            }
        }
    } catch (Exception $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}

// Obtener vehículos activos (con entrada sin salida)
$stmt = $pdo->query("SELECT m.*, v.placa, v.tipo_vehiculo, v.precio_por_dia, u.nombre, u.apellido 
                    FROM movimientos m 
                    JOIN vehiculos v ON m.vehiculo_id = v.id 
                    JOIN usuarios u ON m.usuario_id = u.id 
                    WHERE m.estado = 'Activo' 
                    ORDER BY m.fecha_hora_entrada DESC");
$vehiculos_activos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Control de Vehículos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏢 Sistema de Control de Vehículos</h1>
            <nav>
                <a href="index.php" class="nav-link active">Registro</a>
                <a href="reportes.php" class="nav-link">Reportes</a>
                <a href="vehiculos.php" class="nav-link">Vehículos</a>
            </nav>
        </header>

        <main>
            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="grid-container">
                <!-- Formulario de Registro -->
                <section class="card">
                    <h2>📝 Registro de Entrada/Salida</h2>
                    <form method="POST" class="form">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="placa">Placa del Vehículo:</label>
                                <input type="text" id="placa" name="placa" required 
                                       placeholder="Ej: ABC123, AB-1234, 123ABC" maxlength="10">
                            </div>

                            <div class="form-group">
                                <label for="dni">DNI del Conductor:</label>
                                <input type="text" id="dni" name="dni" required 
                                       placeholder="Ej: 12345678" maxlength="8">
                            </div>
                        </div>

                        <!-- Sugerencias de autocompletado -->
                        <div id="sugerencias" class="sugerencias-container"></div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="tipo_movimiento">Tipo de Movimiento:</label>
                                <select id="tipo_movimiento" name="tipo_movimiento" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="Entrada">🚗 Entrada</option>
                                    <option value="Salida">🚪 Salida</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="precio_dia">Precio por Día:</label>
                                <input type="text" id="precio_dia" readonly 
                                       placeholder="Se selecciona automáticamente">
                            </div>
                        </div>

                        <!-- Información del Vehículo -->
                        <div id="info_vehiculo" class="info-vehiculo" style="display: none;">
                            <div class="info-header">
                                <h3>📋 Información del Vehículo</h3>
                            </div>
                            <div class="info-content">
                                <p><strong>Propietario:</strong> <span id="info_nombre">-</span></p>
                                <p><strong>Tipo:</strong> <span id="info_tipo">-</span></p>
                                <p><strong>Estado:</strong> <span id="info_estado">-</span></p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="observaciones">Observaciones:</label>
                            <textarea id="observaciones" name="observaciones" 
                                      placeholder="Comentarios adicionales (opcional)"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            📋 Registrar Movimiento
                        </button>
                    </form>
                </section>

                <!-- Vehículos Activos -->
                <section class="card">
                    <h2>🚗 Vehículos Activos (En Cochera)</h2>
                    <div class="activos-container">
                        <?php if (empty($vehiculos_activos)): ?>
                            <p class="no-data">No hay vehículos activos en la cochera</p>
                        <?php else: ?>
                            <?php foreach ($vehiculos_activos as $activo): ?>
                                <div class="activo-item">
                                    <div class="activo-header">
                                        <span class="placa"><?php echo htmlspecialchars($activo['placa']); ?></span>
                                        <span class="tipo"><?php echo htmlspecialchars($activo['tipo_vehiculo']); ?></span>
                                    </div>
                                    <div class="activo-info">
                                        <p><strong>Conductor:</strong> <?php echo htmlspecialchars($activo['nombre'] . ' ' . $activo['apellido']); ?></p>
                                        <p><strong>Entrada:</strong> <?php echo date('d/m/Y H:i', strtotime($activo['fecha_hora_entrada'])); ?></p>
                                        <p><strong>Precio/Día:</strong> S/ <?php echo number_format($activo['precio_por_dia'], 2); ?></p>
                                        <?php if ($activo['observaciones']): ?>
                                            <p><strong>Obs:</strong> <?php echo htmlspecialchars($activo['observaciones']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 Sistema de Control de Vehículos. Todos los derechos reservados.</p>
        </footer>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
