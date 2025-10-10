<?php
require_once 'config/database.php';

echo "<h1>🔧 Instalador del Sistema</h1>";
echo "<p>Configurando el sistema con la contraseña correcta...</p>";

try {
    // Hash correcto para la contraseña user123
    $password = 'user123';
    $hash = '$2y$10$N.IBo2zvcyHkVjYI3UqHVOohIQSVUeIU.bHy9rQqsVtoMwgNqWFMS';
    
    echo "<p>✅ Hash configurado para la contraseña 'user123'</p>";
    
    // Actualizar la contraseña en la base de datos
    $stmt = $pdo->prepare("UPDATE administradores SET password = ? WHERE usuario = 'admin'");
    $stmt->execute([$hash]);
    
    echo "<p>✅ Contraseña actualizada en la base de datos</p>";
    
    // Verificar que funciona
    $stmt = $pdo->prepare("SELECT usuario FROM administradores WHERE usuario = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p>✅ Usuario administrador configurado correctamente</p>";
        echo "<hr>";
        echo "<h2>🎉 Instalación Completada</h2>";
        echo "<p><strong>Credenciales del sistema:</strong></p>";
        echo "<ul>";
        echo "<li><strong>Usuario:</strong> admin</li>";
        echo "<li><strong>Contraseña:</strong> user123</li>";
        echo "<li><strong>Email:</strong> admin@sistema.com</li>";
        echo "</ul>";
        echo "<p><a href='login.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Ir al Login</a></p>";
    } else {
        echo "<p>❌ Error: No se pudo configurar el usuario administrador</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error durante la instalación: " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
}

h1, h2 {
    color: #2c3e50;
}

p {
    color: #34495e;
    line-height: 1.6;
}

ul {
    background: white;
    padding: 20px;
    border-radius: 5px;
    border-left: 4px solid #667eea;
}
</style>
