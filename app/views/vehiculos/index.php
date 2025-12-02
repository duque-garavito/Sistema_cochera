<?php 
$title = "Gestión de Vehículos";
$current_page = 'vehiculos';
require __DIR__ . '/../layouts/header.php'; 
?>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?>">
        <?php echo $mensaje; ?>
    </div>
<?php endif; ?>

<div class="grid-container">
    <!-- Formulario de Registro de Vehículo -->
    <section class="card">
        <h2>📝 Registrar Nuevo Vehículo</h2>
        <form method="POST" class="form">
            <input type="hidden" name="accion" value="registrar_vehiculo">

            <div class="form-row">
                <div class="form-group">
                    <label for="placa">Placa del Vehículo:</label>
                    <input type="text" id="placa" name="placa" required placeholder="Ej: ABC123, AB-1234, 123ABC" maxlength="10">
                </div>
                <div class="form-group">
                    <label for="tipo_vehiculo">Tipo de Vehículo:</label>
                    <select id="tipo_vehiculo" name="tipo_vehiculo" required>
                        <option value="">Seleccionar...</option>
                        <option value="Auto">🚗 Auto</option>
                        <option value="Moto">🏍️ Moto</option>
                        <option value="Camioneta">🚛 Camioneta</option>
                        <option value="Otro">🚙 Otro</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="marca">Marca:</label>
                    <input type="text" id="marca" name="marca" placeholder="Ej: Toyota, Honda, etc.">
                </div>
                <div class="form-group">
                    <label for="modelo">Modelo:</label>
                    <input type="text" id="modelo" name="modelo" placeholder="Ej: Corolla, CB190R, etc.">
                </div>
            </div>

            <div class="form-group">
                <label for="color">Color:</label>
                <input type="text" id="color" name="color" placeholder="Ej: Blanco, Rojo, Negro, etc.">
            </div>

            <hr class="form-divider">
            <h3>👤 Datos del Propietario</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="dni">DNI:</label>
                    <input type="text" id="dni" name="dni" required placeholder="12345678" maxlength="8" pattern="[0-9]{8}" title="Ingrese 8 dígitos para autocompletar desde la API">
                    <small style="color: #7f8c8d; font-size: 0.85rem;">💡 Al ingresar 8 dígitos, se consultará automáticamente la API</small>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Nombre del propietario">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" required placeholder="Apellido del propietario">
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" placeholder="987654321">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">🚗 Registrar Vehículo</button>
        </form>
    </section>

    <!-- Lista de Vehículos Registrados -->
    <section class="card">
        <h2>📋 Vehículos Registrados</h2>
        <div class="vehiculos-container">
            <?php if (empty($vehiculos)): ?>
                <p class="no-data">No hay vehículos registrados</p>
            <?php else: ?>
                <?php foreach ($vehiculos as $vehiculo): ?>
                    <div class="vehiculo-item">
                        <div class="vehiculo-header">
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
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
