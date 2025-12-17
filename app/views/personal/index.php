<?php 
$title = "Gestión de Personal";
$current_page = 'personal';
// Ajuste de ruta para header si estamos en subcarpeta
require __DIR__ . '/../layouts/header.php'; 
?>

<div class="container" style="max-width: 1000px; margin-top: 2rem;">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>👥 Gestión de Personal</h2>
            <a href="<?php echo $base_url; ?>/public/index.php/personal/crear" class="btn btn-primary">➕ Nuevo Personal</a>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($personal)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay personal registrado</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($personal as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($p['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($p['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $p['rol'] === 'admin' ? 'badge-primary' : 'badge-success'; ?>">
                                        <?php echo ucfirst($p['rol']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $p['activo'] ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo $p['activo'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($p['id'] != $_SESSION['usuario_id']): ?>
                                        <form method="POST" onsubmit="return confirm('¿Está seguro de eliminar este usuario?');" style="display: inline;">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">🗑️ Eliminar</button>
                                        </form>
                                    <?php else: ?>
                                        <small class="text-muted">(Tú)</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
