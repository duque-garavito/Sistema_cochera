<?php
// Detectar base_url dinámicamente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Detectar carpeta base automáticamente
$script_name = $_SERVER['SCRIPT_NAME'];

// Lógica robusta para detectar base_url
if (strpos($script_name, '/public/index.php') !== false) {
    $base_url = str_replace('/public/index.php', '', $script_name);
} elseif (strpos($script_name, '/app/') !== false) {
    // Si se accede directamente a una vista en /app/
    $base_url = substr($script_name, 0, strpos($script_name, '/app/'));
} else {
    // Fallback para otros casos o raíz
    $base_url = '';
}

// Normalizar: eliminar trailing slash si existe, excepto si es solo '/'
if ($base_url !== '/' && substr($base_url, -1) === '/') {
    $base_url = rtrim($base_url, '/');
}
if ($base_url === '/') {
    $base_url = '';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Sistema de Control de Vehículos'; ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/css/style.css">
    <script>
    const BASE_URL = "<?php echo $base_url; ?>/public/index.php";
    </script>
</head>

<body>
    <div class="container">
        <header>
            <img src="<?php echo $base_url; ?>/img/logoooo.png" alt="logo" class="logo">
            <!--<h1>🏢 Sistema de Control de Vehículos</h1>-->
            <nav>
                <!-- Usamos rutas explícitas para asegurar compatibilidad sin router -->
                <a href="<?php echo $base_url; ?>/public/index.php/movimientos"
                    class="nav-link <?php echo ($current_page ?? '') === 'movimientos' ? 'active' : ''; ?>">Registro</a>
                <a href="<?php echo $base_url; ?>/public/index.php/dashboard"
                    class="nav-link <?php echo ($current_page ?? '') === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
                <a href="<?php echo $base_url; ?>/public/index.php/reportes"
                    class="nav-link <?php echo ($current_page ?? '') === 'reportes' ? 'active' : ''; ?>">Reportes</a>
                <a href="<?php echo $base_url; ?>/public/index.php/vehiculos"
                    class="nav-link <?php echo ($current_page ?? '') === 'vehiculos' ? 'active' : ''; ?>">Vehículos</a>
                
                <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
                    <a href="<?php echo $base_url; ?>/public/index.php/personal"
                    class="nav-link <?php echo ($current_page ?? '') === 'personal' ? 'active' : ''; ?>">Personal</a>
                    <a href="<?php echo $base_url; ?>/public/index.php/caja"
                        class="nav-link <?php echo ($current_page ?? '') === 'caja' ? 'active' : ''; ?>">Caja</a>
                    <a href="<?php echo $base_url; ?>/public/index.php/tarifas"
                        class="nav-link <?php echo ($current_page ?? '') === 'tarifas' ? 'active' : ''; ?>">Configurar</a>
                <?php endif; ?>
                <a href="<?php echo $base_url; ?>/public/index.php/logout" class="nav-link">Salir</a>
            </nav>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($_SESSION['nombre'] ?? ''); ?>
                    (<?php echo ucfirst($_SESSION['rol'] ?? ''); ?>)</span>
                <!-- DEBUG: Base URL: [<?php echo $base_url; ?>] Host: [<?php echo $host; ?>] -->
            </div>
        </header>

        <main>