<?php 
$title = "Caja y Gastos (Admin)";
$current_page = 'caja';
require __DIR__ . '/../layouts/header.php'; 
?>

<div class="container" style="max-width: 1200px; margin-top: 2rem;">
    <h2>💰 Caja del Día (<?php echo date('d/m/Y', strtotime($fecha)); ?>)</h2>

    <!-- Resumen Cards -->
    <div class="grid-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 2rem;">
        <div class="card" style="text-align: center; border-left: 5px solid #2ecc71;">
            <h3>💵 Ingreso Efectivo</h3>
            <p style="font-size: 2em; font-weight: bold; color: #2ecc71;">S/ <?php echo number_format($ingreso_efectivo, 2); ?></p>
        </div>
        <div class="card" style="text-align: center; border-left: 5px solid #9b59b6;">
            <h3>📱 Ingreso Yape</h3>
            <p style="font-size: 2em; font-weight: bold; color: #9b59b6;">S/ <?php echo number_format($ingreso_yape, 2); ?></p>
        </div>
        <div class="card" style="text-align: center; border-left: 5px solid #e74c3c;">
            <h3>💸 Gastos (Salidas)</h3>
            <p style="font-size: 2em; font-weight: bold; color: #e74c3c;">S/ <?php echo number_format($total_gastos, 2); ?></p>
        </div>
        <div class="card" style="text-align: center; border-left: 5px solid #34495e; background: #f8f9fa;">
            <h3>⚖️ Balance Neto</h3>
            <p style="font-size: 2em; font-weight: bold; color: #34495e;">S/ <?php echo number_format($balance_neto, 2); ?></p>
            <small>Total Ingresos - Gastos</small>
        </div>
    </div>

    <!-- Top Usuarios -->
    <?php if (!empty($top_usuarios)): ?>
    <div class="card" style="margin-bottom: 2rem;">
        <h3>🏆 Top Recaudadores del Día</h3>
        <table class="data-table">
             <thead>
                <tr>
                    <th>Usuario</th>
                    <th style="text-align: right;">Total Recaudado</th>
                </tr>
             </thead>
             <tbody>
                <?php foreach ($top_usuarios as $user): ?>
                <tr>
                    <td>👤 <?php echo htmlspecialchars($user['nombre_usuario']); ?></td>
                    <td style="text-align: right; font-weight: bold;">S/ <?php echo number_format($user['total_recaudado'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
             </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="grid-container" style="grid-template-columns: 1fr 2fr; gap: 20px;">
        <!-- Registrar Gasto -->
        <div class="card">
            <h3>📉 Registrar Gasto / Salida</h3>
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-error"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="accion" value="registrar_gasto">
                <div class="form-group">
                    <label for="descripcion">Descripción del Gasto:</label>
                    <input type="text" name="descripcion" required placeholder="Ej: Almuerzo personal, Limpieza...">
                </div>
                <div class="form-group">
                    <label for="monto">Monto (S/):</label>
                    <input type="number" step="0.01" name="monto" required placeholder="0.00">
                </div>
                <button type="submit" class="btn btn-danger" style="width: 100%;">Registrar Salida de Dinero</button>
            </form>
        </div>

        <!-- Lista de Gastos -->
        <div class="card">
            <h3>📋 Lista de Gastos del Día</h3>
            <?php if (empty($gastos)): ?>
                <p class="no-data">No hay gastos registrados hoy.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Descripción</th>
                            <th>Registrado por</th>
                            <th style="text-align: right;">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gastos as $g): ?>
                            <tr>
                                <td><?php echo date('H:i', strtotime($g['fecha_hora'])); ?></td>
                                <td><?php echo htmlspecialchars($g['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($g['registrado_por']); ?></td>
                                <td style="text-align: right; font-weight: bold; color: #e74c3c;">
                                    - S/ <?php echo number_format($g['monto'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    </div>

    <!-- Historial de Cierres -->
    <div class="card" style="margin-top: 2rem;">
        <h3>📅 Historial de Cierre de Caja (Últimos 30 días)</h3>
        <?php if (empty($historial)): ?>
            <p class="no-data">No hay datos históricos disponibles.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th style="text-align: right;">Ingreso Efectivo</th>
                        <th style="text-align: right;">Ingreso Yape</th>
                        <th style="text-align: right;">Total Ingresos</th>
                        <th style="text-align: right;">Gastos</th>
                        <th style="text-align: right;">Balance Neto</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $dia): 
                        $total_ingreso_dia = $dia['efectivo'] + $dia['yape'];
                        $balance_dia = $total_ingreso_dia - $dia['gastos'];
                    ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($dia['fecha'])); ?></td>
                            <td style="text-align: right; color: #2ecc71;">S/ <?php echo number_format($dia['efectivo'], 2); ?></td>
                            <td style="text-align: right; color: #9b59b6;">S/ <?php echo number_format($dia['yape'], 2); ?></td>
                            <td style="text-align: right; font-weight: bold;">S/ <?php echo number_format($total_ingreso_dia, 2); ?></td>
                            <td style="text-align: right; color: #e74c3c;">- S/ <?php echo number_format($dia['gastos'], 2); ?></td>
                            <td style="text-align: right; font-weight: bold; color: #34495e;">S/ <?php echo number_format($balance_dia, 2); ?></td>
                            <td style="text-align: center;">
                                <a href="<?php echo $base_url; ?>/public/index.php/caja/detalle?fecha=<?php echo $dia['fecha']; ?>" class="btn btn-secondary" style="padding: 2px 8px; font-size: 0.8rem;">👁️ Ver Detalles</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
