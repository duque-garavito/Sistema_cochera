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
                        <?php if (!empty($tarifas)): ?>
                            <?php foreach ($tarifas as $tarifa): ?>
                                <option value="<?php echo htmlspecialchars($tarifa['tipo_vehiculo']); ?>">
                                    <?php echo htmlspecialchars($tarifa['tipo_vehiculo']); ?> - S/ <?php echo number_format($tarifa['precio_base'], 2); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="Auto">Auto</option>
                            <option value="Moto">Moto</option>
                            <option value="Camioneta">Camioneta</option>
                            <option value="Otro">Otro</option>
                        <?php endif; ?>
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h2 style="margin: 0;">📋 Vehículos Registrados</h2>
            <div style="position: relative;">
                <input type="text" id="filtro_vehiculo" placeholder="🔍 Buscar por Placa o DNI..." 
                       style="padding: 8px 15px; border-radius: 20px; border: 1px solid #ddd; width: 250px;">
            </div>
        </div>
        <div class="vehiculos-container">
            <?php if (empty($vehiculos)): ?>
                <p class="no-data">No hay vehículos registrados</p>
            <?php else: ?>
                <?php require __DIR__ . '/lista_partial.php'; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
