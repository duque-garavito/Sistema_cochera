<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Verificar login
verificarLogin();

// Obtener estadísticas
$error_stats = '';
$stats_generales = [];
$dias_ocupados = [];
$horas_entrada = [];
$horas_salida = [];
$tipos_vehiculos = [];
$ingresos_7_dias = [];
$ingresos_por_tipo = [];
$resumen_ingresos_tipo = [];

try {
    $stats_generales = obtenerEstadisticasGenerales();
    $dias_ocupados = obtenerDiasMasOcupados(5);
    $horas_entrada = obtenerHorasPico('entrada');
    $horas_salida = obtenerHorasPico('salida');
    $tipos_vehiculos = obtenerEstadisticasTiposVehiculos();
    $ingresos_7_dias = obtenerIngresosPorDia(7);
    $ingresos_por_tipo = obtenerIngresosPorTipoVehiculo(7);
    $resumen_ingresos_tipo = obtenerResumenIngresosPorTipo(7);
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

        .stat-card.primary {
            border-left-color: #667eea;
        }

        .stat-card.success {
            border-left-color: #27ae60;
        }

        .stat-card.warning {
            border-left-color: #f39c12;
        }

        .stat-card.danger {
            border-left-color: #e74c3c;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-card.primary .stat-number {
            color: #667eea;
        }

        .stat-card.success .stat-number {
            color: #27ae60;
        }

        .stat-card.warning .stat-number {
            color: #f39c12;
        }

        .stat-card.danger .stat-number {
            color: #e74c3c;
        }

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

        .dashboard-controls {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .dashboard-controls h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .controls-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .control-group {
            display: flex;
            flex-direction: column;
        }

        .control-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .control-group select {
            padding: 10px 15px;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            font-size: 1rem;
            background: white;
            transition: border-color 0.3s ease;
        }

        .control-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-refresh {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            align-self: flex-end;
        }

        .btn-refresh:hover {
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }

        .no-data {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            padding: 20px;
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
        <header>
            <h1>🏢 Sistema de Control de Vehículos</h1>
            <nav>
                <a href="index.php" class="nav-link">Registro</a>
                <a href="dashboard.php" class="nav-link active">Dashboard</a>
                <a href="reportes.php" class="nav-link">Reportes</a>
                <a href="vehiculos.php" class="nav-link">Vehículos</a>
                <a href="logout.php" class="nav-link">🚪 Salir</a>
            </nav>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                    (<?php echo ucfirst($_SESSION['rol']); ?>)</span>
            </div>
        </header>

        <main>
            <?php if ($error_stats): ?>
                <div class="alert alert-error" style="margin-bottom: 20px;">
                    ⚠️ Error al cargar estadísticas: <?php echo htmlspecialchars($error_stats); ?>
                </div>
            <?php endif; ?>

            <!-- Debug Info (temporal) -->
            <div class="debug-info" style="background: #e8f4f8; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                <strong>🔍 Debug Info:</strong><br>
                Datos cargados:
                Días ocupados: <?php echo count($dias_ocupados); ?> |
                Tipos vehículos: <?php echo count($tipos_vehiculos); ?> |
                Ingresos totales: <?php echo count($ingresos_7_dias); ?> |
                Ingresos por tipo: <?php echo count($ingresos_por_tipo); ?> |
                Resumen tipos: <?php echo count($resumen_ingresos_tipo); ?>
            </div>

            <!-- Controles del Dashboard -->
            <div class="dashboard-controls">
                <h2>🎛️ Controles del Dashboard</h2>
                <div class="controls-grid">
                    <div class="control-group">
                        <label for="dias_filtro">Días a mostrar:</label>
                        <select id="dias_filtro" onchange="actualizarGrafico('dias')">
                            <option value="5">Últimos 5 días</option>
                            <option value="7">Últimos 7 días</option>
                            <option value="15">Últimos 15 días</option>
                            <option value="30">Últimos 30 días</option>
                        </select>
                    </div>

                    <div class="control-group">
                        <label for="tipo_grafico_dias">Tipo de gráfico (Días):</label>
                        <select id="tipo_grafico_dias" onchange="cambiarTipoGrafico('diasChart')">
                            <option value="bar">Barras</option>
                            <option value="line">Líneas</option>
                            <option value="doughnut">Donut</option>
                        </select>
                    </div>

                    <div class="control-group">
                        <label for="tipo_grafico_tipos">Tipo de gráfico (Vehículos):</label>
                        <select id="tipo_grafico_tipos" onchange="cambiarTipoGrafico('tiposChart')">
                            <option value="doughnut">Donut</option>
                            <option value="pie">Circular</option>
                            <option value="bar">Barras</option>
                        </select>
                    </div>

                    <div class="control-group">
                        <label for="vista_ingresos">Vista de Ingresos:</label>
                        <select id="vista_ingresos" onchange="cambiarVistaIngresos()">
                            <option value="total">💰 Total por Día</option>
                            <option value="por_tipo">🚗 Por Tipo de Vehículo</option>
                            <option value="comparacion">📊 Comparación Autos vs Motos</option>
                        </select>
                    </div>

                    <div class="control-group">
                        <button onclick="probarControles()" class="btn-refresh">🧪 Probar Controles</button>
                        <button onclick="actualizarDashboard()" class="btn-refresh">🔄 Actualizar Dashboard</button>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Generales -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-number"><?php echo $stats_generales['total_vehiculos'] ?? 0; ?></div>
                    <div class="stat-label">🚗 Vehículos Registrados</div>
                </div>

                <div class="stat-card success">
                    <div class="stat-number"><?php echo $stats_generales['vehiculos_activos'] ?? 0; ?></div>
                    <div class="stat-label">🏃 Vehículos Activos</div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-number"><?php echo $stats_generales['movimientos_hoy'] ?? 0; ?></div>
                    <div class="stat-label">📈 Movimientos Hoy</div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-number">S/ <?php echo number_format($stats_generales['ingresos_hoy'] ?? 0, 2); ?></div>
                    <div class="stat-label">💰 Ingresos Hoy</div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="charts-grid">
                <!-- Días Más Ocupados -->
                <div class="chart-container">
                    <h3 class="chart-title">📅 Días Más Ocupados (Últimos 30 días)</h3>
                    <?php if (empty($dias_ocupados)): ?>
                        <div class="no-data">No hay datos de movimientos disponibles</div>
                    <?php else: ?>
                        <canvas id="diasChart" class="chart-canvas"></canvas>
                    <?php endif; ?>
                </div>

                <!-- Tipos de Vehículos -->
                <div class="chart-container">
                    <h3 class="chart-title">🚗 Distribución de Tipos de Vehículos</h3>
                    <?php if (empty($tipos_vehiculos)): ?>
                        <div class="no-data">No hay vehículos registrados</div>
                    <?php else: ?>
                        <canvas id="tiposChart" class="chart-canvas"></canvas>
                    <?php endif; ?>
                </div>

                <!-- Horas Pico de Entrada -->
                <div class="chart-container">
                    <h3 class="chart-title">⏰ Horas Pico de Entrada</h3>
                    <?php if (empty($horas_entrada)): ?>
                        <div class="no-data">No hay datos de entradas disponibles</div>
                    <?php else: ?>
                        <canvas id="entradaChart" class="chart-canvas"></canvas>
                    <?php endif; ?>
                </div>

                <!-- Horas Pico de Salida -->
                <div class="chart-container">
                    <h3 class="chart-title">⏰ Horas Pico de Salida</h3>
                    <?php if (empty($horas_salida)): ?>
                        <div class="no-data">No hay datos de salidas disponibles</div>
                    <?php else: ?>
                        <canvas id="salidaChart" class="chart-canvas"></canvas>
                    <?php endif; ?>
                </div>

                <!-- Ingresos por Día -->
                <div class="chart-container full-width-chart">
                    <h3 class="chart-title" id="tituloIngresos">💰 Ingresos por Día (Últimos 7 días)</h3>
                    <?php if (empty($ingresos_7_dias) && empty($ingresos_por_tipo)): ?>
                        <div class="no-data">No hay datos de ingresos disponibles</div>
                    <?php else: ?>
                        <canvas id="ingresosChart" class="chart-canvas"></canvas>
                        <!-- Información adicional -->
                        <div id="infoIngresos" class="chart-info" style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; font-size: 0.9rem;">
                            <!-- Se llenará dinámicamente -->
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 Sistema de Control de Vehículos. Todos los derechos reservados.</p>
        </footer>
    </div>

    <script>
        // Datos para los gráficos
        const diasData = <?php echo json_encode($dias_ocupados); ?>;
        const tiposData = <?php echo json_encode($tipos_vehiculos); ?>;
        const entradaData = <?php echo json_encode($horas_entrada); ?>;
        const salidaData = <?php echo json_encode($horas_salida); ?>;
        const ingresosData = <?php echo json_encode($ingresos_7_dias); ?>;
        const ingresosPorTipoData = <?php echo json_encode($ingresos_por_tipo); ?>;
        const resumenIngresosTipo = <?php echo json_encode($resumen_ingresos_tipo); ?>;

        // Debug: Mostrar datos en consola
        console.log('Datos cargados:', {
            diasData,
            tiposData,
            entradaData,
            salidaData,
            ingresosData,
            ingresosPorTipoData,
            resumenIngresosTipo
        });

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

        // Variables globales para los gráficos
        let diasChart, tiposChart, entradaChart, salidaChart, ingresosChart;

        // Función para mostrar mensaje de error
        function mostrarError(mensaje) {
            console.error(mensaje);
            // Aquí podrías mostrar un toast o modal con el error
        }

        // Función para verificar si hay datos
        function verificarDatos(datos, nombre) {
            if (!datos || datos.length === 0) {
                mostrarError(`No hay datos disponibles para ${nombre}`);
                return false;
            }
            return true;
        }

        // Función para crear gráfico de días
        function crearGraficoDias() {
            const ctx = document.getElementById('diasChart').getContext('2d');
            const tipo = document.getElementById('tipo_grafico_dias').value;

            if (diasChart) diasChart.destroy();

            if (!verificarDatos(diasData, 'días más ocupados')) return;

            diasChart = new Chart(ctx, {
                type: tipo,
                data: {
                    labels: diasData.map(item => new Date(item.fecha).toLocaleDateString()),
                    datasets: [{
                        label: 'Movimientos',
                        data: diasData.map(item => item.total_movimientos),
                        backgroundColor: tipo === 'doughnut' ? [
                            'rgba(102, 126, 234, 0.8)',
                            'rgba(46, 204, 113, 0.8)',
                            'rgba(241, 196, 15, 0.8)',
                            'rgba(231, 76, 60, 0.8)'
                        ] : 'rgba(102, 126, 234, 0.8)',
                        borderColor: 'rgba(102, 126, 234, 1)',
                        borderWidth: 1
                    }]
                },
                options: chartOptions
            });
        }

        // Función para crear gráfico de tipos
        function crearGraficoTipos() {
            const ctx = document.getElementById('tiposChart').getContext('2d');
            const tipo = document.getElementById('tipo_grafico_tipos').value;

            if (tiposChart) tiposChart.destroy();

            if (!verificarDatos(tiposData, 'tipos de vehículos')) return;

            tiposChart = new Chart(ctx, {
                type: tipo,
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
        }

        // Crear gráficos iniciales
        crearGraficoDias();
        crearGraficoTipos();

        // Crear gráfico de horas de entrada
        function crearGraficoEntrada() {
            const ctx = document.getElementById('entradaChart').getContext('2d');
            if (entradaChart) entradaChart.destroy();

            if (!verificarDatos(entradaData, 'horas de entrada')) return;

            entradaChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array.from({
                        length: 24
                    }, (_, i) => i + ':00'),
                    datasets: [{
                        label: 'Entradas',
                        data: Array.from({
                            length: 24
                        }, (_, i) => {
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
        }

        // Crear gráfico de horas de salida
        function crearGraficoSalida() {
            const ctx = document.getElementById('salidaChart').getContext('2d');
            if (salidaChart) salidaChart.destroy();

            if (!verificarDatos(salidaData, 'horas de salida')) return;

            salidaChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array.from({
                        length: 24
                    }, (_, i) => i + ':00'),
                    datasets: [{
                        label: 'Salidas',
                        data: Array.from({
                            length: 24
                        }, (_, i) => {
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
        }

        // Crear gráfico de ingresos
        function crearGraficoIngresos(vista = 'total') {
            const ctx = document.getElementById('ingresosChart').getContext('2d');
            if (ingresosChart) ingresosChart.destroy();

            let titulo = '';
            let data = {};
            let info = '';

            switch (vista) {
                case 'total':
                    if (!verificarDatos(ingresosData, 'ingresos totales')) return;
                    titulo = '💰 Ingresos Totales por Día (Últimos 7 días)';
                    data = {
                        labels: ingresosData.map(item => new Date(item.fecha).toLocaleDateString()),
                        datasets: [{
                            label: 'Ingresos Totales (S/)',
                            data: ingresosData.map(item => parseFloat(item.total_ingresos)),
                            backgroundColor: 'rgba(241, 196, 15, 0.8)',
                            borderColor: 'rgba(241, 196, 15, 1)',
                            borderWidth: 1
                        }]
                    };
                    info = `Total de ingresos: S/ ${ingresosData.reduce((sum, item) => sum + parseFloat(item.total_ingresos), 0).toFixed(2)}`;
                    break;

                case 'por_tipo':
                    if (!verificarDatos(ingresosPorTipoData, 'ingresos por tipo')) return;

                    // Agrupar por fecha y tipo
                    const fechas = [...new Set(ingresosPorTipoData.map(item => item.fecha))].sort();
                    const tipos = [...new Set(ingresosPorTipoData.map(item => item.tipo_vehiculo))];

                    titulo = '🚗 Ingresos por Tipo de Vehículo (Últimos 7 días)';

                    const datasets = tipos.map((tipo, index) => {
                        const colores = [
                            'rgba(102, 126, 234, 0.8)', // Azul
                            'rgba(46, 204, 113, 0.8)', // Verde
                            'rgba(241, 196, 15, 0.8)', // Amarillo
                            'rgba(231, 76, 60, 0.8)' // Rojo
                        ];

                        return {
                            label: tipo,
                            data: fechas.map(fecha => {
                                const item = ingresosPorTipoData.find(d => d.fecha === fecha && d.tipo_vehiculo === tipo);
                                return item ? parseFloat(item.total_ingresos) : 0;
                            }),
                            backgroundColor: colores[index % colores.length],
                            borderColor: colores[index % colores.length].replace('0.8', '1'),
                            borderWidth: 1
                        };
                    });

                    data = {
                        labels: fechas.map(fecha => new Date(fecha).toLocaleDateString()),
                        datasets: datasets
                    };

                    // Información por tipo
                    info = resumenIngresosTipo.map(item =>
                        `${item.tipo_vehiculo}: S/ ${parseFloat(item.total_ingresos).toFixed(2)} (${item.total_salidas} salidas)`
                    ).join(' | ');
                    break;

                case 'comparacion':
                    if (!verificarDatos(resumenIngresosTipo, 'resumen por tipo')) return;

                    titulo = '📊 Comparación: Autos vs Motos';

                    const autosData = resumenIngresosTipo.find(item => item.tipo_vehiculo.toLowerCase().includes('auto'));
                    const motosData = resumenIngresosTipo.find(item => item.tipo_vehiculo.toLowerCase().includes('moto'));

                    data = {
                        labels: ['Autos', 'Motos'],
                        datasets: [{
                            label: 'Ingresos Totales (S/)',
                            data: [
                                autosData ? parseFloat(autosData.total_ingresos) : 0,
                                motosData ? parseFloat(motosData.total_ingresos) : 0
                            ],
                            backgroundColor: [
                                'rgba(102, 126, 234, 0.8)',
                                'rgba(46, 204, 113, 0.8)'
                            ],
                            borderColor: [
                                'rgba(102, 126, 234, 1)',
                                'rgba(46, 204, 113, 1)'
                            ],
                            borderWidth: 1
                        }]
                    };

                    const totalAutos = autosData ? parseFloat(autosData.total_ingresos) : 0;
                    const totalMotos = motosData ? parseFloat(motosData.total_ingresos) : 0;
                    const total = totalAutos + totalMotos;

                    info = `Autos: S/ ${totalAutos.toFixed(2)} (${((totalAutos/total)*100).toFixed(1)}%) | ` +
                        `Motos: S/ ${totalMotos.toFixed(2)} (${((totalMotos/total)*100).toFixed(1)}%)`;
                    break;
            }

            // Actualizar título
            document.getElementById('tituloIngresos').textContent = titulo;

            // Crear gráfico
            ingresosChart = new Chart(ctx, {
                type: vista === 'comparacion' ? 'doughnut' : 'bar',
                data: data,
                options: chartOptions
            });

            // Actualizar información
            document.getElementById('infoIngresos').innerHTML = `<strong>📈 Resumen:</strong> ${info}`;
        }

        // Función para inicializar todos los gráficos
        function inicializarGraficos() {
            console.log('Inicializando gráficos...');

            // Verificar que Chart.js esté disponible
            if (typeof Chart === 'undefined') {
                console.error('Chart.js no está cargado');
                return;
            }

            // Crear todos los gráficos
            crearGraficoEntrada();
            crearGraficoSalida();
            crearGraficoIngresos();

            console.log('Gráficos inicializados correctamente');
        }

        // Funciones de control
        function cambiarTipoGrafico(chartId) {
            if (chartId === 'diasChart') {
                crearGraficoDias();
            } else if (chartId === 'tiposChart') {
                crearGraficoTipos();
            }
        }

        function actualizarGrafico(tipo) {
            // Aquí podrías hacer una llamada AJAX para obtener nuevos datos
            console.log(`Actualizando gráfico de ${tipo} con ${document.getElementById('dias_filtro').value} días`);
        }

        function actualizarDashboard() {
            // Recargar la página para obtener datos actualizados
            location.reload();
        }

        function cambiarVistaIngresos() {
            const vista = document.getElementById('vista_ingresos').value;
            console.log('Cambiando vista de ingresos a:', vista);
            crearGraficoIngresos(vista);
        }

        function probarControles() {
            console.log('🧪 Probando controles...');

            // Verificar que los elementos existen
            const vistaIngresos = document.getElementById('vista_ingresos');
            if (vistaIngresos) {
                console.log('✅ Selector de vista encontrado');
                console.log('Opciones disponibles:', Array.from(vistaIngresos.options).map(opt => opt.value));

                // Probar cambio de vista
                vistaIngresos.value = 'por_tipo';
                cambiarVistaIngresos();

                setTimeout(() => {
                    vistaIngresos.value = 'comparacion';
                    cambiarVistaIngresos();
                }, 2000);

                setTimeout(() => {
                    vistaIngresos.value = 'total';
                    cambiarVistaIngresos();
                }, 4000);

            } else {
                console.error('❌ Selector de vista no encontrado');
            }

            // Verificar datos
            console.log('Datos disponibles:', {
                ingresosData: ingresosData?.length || 0,
                ingresosPorTipoData: ingresosPorTipoData?.length || 0,
                resumenIngresosTipo: resumenIngresosTipo?.length || 0
            });
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM cargado, inicializando gráficos...');
            inicializarGraficos();
        });

        // También intentar inicializar inmediatamente (por si el DOM ya está listo)
        if (document.readyState === 'loading') {
            // DOM aún cargando, esperar al evento
        } else {
            // DOM ya está listo
            console.log('DOM ya está listo, inicializando inmediatamente...');
            inicializarGraficos();
        }
    </script>
</body>

</html>