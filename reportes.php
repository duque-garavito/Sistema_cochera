<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$mensaje = '';
$tipo_mensaje = '';
$movimientos = [];
$estadisticas = [];

// Procesar filtros
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
$tipo_filtro = isset($_GET['tipo_filtro']) ? $_GET['tipo_filtro'] : 'fecha';

try {
    if ($tipo_filtro == 'fecha') {
        $movimientos = obtenerMovimientosPorFecha($fecha_inicio, $fecha_fin);
    } else {
        // Obtener todos los movimientos
        $stmt = $pdo->query("SELECT m.*, v.placa, v.tipo_vehiculo, v.precio_por_dia, u.nombre, u.apellido 
                            FROM movimientos m 
                            JOIN vehiculos v ON m.vehiculo_id = v.id 
                            JOIN usuarios u ON m.usuario_id = u.id 
                            ORDER BY m.fecha_hora_entrada DESC");
        $movimientos = $stmt->fetchAll();
    }
    
    $estadisticas = obtenerEstadisticasDia($fecha_inicio);
} catch (Exception $e) {
    $mensaje = "Error: " . $e->getMessage();
    $tipo_mensaje = "error";
}

// Calcular estadísticas generales
$total_entradas = 0;
$total_salidas = 0;
$tiempo_promedio = 0;
$vehiculos_unicos = [];

foreach ($movimientos as $mov) {
    if ($mov['tipo_movimiento'] == 'Entrada') {
        $total_entradas++;
    } else {
        $total_salidas++;
    }
    
    if (!in_array($mov['vehiculo_id'], $vehiculos_unicos)) {
        $vehiculos_unicos[] = $mov['vehiculo_id'];
    }
    
    if ($mov['fecha_hora_salida']) {
        $tiempo = calcularTiempoTranscurrido($mov['fecha_hora_entrada'], $mov['fecha_hora_salida']);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Sistema de Control de Vehículos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📊 Reportes y Consultas</h1>
            <nav>
                <a href="index.php" class="nav-link">Registro</a>
                <a href="reportes.php" class="nav-link active">Reportes</a>
                <a href="vehiculos.php" class="nav-link">Vehículos</a>
            </nav>
        </header>

        <main>
            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <!-- Filtros -->
            <section class="card">
                <h2>🔍 Filtros de Búsqueda</h2>
                <form method="GET" class="form-inline">
                    <div class="form-group">
                        <label for="tipo_filtro">Tipo de Consulta:</label>
                        <select id="tipo_filtro" name="tipo_filtro" onchange="toggleDateFilters()">
                            <option value="fecha" <?php echo $tipo_filtro == 'fecha' ? 'selected' : ''; ?>>Por Fecha</option>
                            <option value="todos" <?php echo $tipo_filtro == 'todos' ? 'selected' : ''; ?>>Todos los Registros</option>
                        </select>
                    </div>

                    <div id="date-filters" class="form-group" style="<?php echo $tipo_filtro == 'todos' ? 'display: none;' : ''; ?>">
                        <label for="fecha_inicio">Fecha Inicio:</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                        
                        <label for="fecha_fin">Fecha Fin:</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
                    </div>

                    <button type="submit" class="btn btn-secondary">🔍 Consultar</button>
                </form>
            </section>

            <!-- Estadísticas -->
            <section class="card">
                <h2>📈 Estadísticas</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($movimientos); ?></div>
                        <div class="stat-label">Total Movimientos</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $total_entradas; ?></div>
                        <div class="stat-label">Entradas</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $total_salidas; ?></div>
                        <div class="stat-label">Salidas</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($vehiculos_unicos); ?></div>
                        <div class="stat-label">Vehículos Únicos</div>
                    </div>
                </div>
            </section>

            <!-- Tabla de Movimientos -->
            <section class="card">
                <h2>📋 Historial de Movimientos</h2>
                <?php if (empty($movimientos)): ?>
                    <p class="no-data">No se encontraron movimientos para los filtros seleccionados</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Placa</th>
                                    <th>Tipo</th>
                                    <th>Conductor</th>
                                    <th>Movimiento</th>
                                    <th>Fecha/Hora Entrada</th>
                                    <th>Fecha/Hora Salida</th>
                                    <th>Tiempo</th>
                                    <th>Precio/Día</th>
                                    <th>Total Pagado</th>
                                    <th>Estado</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movimientos as $mov): ?>
                                    <tr class="<?php echo $mov['estado'] == 'Activo' ? 'row-active' : ''; ?>">
                                        <td><strong><?php echo htmlspecialchars($mov['placa']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($mov['tipo_vehiculo']); ?></td>
                                        <td><?php echo htmlspecialchars($mov['nombre'] . ' ' . $mov['apellido']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $mov['tipo_movimiento'] == 'Entrada' ? 'success' : 'warning'; ?>">
                                                <?php echo $mov['tipo_movimiento']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatearFechaHora($mov['fecha_hora_entrada']); ?></td>
                                        <td><?php echo formatearFechaHora($mov['fecha_hora_salida']); ?></td>
                                        <td>
                                            <?php if ($mov['fecha_hora_salida']): ?>
                                                <?php echo calcularTiempoTranscurrido($mov['fecha_hora_entrada'], $mov['fecha_hora_salida']); ?>
                                            <?php else: ?>
                                                <span class="tiempo-activo">
                                                    <?php echo calcularTiempoTranscurrido($mov['fecha_hora_entrada']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="precio">S/ <?php echo number_format($mov['precio_por_dia'], 2); ?></td>
                                        <td class="precio">
                                            <?php if ($mov['precio_total'] > 0): ?>
                                                S/ <?php echo number_format($mov['precio_total'], 2); ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $mov['estado'] == 'Activo' ? 'primary' : 'secondary'; ?>">
                                                <?php echo $mov['estado']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($mov['observaciones'] ?: '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Botón de Exportar -->
            <section class="card">
                <h2>💾 Exportar Datos</h2>
                <p>Exportar los datos filtrados a diferentes formatos:</p>
                <div class="export-buttons">
                    <button onclick="exportarCSV()" class="btn btn-secondary">📄 Exportar CSV</button>
                    <button onclick="imprimirReporte()" class="btn btn-secondary">🖨️ Imprimir</button>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 Sistema de Control de Vehículos. Todos los derechos reservados.</p>
        </footer>
    </div>

    <script src="js/script.js"></script>
    <script>
        function toggleDateFilters() {
            const tipoFiltro = document.getElementById('tipo_filtro').value;
            const dateFilters = document.getElementById('date-filters');
            
            if (tipoFiltro === 'fecha') {
                dateFilters.style.display = 'block';
            } else {
                dateFilters.style.display = 'none';
            }
        }

        function exportarCSV() {
            // Implementar exportación a CSV
            alert('Función de exportación CSV en desarrollo');
        }

        function imprimirReporte() {
            window.print();
        }

        // Actualizar tiempo en tiempo real para vehículos activos
        function actualizarTiempos() {
            const elementos = document.querySelectorAll('.tiempo-activo');
            elementos.forEach(elemento => {
                // Aquí se podría implementar actualización en tiempo real
                // Por simplicidad, mantenemos el tiempo estático
            });
        }

        // Actualizar cada minuto
        setInterval(actualizarTiempos, 60000);
    </script>
</body>
</html>
