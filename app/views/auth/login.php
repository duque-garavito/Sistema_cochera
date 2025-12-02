<?php
// Detectar base_url dinámicamente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Si estamos en un servidor local con puerto (ej: localhost:3000, localhost:8080)
if (preg_match('/^localhost:\d+$/', $host)) {
    $base_url = ''; // En la raíz
} else {
    // Si estamos en XAMPP (localhost/Sistema_cochera)
    $base_url = '/Sistema_cochera';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Control</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <h1>🏢 Sistema de Control de Vehículos</h1>
            <h2>Iniciar Sesión</h2>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo $base_url; ?>/public/index.php/login" class="login-form">
                <div class="form-group">
                    <label for="usuario">Usuario:</label>
                    <input type="text" id="usuario" name="usuario" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary">🔐 Iniciar Sesión</button>
            </form>
        </div>
    </div>
</body>
</html>
