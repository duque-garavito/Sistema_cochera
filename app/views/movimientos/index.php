<?php
$title = "Registro de Movimientos";
$current_page = 'movimientos';
require __DIR__ . '/../layouts/header.php';
?>

<?php if (!empty($mensaje)): ?>
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
                    <input type="text" id="placa" name="placa" required placeholder="Ej: ABC123, AB-1234"
                        maxlength="10">
                </div>
                <div class="form-group">
                    <label for="dni">DNI del Conductor:</label>
                    <input type="text" id="dni" name="dni" required placeholder="Ej: 12345678" maxlength="8">
                </div>
            </div>

            <!-- Sugerencias -->
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
                    <input type="text" id="precio_dia" readonly placeholder="Se selecciona automáticamente">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="metodo_pago">Método de Pago:</label>
                    <select id="metodo_pago" name="metodo_pago">
                        <option value="Efectivo">💵 Efectivo</option>
                        <option value="Yape">📱 Yape (Billetera)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="momento_pago">Momento de Pago:</label>
                    <select id="momento_pago" name="momento_pago">
                        <option value="Salida">🚪 Pagar a la Salida (Por defecto)</option>
                        <option value="Entrada">🚗 Pagar al Ingreso</option>
                    </select>
                    <small style="color: #7f8c8d;">Si es salida, se cobrará ahora.</small>
                </div>
            </div>

            <!-- Info del Vehículo -->
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

            <button type="submit" class="btn btn-primary">📋 Registrar Movimiento</button>
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
                <div class="activo-info">
                    <p><strong>Conductor:</strong>
                        <?php echo htmlspecialchars($activo['nombre'] . ' ' . $activo['apellido']); ?></p>
                    <p class="placa"><strong>Placa:</strong> <?php echo htmlspecialchars($activo['placa']); ?></p>
                    <p class="tipo"><strong>Tipo de
                            vehiculo:</strong><?php echo htmlspecialchars($activo['tipo_vehiculo']); ?></p>
                    <p><strong>Entrada:</strong>
                        <?php echo date('d/m/Y H:i', strtotime($activo['fecha_hora_entrada'])); ?></p>
                    <p><strong>Precio/Día:</strong> S/ <?php echo number_format($activo['precio_por_dia'], 2); ?></p>
                    <?php if (!empty($activo['personal_nombre'])): ?>
                        <p style="font-size: 0.85rem; color: #7f8c8d; margin-top: 5px;">
                            <small>👤 Registrado por: <?php echo htmlspecialchars($activo['personal_nombre']); ?></small>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($activo['observaciones'])): ?>
                    <p><strong>Obs:</strong> <?php echo htmlspecialchars($activo['observaciones']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>