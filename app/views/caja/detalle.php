<?php 
$title = "Detalle de Caja - " . date('d/m/Y', strtotime($fecha));
$current_page = 'caja';
require __DIR__ . '/../layouts/header.php'; 
?>

<div class="container" style="max-width: 1200px; margin-top: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>📅 Detalle del Día: <?php echo date('d/m/Y', strtotime($fecha)); ?></h2>
        <a href="<?php echo $base_url; ?>/public/index.php/caja" class="btn btn-secondary">🔙 Volver a Caja</a>
    </div>

    <!-- Resumen -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: flex; gap: 20px; justify-content: space-around; flex-wrap: wrap;">
            <div><strong>💵 Efectivo:</strong> <span style="color: #2ecc71; font-weight: bold;">S/ <?php echo number_format($ingreso_efectivo, 2); ?></span></div>
            <div><strong>📱 Yape:</strong> <span style="color: #9b59b6; font-weight: bold;">S/ <?php echo number_format($ingreso_yape, 2); ?></span></div>
            <div><strong>Ingresos Totales:</strong> <span style="color: #27ae60; font-weight: bold;">S/ <?php echo number_format($total_ingresos, 2); ?></span></div>
            <div><strong>Gastos Totales:</strong> <span style="color: #e74c3c; font-weight: bold;">- S/ <?php echo number_format($total_gastos, 2); ?></span></div>
            <div><strong>Balance:</strong> <span style="color: #34495e; font-weight: bold;">S/ <?php echo number_format($balance, 2); ?></span></div>
        </div>
    </div>

    <div class="grid-container" style="grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- Movimientos -->
        <div class="card">
            <h3>🚗 Movimientos del Día</h3>
            <?php if (empty($movimientos)): ?>
                <p class="no-data">No se registraron movimientos.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Hora Entry</th>
                            <th>Placa</th>
                            <th>Movimiento</th>
                            <th>Pago</th>
                            <th>Monto</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimientos as $m): 
                            // Mostrar solo si hubo pago este día? 
                            // O mostrar todos? Mostraremos todos los activos ese día.
                            // Resaltaremos si hubo pago.
                            
                            $pago_texto = '-';
                            $monto_texto = '-';
                            $usuario_cobro = '-';

                            // Lógica visual para identificar si se pagó HOY en este movimiento
                            $es_pago_hoy = false;
                            
                            if ($m['momento_pago'] === 'Entrada' && date('Y-m-d', strtotime($m['fecha_hora_entrada'])) === $fecha) {
                                $es_pago_hoy = true;
                                $usuario_cobro = $m['personal_entrada'];
                            } elseif ($m['momento_pago'] === 'Salida' && $m['fecha_hora_salida'] && date('Y-m-d', strtotime($m['fecha_hora_salida'])) === $fecha) {
                                $es_pago_hoy = true;
                                $usuario_cobro = $m['personal_salida'];
                            }

                            if ($es_pago_hoy) {
                                $pago_texto = $m['metodo_pago'] . ' (' . $m['momento_pago'] . ')';
                                $monto_texto = 'S/ ' . number_format($m['precio_total'], 2);
                            }
                        ?>
                            <tr style="<?php echo $es_pago_hoy ? 'background-color: #f0fff4;' : ''; ?>">
                                <td><?php echo date('H:i', strtotime($m['fecha_hora_entrada'])); ?></td>
                                <td><?php echo htmlspecialchars($m['placa']); ?></td>
                                <td><?php echo $m['fecha_hora_salida'] ? 'Entrada/Salida' : 'Solo Entrada'; ?></td>
                                <td><?php echo $pago_texto; ?></td>
                                <td style="font-weight: bold;"><?php echo $monto_texto; ?></td>
                                <td style="font-size: 0.85rem;"><?php echo htmlspecialchars($usuario_cobro ?: '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <small class="text-muted">* Registros resaltados indican pago realizado en esta fecha.</small>
            <?php endif; ?>
        </div>

        <!-- Gastos -->
        <div class="card">
            <h3>💸 Gastos del Día</h3>
            <?php if (empty($gastos)): ?>
                <p class="no-data">No hay gastos.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Desc.</th>
                            <th style="text-align: right;">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gastos as $g): ?>
                            <tr>
                                <td><?php echo date('H:i', strtotime($g['fecha_hora'])); ?></td>
                                <td><?php echo htmlspecialchars($g['descripcion']); ?></td>
                                <td style="text-align: right; color: #e74c3c;">- S/ <?php echo number_format($g['monto'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
