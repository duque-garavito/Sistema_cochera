<?php
$title = "Configuración de Tarifas";
$current_page = 'tarifas';
require __DIR__ . '/../layouts/header.php';
?>

<div class="header-content" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h1>💰 Configuración de Tarifas</h1>
    <button class="btn btn-primary" onclick="abrirModal()">
        <i class="fas fa-plus"></i> Nueva Tarifa
    </button>
</div>

<?php if (isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?php echo $_SESSION['tipo_mensaje'] === 'error' ? 'danger' : 'success'; ?>">
        <?php 
            echo $_SESSION['mensaje']; 
            unset($_SESSION['mensaje']);
            unset($_SESSION['tipo_mensaje']);
        ?>
    </div>
<?php endif; ?>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Tipo de Vehículo</th>
                <th>Precio Base (S/)</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($tarifas)): ?>
                <?php foreach ($tarifas as $tarifa): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tarifa['tipo_vehiculo']); ?></td>
                        <td>S/ <?php echo number_format($tarifa['precio_base'], 2); ?></td>
                        <td>
                            <span class="badge badge-success"><?php echo $tarifa['estado']; ?></span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="editarTarifa(<?php echo htmlspecialchars(json_encode($tarifa)); ?>)">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <a href="/tarifas/eliminar?id=<?php echo $tarifa['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta tarifa?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No hay tarifas registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Tarifa -->
<div id="modalTarifa" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div class="modal-content" style="background: white; width: 400px; margin: 10% auto; padding: 25px; border-radius: 8px; position: relative;">
        <h2 id="modalTitle" style="margin-top: 0;">Nueva Tarifa</h2>
        
        <form action="/tarifas/guardar" method="POST">
            <input type="hidden" id="tarifa_id" name="id">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="tipo_vehiculo">Tipo de Vehículo</label>
                <input type="text" id="tipo_vehiculo" name="tipo_vehiculo" required class="form-control" placeholder="Ej. Auto, Moto">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="precio_base">Precio por Día (S/)</label>
                <input type="number" id="precio_base" name="precio_base" step="0.50" min="0" required class="form-control">
            </div>
            
            <div style="text-align: right;">
                <button type="button" class="btn btn-outline-secondary" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModal() {
    document.getElementById('modalTarifa').style.display = 'block';
    document.getElementById('modalTitle').textContent = 'Nueva Tarifa';
    document.getElementById('tarifa_id').value = '';
    document.getElementById('tipo_vehiculo').value = '';
    document.getElementById('precio_base').value = '';
}

function editarTarifa(tarifa) {
    document.getElementById('modalTarifa').style.display = 'block';
    document.getElementById('modalTitle').textContent = 'Editar Tarifa';
    document.getElementById('tarifa_id').value = tarifa.id;
    document.getElementById('tipo_vehiculo').value = tarifa.tipo_vehiculo;
    document.getElementById('precio_base').value = tarifa.precio_base;
}

function cerrarModal() {
    document.getElementById('modalTarifa').style.display = 'none';
}

// Cerrar al hacer clic fuera
window.onclick = function(event) {
    var modal = document.getElementById('modalTarifa');
    if (event.target == modal) {
        cerrarModal();
    }
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
