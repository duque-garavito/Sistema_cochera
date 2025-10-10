<?php
require_once 'config/database.php';

// 👇 Cambia estos datos si quieres personalizar el admin
$usuario = "admin";
$passwordPlano = "user123";
$nombre = "Administrador Principal";
$email = "admin@cochera.com";
$rol = "admin";

// Generar hash seguro
$hash = password_hash($passwordPlano, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("INSERT INTO administradores (usuario, password, nombre, email, rol, activo, fecha_registro) 
                           VALUES (:usuario, :password, :nombre, :email, :rol, 1, NOW())");
    $stmt->execute([
        'usuario' => $usuario,
        'password' => $hash,
        'nombre' => $nombre,
        'email' => $email,
        'rol' => $rol
    ]);

    echo "✅ Usuario administrador creado correctamente<br>";
    echo "👉 Usuario: <strong>$usuario</strong><br>";
    echo "👉 Contraseña: <strong>$passwordPlano</strong><br>";
    echo "⚠️ Recuerda borrar este archivo después de usarlo por seguridad.";
} catch (PDOException $e) {
    echo "❌ Error al crear el administrador: " . $e->getMessage();
}
