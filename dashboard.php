<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Verificar login
verificarLogin();

// Obtener estadísticas
try {
    $stats_generales = obtenerEstadisticasGenerales();
    $dias_ocupados = obtenerDiasMasOcupados(5);
    $horas_entrada = obtenerHorasPico('entrada');
    $horas_salida = obtenerHorasPico('salida');
    $tipos_vehiculos = obtenerEstadisticasTiposVehiculos();
    $ingresos_7_dias = obtenerIngresosPorDia(7);
} catch (Exception $e) {
    $error_stats = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Control de Vehículos</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .dashboard-header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        
        .dashboard-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 4px solid;
        }
        
        .stat-card.primary { border-left-color: #667eea; }
        .stat-card.success { border-left-color: #27ae60; }
        .stat-card.warning { border-left-color: #f39c12; }
        .stat-card.danger { border-left-color: #e74c3c; }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-card.primary .stat-number { color: #667eea; }
        .stat-card.success .stat-number { color: #27ae60; }
        .stat-card.warning .stat-number { color: #f39c12; }
        .stat-card.danger .stat-number { color: #e74c3c; }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .chart-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .chart-canvas {
            max-height: 300px;
        }
        
        .full-width-chart {
            grid-column: 1 / -1;
        }
        
        .user-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-info .user-details {
            color: white;
        }
        
        .user-info .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        
        .user-info .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .dashboard-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1>📊 Dashboard</h1>
            <p>Estadísticas y análisis del sistema de control de vehículos</p>
            
            <div class="user-info">
                <div class="user-details">
                    <strong>👤 <?php echo htmlspecialchars($_SESSION['nombre']); ?></strong>
                    <span>(<?php echo ucfirst($_SESSION['rol']); ?>)</span>
                </div>
                <a href="logout.php" class="logout-btn">🚪 Cerrar Sesión</a>
            </div>
        </div>

        <!-- Estadísticas Generales -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-number"><?php echo $stats_generales['total_vehiculos']; ?></div>
                <div class="stat-label">🚗 Vehículos Registrados</div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-number"><?php echo $stats_generales['vehiculos_activos']; ?></div>
                <div class="stat-label">🏃 Vehículos Activos</div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-number"><?php echo $stats_generales['movimientos_hoy']; ?></div>
                <div class="stat-label">📈 Movimientos Hoy</div>
            </div>
            
            <div class="stat-card danger">
                <div class="stat-number">S/ <?php echo number_format($stats_generales['ingresos_hoy'], 2); ?></div>
                <div class="stat-label">💰 Ingresos Hoy</div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="charts-grid">
            <!-- Días Más Ocupados -->
            <div class="chart-container">
                <h3 class="chart-title">📅 Días Más Ocupados (Últimos 30 días)</h3>
                <canvas id="diasChart" class="chart-canvas"></canvas>
            </div>

            <!-- Tipos de Vehículos -->
            <div class="chart-container">
                <h3 class="chart-title">🚗 Distribución de Tipos de Vehículos</h3>
                <canvas id="tiposChart" class="chart-canvas"></canvas>
            </div>

            <!-- Horas Pico de Entrada -->
            <div class="chart-container">
                <h3 class="chart-title">⏰ Horas Pico de Entrada</h3>
                <canvas id="entradaChart" class="chart-canvas"></canvas>
            </div>

            <!-- Horas Pico de Salida -->
            <div class="chart-container">
                <h3 class="chart-title">⏰ Horas Pico de Salida</h3>
                <canvas id="salidaChart" class="chart-canvas"></canvas>
            </div>

            <!-- Ingresos por Día -->
            <div class="chart-container full-width-chart">
                <h3 class="chart-title">💰 Ingresos por Día (Últimos 7 días)</h3>
                <canvas id="ingresosChart" class="chart-canvas"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Datos para los gráficos
        const diasData = <?php echo json_encode($dias_ocupados); ?>;
        const tiposData = <?php echo json_encode($tipos_vehiculos); ?>;
        const entradaData = <?php echo json_encode($horas_entrada); ?>;
        const salidaData = <?php echo json_encode($horas_salida); ?>;
        const ingresosData = <?php echo json_encode($ingresos_7_dias); ?>;

        // Configuración común para gráficos
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        };

        // Gráfico de días más ocupados
        const diasCtx = document.getElementById('diasChart').getContext('2d');
        new Chart(diasCtx, {
            type: 'bar',
            data: {
                labels: diasData.map(item => new Date(item.fecha).toLocaleDateString()),
                datasets: [{
                    label: 'Movimientos',
                    data: diasData.map(item => item.total_movimientos),
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1
                }]
            },
            options: chartOptions
        });

        // Gráfico de tipos de vehículos
        const tiposCtx = document.getElementById('tiposChart').getContext('2d');
        new Chart(tiposCtx, {
            type: 'doughnut',
            data: {
                labels: tiposData.map(item => item.tipo_vehiculo),
                datasets: [{
                    data: tiposData.map(item => item.total),
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(46, 204, 113, 0.8)',
                        'rgba(241, 196, 15, 0.8)',
                        'rgba(231, 76, 60, 0.8)'
                    ]
                }]
            },
            options: chartOptions
        });

        // Gráfico de horas de entrada
        const entradaCtx = document.getElementById('entradaChart').getContext('2d');
        new Chart(entradaCtx, {
            type: 'line',
            data: {
                labels: Array.from({length: 24}, (_, i) => i + ':00'),
                datasets: [{
                    label: 'Entradas',
                    data: Array.from({length: 24}, (_, i) => {
                        const found = entradaData.find(item => item.hora == i);
                        return found ? found.total : 0;
                    }),
                    borderColor: 'rgba(46, 204, 113, 1)',
                    backgroundColor: 'rgba(46, 204, 113, 0.1)',
                    tension: 0.4
                }]
            },
            options: chartOptions
        });

        // Gráfico de horas de salida
        const salidaCtx = document.getElementById('salidaChart').getContext('2d');
        new Chart(salidaCtx, {
            type: 'line',
            data: {
                labels: Array.from({length: 24}, (_, i) => i + ':00'),
                datasets: [{
                    label: 'Salidas',
                    data: Array.from({length: 24}, (_, i) => {
                        const found = salidaData.find(item => item.hora == i);
                        return found ? found.total : 0;
                    }),
                    borderColor: 'rgba(231, 76, 60, 1)',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    tension: 0.4
                }]
            },
            options: chartOptions
        });

        // Gráfico de ingresos
        const ingresosCtx = document.getElementById('ingresosChart').getContext('2d');
        new Chart(ingresosCtx, {
            type: 'bar',
            data: {
                labels: ingresosData.map(item => new Date(item.fecha).toLocaleDateString()),
                datasets: [{
                    label: 'Ingresos (S/)',
                    data: ingresosData.map(item => parseFloat(item.total_ingresos)),
                    backgroundColor: 'rgba(241, 196, 15, 0.8)',
                    borderColor: 'rgba(241, 196, 15, 1)',
                    borderWidth: 1
                }]
            },
            options: chartOptions
        });
    </script>
</body>
</html>
