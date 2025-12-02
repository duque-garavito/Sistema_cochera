<?php 
$title = "Reportes";
$current_page = 'reportes';
require __DIR__ . '/../layouts/header.php'; 
?>

<div class="card">
    <h2>📊 Generar Reporte de Movimientos</h2>
    <form method="POST" class="form">
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
                            <td style="padding: 12px; text-align: right; font-weight: bold;">
                                S/ <?php echo number_format($mov['precio_total'] ?? 0, 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background: #f8f9fa; font-weight: bold;">
                        <td colspan="5" style="padding: 12px; text-align: right;">TOTAL:</td>
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
