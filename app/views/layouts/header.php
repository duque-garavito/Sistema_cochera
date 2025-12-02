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
    <title><?php echo $title ?? 'Sistema de Control de Vehículos'; ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏢 Sistema de Control de Vehículos</h1>
            <nav>
                <!-- Usamos rutas explícitas para asegurar compatibilidad sin router -->
                <a href="<?php echo $base_url; ?>/public/index.php/movimientos" class="nav-link <?php echo ($current_page ?? '') === 'movimientos' ? 'active' : ''; ?>">Registro</a>
                <a href="<?php echo $base_url; ?>/public/index.php/dashboard" class="nav-link <?php echo ($current_page ?? '') === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
                <a href="<?php echo $base_url; ?>/public/index.php/reportes" class="nav-link <?php echo ($current_page ?? '') === 'reportes' ? 'active' : ''; ?>">Reportes</a>
                <a href="<?php echo $base_url; ?>/public/index.php/vehiculos" class="nav-link <?php echo ($current_page ?? '') === 'vehiculos' ? 'active' : ''; ?>">Vehículos</a>
                <a href="<?php echo $base_url; ?>/public/index.php/logout" class="nav-link">🚪 Salir</a>
            </nav>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($_SESSION['nombre'] ?? ''); ?> (<?php echo ucfirst($_SESSION['rol'] ?? ''); ?>)</span>
                <!-- DEBUG: Base URL: [<?php echo $base_url; ?>] Host: [<?php echo $host; ?>] -->
            </div>
        </header>

        <main>
