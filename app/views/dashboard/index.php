<?php 
$title = "Dashboard - Estadísticas";
$current_page = 'dashboard';
require __DIR__. '/../layouts/header.php'; 
?>

<!-- Incluir Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="dashboard-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; text-align: center;">
    <h1 style="margin: 0; font-size: 2.5rem;">📊 Dashboard de Estadísticas</h1>
    <p style="margin: 10px 0 0 0; opacity: 0.9; font-size: 1.1rem;">Vista general del sistema de control de vehículos</p>
</div>

<!-- Estadísticas Generales -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="stat-card primary" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #667eea;">
        <div class="stat-number" style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px; color: #667eea;">
            <?php echo $stats['total_vehiculos'] ?? 0; ?>
        </div>
        <div class="stat-label" style="color: #7f8c8d; font-size: 1rem; font-weight: 500;">🚗 Vehículos Registrados</div>
    </div>

    <div class="stat-card success" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #27ae60;">
        <div class="stat-number" style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px; color: #27ae60;">
            <?php echo $stats['movimientos_activos'] ?? 0; ?>
        </div>
        <div class="stat-label" style="color: #7f8c8d; font-size: 1rem; font-weight: 500;">🏃 Vehículos Activos</div>
    </div>

    <div class="stat-card warning" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #f39c12;">
        <div class="stat-number" style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px; color: #f39c12;">
            <?php echo $stats['movimientos_hoy'] ?? 0; ?>
        </div>
        <div class="stat-label" style="color: #7f8c8d; font-size: 1rem; font-weight: 500;">📈 Movimientos Hoy</div>
    </div>

    <div class="stat-card danger" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; border-left: 4px solid #e74c3c;">
        <div class="stat-number" style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px; color: #e74c3c;">
            S/ <?php echo number_format($stats['ingresos_hoy'] ?? 0, 2); ?>
        </div>
        <div class="stat-label" style="color: #7f8c8d; font-size: 1rem; font-weight: 500;">💰 Ingresos Hoy</div>
    </div>
</div>

<div class="card">
    <h2>📊 Estadísticas del Sistema</h2>
    <div style="padding: 20px;">
        <p style="color: #7f8c8d; font-style: italic; text-align: center;">El dashboard completo con gráficos está disponible en el archivo original dashboard.php</p>
        <p style="text-align: center; margin-top: 20px;">
            <strong>Total vehículos:</strong> <?php echo $stats['total_vehiculos'] ?? 0; ?> |
            <strong>Ingresos del mes:</strong> S/ <?php echo number_format($stats['ingresos_mes'] ?? 0, 2); ?>
        </p>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
