<?php
session_start();
require_once 'config/database.php';


$mensaje = '';
$tipo_mensaje = '';
$token_valido = false;
$admin_id = null;

// Verificar token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM administradores WHERE reset_token = ? AND reset_token_expires > NOW() AND activo = 1");
        $stmt->execute([$token]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            $token_valido = true;
            $admin_id = $admin['id'];
        } else {
            $mensaje = "El enlace de recuperación es inválido o ha expirado.";
            $tipo_mensaje = "error";
        }
    } catch (Exception $e) {
        $mensaje = "Error en el sistema.";
        $tipo_mensaje = "error";
    }
} else {
    $mensaje = "Token de recuperación no proporcionado.";
    $tipo_mensaje = "error";
}

// Procesar cambio de contraseña
if ($_POST && $token_valido) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo_mensaje = "error";
    } elseif (strlen($password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipo_mensaje = "error";
    } else {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Actualizar contraseña y limpiar token
            $stmt = $pdo->prepare("UPDATE administradores SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
            $stmt->execute([$password_hash, $admin_id]);
            
            $mensaje = "Contraseña cambiada exitosamente. Puede iniciar sesión con la nueva contraseña.";
            $tipo_mensaje = "success";
            $token_valido = false; // Ya no mostrar el formulario
            
        } catch (Exception $e) {
            $mensaje = "Error al cambiar la contraseña. Intente nuevamente.";
            $tipo_mensaje = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - Sistema de Control</title>
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
        
        .change-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 450px;
        }
        
        .change-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .change-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .change-header p {
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
        
        .btn-change {
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
        
        .btn-change:hover {
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
        
        .password-requirements {
            background: rgba(241, 196, 15, 0.1);
            border: 1px solid rgba(241, 196, 15, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .password-requirements h4 {
            margin: 0 0 10px 0;
            color: #f39c12;
            font-size: 1rem;
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
            color: #2c3e50;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="change-container">
        <div class="change-header">
            <h1>🔐 Cambiar Contraseña</h1>
            <p>Ingrese su nueva contraseña</p>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <?php if ($token_valido): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="password">Nueva Contraseña:</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Ingrese su nueva contraseña" minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Confirme su nueva contraseña" minlength="6">
                </div>

                <button type="submit" class="btn-change">
                    🔑 Cambiar Contraseña
                </button>
            </form>

            <div class="password-requirements">
                <h4>📋 Requisitos de la contraseña:</h4>
                <ul>
                    <li>Mínimo 6 caracteres</li>
                    <li>Recomendado: usar letras y números</li>
                </ul>
            </div>
        <?php endif; ?>

        <a href="login.php" class="btn-back">
            ← Volver al Login
        </a>
    </div>

    <script>
        // Validar que las contraseñas coincidan en tiempo real
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
