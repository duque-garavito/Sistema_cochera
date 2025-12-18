<?php 
$title = "Reportes";
$current_page = 'reportes';
require __DIR__ . '/../layouts/header.php'; 
// Inicializar variables para prevenir warnings
$fecha_inicio = $fecha_inicio ?? date('Y-m-d');
$fecha_fin = $fecha_fin ?? date('Y-m-d');
?>

<div class="card">
    <h2>📊 Generar Reporte de Movimientos</h2>
    <form method="POST" action="<?php echo $base_url; ?>/public/index.php/reportes" class="form">
        <div class="form-row">
            <div class="form-group">
                <label for="fecha_inicio">Fecha Inicio:</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" required>
            </div>
            <div class="form-group">
                <label for="fecha_fin">Fecha Fin:</label>
                <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">🔍 Generar Reporte</button>
    </form>
</div>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
<div class="card">
    <h2>📋 Resultados del Reporte</h2>
    <p><strong>Período:</strong> <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> - <?php echo date('d/m/Y', strtotime($fecha_fin)); ?></p>
    
    <?php if (empty($movimientos)): ?>
        <p class="no-data">No hay movimientos en el período seleccionado</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Placa</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Tipo</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Propietario</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Entrada</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Salida</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Momento Pago</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Método</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Reg. Entrada</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Reg. Salida</th>
                        <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_ingresos = 0;
                    foreach ($movimientos as $mov): 
                        $total_ingresos += $mov['precio_total'] ?? 0;
                    ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 12px;"><?php echo htmlspecialchars($mov['placa']); ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($mov['tipo_vehiculo']); ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($mov['nombre'] . ' ' . $mov['apellido']); ?></td>
                            <td style="padding: 12px;"><?php echo date('d/m/Y H:i', strtotime($mov['fecha_hora_entrada'])); ?></td>
                            <td style="padding: 12px;">
                                <?php echo $mov['fecha_hora_salida'] ? date('d/m/Y H:i', strtotime($mov['fecha_hora_salida'])) : '-'; ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php 
                                    $momento = $mov['momento_pago'] ?? '-';
                                    $color = $momento === 'Entrada' ? '#2ecc71' : ($momento === 'Salida' ? '#e74c3c' : '#bdc3c7');
                                    // Iconos: Entrada -> 🚗, Salida -> 🚪
                                    $icono = $momento === 'Entrada' ? '🚗' : ($momento === 'Salida' ? '🚪' : '');
                                ?>
                                <span style="color: <?php echo $color; ?>; font-weight: bold;">
                                    <?php echo $icono . ' ' . $momento; ?>
                                </span>
                            </td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($mov['metodo_pago'] ?? '-'); ?></td>
                            <td style="padding: 12px; font-size: 0.9em;"><?php echo htmlspecialchars($mov['personal_entrada'] ?? '-'); ?></td>
                            <td style="padding: 12px; font-size: 0.9em;"><?php echo htmlspecialchars($mov['personal_salida'] ?? '-'); ?></td>
                            <td style="padding: 12px; text-align: right; font-weight: bold;">
                                S/ <?php echo number_format($mov['precio_total'] ?? 0, 2); ?>
                                <?php 
                                    // Detectar si hubo recargo (Feriado) comparando con precio base
                                    $precio_base = 0;
                                    switch($mov['tipo_vehiculo']) {
                                        case 'Moto': $precio_base = 4.00; break;
                                        case 'Auto': $precio_base = 10.00; break;
                                        case 'Camioneta': $precio_base = 15.00; break;
                                        default: $precio_base = 20.00; break; // Otro
                                    }
                                    
                                    $total_actual = floatval($mov['precio_total'] ?? 0);
                                    if ($total_actual > $precio_base) {
                                        $diferencia = $total_actual - $precio_base;
                                        echo '<br><small style="color: #e74c3c; font-weight: normal;">+ S/ ' . number_format($diferencia, 2) . ' (Feriado)</small>';
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background: #f8f9fa; font-weight: bold;">
                        <td colspan="9" style="padding: 12px; text-align: right;">TOTAL:</td>
                        <td style="padding: 12px; text-align: right;">S/ <?php echo number_format($total_ingresos, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div style="margin-top: 20px; text-align: center;">
            <button onclick="window.print()" class="btn btn-primary" style="margin-right: 10px;">🖨️ Imprimir</button>
            <button onclick="exportarCSV()" class="btn btn-secondary">📥 Exportar CSV</button>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
