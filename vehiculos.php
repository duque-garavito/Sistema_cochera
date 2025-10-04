<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Verificar login
verificarLogin();

$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario de registro de vehículo
if ($_POST && isset($_POST['accion']) && $_POST['accion'] == 'registrar_vehiculo') {
    $placa = strtoupper(trim($_POST['placa']));
    $tipo_vehiculo = $_POST['tipo_vehiculo'];
    $marca = trim($_POST['marca']);
    $modelo = trim($_POST['modelo']);
    $color = trim($_POST['color']);
    $dni = trim($_POST['dni']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    
    try {
        // Validaciones
        if (!validarPlaca($placa)) {
            throw new Exception("Formato de placa inválido. Use formato ABC-123 o ABC123");
        }
        
        if (!validarDNI($dni)) {
            throw new Exception("DNI debe tener 8 dígitos");
        }
        
        // Verificar si el vehículo ya existe
        if (buscarVehiculoPorPlaca($placa)) {
            throw new Exception("La placa ya está registrada");
        }
        
        // Verificar si el usuario existe
        $usuario = buscarUsuarioPorDNI($dni);
        if (!$usuario) {
            // Registrar nuevo usuario
            $usuario_id = registrarUsuario($dni, $nombre, $apellido, $telefono, $email);
        } else {
            $usuario_id = $usuario['id'];
        }
        
        // Obtener precio por tipo de vehículo
        $precio_por_dia = obtenerPrecioPorTipo($tipo_vehiculo);
        
        // Registrar vehículo con precio
        $vehiculo_id = registrarVehiculo($placa, $tipo_vehiculo, $marca, $modelo, $color, $usuario_id);
        
        // Actualizar precio en la base de datos
        $stmt = $pdo->prepare("UPDATE vehiculos SET precio_por_dia = ? WHERE id = ?");
        $stmt->execute([$precio_por_dia, $vehiculo_id]);
        
        $mensaje = "Vehículo registrado exitosamente - Precio: S/ " . number_format($precio_por_dia, 2) . " por día";
        $tipo_mensaje = "success";
        
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipo_mensaje = "error";
    }
}

// Obtener todos los vehículos registrados
$vehiculos = obtenerTodosLosVehiculos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vehículos - Sistema de Control</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🚗 Gestión de Vehículos</h1>
            <nav>
                <a href="index.php" class="nav-link">Registro</a>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="reportes.php" class="nav-link">Reportes</a>
                <a href="vehiculos.php" class="nav-link active">Vehículos</a>
                <a href="logout.php" class="nav-link">🚪 Salir</a>
            </nav>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($_SESSION['nombre']); ?> (<?php echo ucfirst($_SESSION['rol']); ?>)</span>
            </div>
        </header>

        <main>
            <?php if ($mensaje): ?>
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
                                <input type="text" id="placa" name="placa" required 
                                       placeholder="Ej: ABC123, AB-1234, 123ABC" maxlength="10">
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
                                <input type="text" id="marca" name="marca" 
                                       placeholder="Ej: Toyota, Honda, etc.">
                            </div>
                            
                            <div class="form-group">
                                <label for="modelo">Modelo:</label>
                                <input type="text" id="modelo" name="modelo" 
                                       placeholder="Ej: Corolla, CB190R, etc.">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="color">Color:</label>
                            <input type="text" id="color" name="color" 
                                   placeholder="Ej: Blanco, Rojo, Negro, etc.">
                        </div>

                        <hr class="form-divider">
                        <h3>👤 Datos del Propietario</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="dni">DNI:</label>
                                <input type="text" id="dni" name="dni" required 
                                       placeholder="12345678" maxlength="8" pattern="[0-9]{8}">
                            </div>
                            
                            <div class="form-group">
                                <label for="nombre">Nombre:</label>
                                <input type="text" id="nombre" name="nombre" required 
                                       placeholder="Nombre del propietario">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="apellido">Apellido:</label>
                                <input type="text" id="apellido" name="apellido" required 
                                       placeholder="Apellido del propietario">
                            </div>
                            
                            <div class="form-group">
                                <label for="telefono">Teléfono:</label>
                                <input type="tel" id="telefono" name="telefono" 
                                       placeholder="987654321">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" 
                                   placeholder="usuario@email.com">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            🚗 Registrar Vehículo
                        </button>
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
                                        <span class="placa"><?php echo htmlspecialchars($vehiculo['placa']); ?></span>
                                        <span class="tipo"><?php echo htmlspecialchars($vehiculo['tipo_vehiculo']); ?></span>
                                    </div>
                                    <div class="vehiculo-info">
                                        <p><strong>Propietario:</strong> <?php echo htmlspecialchars($vehiculo['nombre'] . ' ' . $vehiculo['apellido']); ?></p>
                                        <p><strong>DNI:</strong> <?php echo htmlspecialchars($vehiculo['dni']); ?></p>
                                        <?php if ($vehiculo['marca'] && $vehiculo['modelo']): ?>
                                            <p><strong>Vehículo:</strong> <?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></p>
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
        </main>

        <footer>
            <p>&copy; 2024 Sistema de Control de Vehículos. Todos los derechos reservados.</p>
        </footer>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
