<?php 
$title = "Registrar Personal";
$current_page = 'personal';
require __DIR__ . '/../layouts/header.php'; 
?>

<div class="container" style="max-width: 600px; margin-top: 2rem;">
    <div class="card">
        <h2>➕ Registrar Nuevo Personal</h2>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="form">
            <div class="form-group">
                <label for="nombre">Nombre Completo:</label>
                <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan Pérez">
            </div>

            <div class="form-group">
                <label for="email">Email (Opcional):</label>
                <input type="email" id="email" name="email" placeholder="juan@ejemplo.com">
            </div>

            <div class="form-group">
                <label for="usuario">Usuario (Login):</label>
                <input type="text" id="usuario" name="usuario" required placeholder="Usuario para iniciar sesión">
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required placeholder="********">
            </div>

            <div class="form-group">
                <label for="rol">Rol:</label>
                <select id="rol" name="rol" required>
                    <option value="operador">👷 Operador (Registra entradas/salidas)</option>
                    <option value="admin">🔑 Administrador (Acceso total)</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Guardar Personal</button>
                <a href="<?php echo $base_url; ?>/public/index.php/personal" class="btn btn-secondary" style="text-decoration: none;">🔙 Volver</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
