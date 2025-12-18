<?php
$title = "Dashboard - Estadísticas";
$current_page = 'dashboard';
require __DIR__ . '/../layouts/header.php';
?>

<!-- Incluir Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="dashboard-header"
    style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; text-align: center;">
    <h1 style="margin: 0; font-size: 2.5rem;">📊 Dashboard de Estadísticas</h1>
    <p style="margin: 10px 0 0 0; opacity: 0.9; font-size: 1.1rem;">Vista general del sistema de control de vehículos
    </p>
</div>

<?php if (isset($error)): ?>
    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<!-- Estadísticas Generales -->
<div class="stats-grid"
    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="stat-card primary"
        style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #667eea;">
        <div class="stat-number" style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px; color: #667eea;">
            <?php echo $stats['total_vehiculos'] ?? 0; ?>
        </div>
        <div class="stat-label" style="color: #7f8c8d; font-size: 1rem; font-weight: 500;">🚗 Vehículos Registrados
        </div>
    </div>

    <div class="stat-card success"
        style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #27ae60;">
        <div class="stat-number" style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px; color: #27ae60;">
            <?php echo $stats['movimientos_activos'] ?? 0; ?>
        </div>
        <div class="stat-label" style="color: #7f8c8d; font-size: 1rem; font-weight: 500;">🏃 Vehículos Activos</div>
    </div>

    <div class="stat-card warning"
        style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #f39c12;">
        <div class="stat-number" style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px; color: #f39c12;">
            <?php echo $stats['movimientos_hoy'] ?? 0; ?>
        </div>
        <div class="stat-label" style="color: #7f8c8d; font-size: 1rem; font-weight: 500;">📈 Movimientos Hoy</div>
    </div>

    <div class="stat-card danger"
        style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #e74c3c;">
        <div class="stat-number" style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px; color: #e74c3c;">
            S/ <?php echo number_format($stats['ingresos_hoy'] ?? 0, 2); ?>
        </div>
        <div class="stat-label" style="color: #7f8c8d; font-size: 1rem; font-weight: 500;">💰 Ingresos Hoy</div>
    </div>

    <div class="stat-card info" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #3498db;">
        <div class="stat-number" style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px; color: #3498db;">
            S/ <?php echo number_format($stats['ingresos_mes'] ?? 0, 2); ?>
        </div>
        <div class="stat-label" style="color: #7f8c8d; font-size: 1rem; font-weight: 500;">📅 Ingresos Mes</div>
    </div>
</div>

<div class="charts-grid"
    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; margin-bottom: 30px;">
    <!-- Gráfico de Vehículos por Tipo -->
    <div class="card" style="padding: 20px;">
        <h3 style="text-align: center; margin-bottom: 20px; color: #2c3e50;">🚗 Vehículos por Tipo</h3>
        <div style="height: 300px; position: relative;">
            <canvas id="vehiculosChart"></canvas>
        </div>
    </div>

    <!-- Gráfico de Ingresos -->
    <div class="card" style="padding: 20px;">
        <h3 style="text-align: center; margin-bottom: 20px; color: #2c3e50;">💰 Ingresos de los Últimos 30 Días</h3>
        <div style="height: 300px; position: relative;">
            <canvas id="ingresosChart"></canvas>
        </div>
    </div>
</div>

<!-- Gráfico de Horas Pico (Fila Completa) -->
<div class="card" style="padding: 20px; margin-bottom: 30px;">
    <h3 style="text-align: center; margin-bottom: 20px; color: #2c3e50;">🕒 Horas Pico de Ingreso</h3>
    <div style="height: 300px; position: relative;">
        <canvas id="horasPicoChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos para el gráfico de vehículos
    const vehiculosData = <?php echo !empty($vehiculos_por_tipo) ? json_encode($vehiculos_por_tipo) : '{}'; ?>;
    const tipos = Object.keys(vehiculosData);
    const cantidades = Object.values(vehiculosData);

    // Gráfico de Vehículos (Pie)
    const ctxVehiculos = document.getElementById('vehiculosChart');
    if (ctxVehiculos) {
        new Chart(ctxVehiculos, {
            type: 'doughnut',
            data: {
                labels: tipos.length ? tipos : ['Sin datos'],
                datasets: [{
                    data: cantidades.length ? cantidades : [1],
                    backgroundColor: [
                        '#667eea', '#764ba2', '#27ae60', '#f39c12', '#e74c3c', '#3498db'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Datos para el gráfico de ingresos
    const ingresosData = <?php echo !empty($ingresos_semanales) ? json_encode($ingresos_semanales) : '{}'; ?>;
    const fechas = Object.keys(ingresosData);
    const montos = Object.values(ingresosData);

    // Formatear fechas para mostrar
    const fechasFormateadas = fechas.map(f => {
        const d = new Date(f + 'T00:00:00'); // Asegurar interpretación correcta de fecha local
        return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
    });

    // Gráfico de Ingresos (Bar)
    const ctxIngresos = document.getElementById('ingresosChart');
    if (ctxIngresos) {
        new Chart(ctxIngresos, {
            type: 'bar',
            data: {
                labels: fechas.length ? fechasFormateadas : ['Sin datos'],
                datasets: [{
                    label: 'Ingresos (S/)',
                    data: montos.length ? montos : [0],
                    backgroundColor: '#667eea',
                    borderRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value;
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Datos para Horas Pico
    const horasData = <?php echo !empty($horas_pico) ? json_encode(array_values($horas_pico)) : '[]'; ?>;
    // Etiquetas de 5:00 a 22:00
    const etiquetasHoras = Array.from({length: 18}, (_, i) => (i + 5) + ':00');
    // Gráfico de Horas Pico (Line)
    const ctxHoras = document.getElementById('horasPicoChart');
    if (ctxHoras) {
        new Chart(ctxHoras, {
            type: 'line',
            data: {
                labels: etiquetasHoras,
                datasets: [{
                    label: 'Entradas de Vehículos',
                    data: horasData,
                    borderColor: '#f39c12',
                    backgroundColor: 'rgba(243, 156, 18, 0.2)',

                    fill: true,
                    tension: 0.4, // Curvas suaves
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad de Movimientos'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                         callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' movimientos';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>