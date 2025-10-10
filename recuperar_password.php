<?php
session_start();
require_once 'config/database.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar solicitud de recuperación
if ($_POST && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM administradores WHERE email = ? AND activo = 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            // Generar token de recuperación
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Guardar token en la base de datos
            $stmt = $pdo->prepare("UPDATE administradores SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $admin['id']]);
            
            // Enviar correo (simulado por ahora)
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/cambiar_password.php?token=" . $token;
            
            // Aquí iría el envío real del correo
            // Por ahora, mostramos el enlace en pantalla para desarrollo
            $mensaje = "Enlace de recuperación generado. Para desarrollo: <br><a href='" . $reset_link . "'>" . $reset_link . "</a>";
            $tipo_mensaje = "success";
            
        } else {
            $mensaje = "No se encontró una cuenta con ese correo electrónico.";
            $tipo_mensaje = "error";
        }
    } catch (Exception $e) {
        $mensaje = "Error en el sistema. Intente nuevamente.";
        $tipo_mensaje = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Sistema de Control</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        
        .recovery-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 450px;
        }
        
        .recovery-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .recovery-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .recovery-header p {
            color: #7f8c8d;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-recovery {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-recovery:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-back {
            width: 100%;
            padding: 12px;
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-top: 15px;
        }
        
        .btn-back:hover {
            background: #667eea;
            color: white;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.1);
            color: #27ae60;
            border-left: 4px solid #27ae60;
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }
        
        .info-box {
            background: rgba(52, 152, 219, 0.1);
            border: 1px solid rgba(52, 152, 219, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
        }
        
        .info-box h4 {
            margin: 0 0 10px 0;
            color: #3498db;
            font-size: 1rem;
        }
        
        .info-box p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="recovery-header">
            <h1>🔑 Recuperar Contraseña</h1>
            <p>Ingrese su correo electrónico para recibir un enlace de recuperación</p>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required 
                       placeholder="admin@sistema.com" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <button type="submit" class="btn-recovery">
                📧 Enviar Enlace de Recuperación
            </button>
        </form>

        <a href="login.php" class="btn-back">
            ← Volver al Login
        </a>

        <div class="info-box">
            <h4>💡 Información</h4>
            <p><strong>Email del administrador:</strong> admin@sistema.com</p>
            <p><strong>Usuario:</strong> admin</p>
            <p><strong>Contraseña actual:</strong> user123</p>
        </div>
    </div>
</body>
</html>
