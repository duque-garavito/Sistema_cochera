<?php if (empty($vehiculos)): ?>
    <p class="no-data">No hay vehículos registrados</p>
<?php else: ?>
    <?php foreach ($vehiculos as $vehiculo): ?>
        <div class="vehiculo-item" style="<?php echo $vehiculo['en_lista_negra'] ? 'border: 2px solid #e74c3c; background-color: #fff5f5;' : ''; ?>">
            <div class="vehiculo-header">
                <?php if ($vehiculo['en_lista_negra']): ?>
                    <div style="background: #e74c3c; color: white; padding: 5px; text-align: center; border-radius: 4px; margin-bottom: 10px; font-weight: bold;">
                        🚫 EN LISTA NEGRA
                    </div>
                <?php endif; ?>
               <p><strong>Propietario:</strong> <?php echo htmlspecialchars($vehiculo['nombre'] . ' ' . $vehiculo['apellido']); ?></p>
                <p><strong>DNI:</strong> <?php echo htmlspecialchars($vehiculo['dni']); ?></p>
                 <span class="placa"><strong>Placa:</strong> <?php echo htmlspecialchars($vehiculo['placa']); ?></span><br>
                <span class="tipo"><strong>Tipo:</strong><?php echo htmlspecialchars($vehiculo['tipo_vehiculo']); ?></span><br>
                <?php if ($vehiculo['marca'] && $vehiculo['modelo']): ?>
                    <p><strong>Modelo:</strong> <?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></p>
                <?php endif; ?>
                
                <?php if ($vehiculo['color']): ?>
                    <p><strong>Color:</strong> <?php echo htmlspecialchars($vehiculo['color']); ?></p>
                <?php endif; ?>
                
                <form method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="accion" value="alternar_bloqueo">
                    <input type="hidden" name="vehiculo_id" value="<?php echo $vehiculo['id']; ?>">
                    <input type="hidden" name="nuevo_estado" value="<?php echo $vehiculo['en_lista_negra'] ? '0' : '1'; ?>">
                    <button type="submit" class="btn btn-sm" style="width: 100%; background-color: <?php echo $vehiculo['en_lista_negra'] ? '#27ae60' : '#e74c3c'; ?>; color: white;">
                        <?php echo $vehiculo['en_lista_negra'] ? '✅ Desbloquear' : '🚫 Bloquear'; ?>
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
