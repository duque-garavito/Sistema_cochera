<?php
session_start();
require_once 'config/database.php';

$error = '';

// Si ya está logueado, redirigir
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

// Procesar formulario de login
if ($_POST) {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];
    
    if (!empty($usuario) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM administradores WHERE usuario = ? AND activo = 1");
            $stmt->execute([$usuario]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Login exitoso
                $_SESSION['usuario_id'] = $admin['id'];
                $_SESSION['usuario'] = $admin['usuario'];
                $_SESSION['nombre'] = $admin['nombre'];
                $_SESSION['rol'] = $admin['rol'];
                
                // Actualizar último acceso
                $stmt = $pdo->prepare("UPDATE administradores SET ultimo_acceso = NOW() WHERE id = ?");
                $stmt->execute([$admin['id']]);
                
                header('Location: index.php');
                exit();
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        } catch (Exception $e) {
            $error = 'Error en el sistema. Intente nuevamente.';
        }
    } else {
        $error = 'Por favor complete todos los campos';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Control de Vehículos</title>
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
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .login-header p {
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
        
        .btn-login {
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
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .error {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
            text-align: center;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e6ed;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .demo-credentials {
            background: rgba(52, 152, 219, 0.1);
            border: 1px solid rgba(52, 152, 219, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
        }
        
        .demo-credentials h4 {
            margin: 0 0 10px 0;
            color: #3498db;
            font-size: 1rem;
        }
        
        .demo-credentials p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🚗 Sistema de Control</h1>
            <p>Inicie sesión para continuar</p>
        </div>

        <?php if ($error): ?>
            <div class="error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required 
                       placeholder="Ingrese su usuario" value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Ingrese su contraseña">
            </div>

            <button type="submit" class="btn-login">
                🔐 Iniciar Sesión
            </button>
        </form>

        <div class="demo-credentials">
            <h4>Credenciales de Prueba:</h4>
            <p><strong>Admin:</strong> usuario: admin, contraseña: admin123</p>
            <p><strong>Operador:</strong> usuario: operador, contraseña: admin123</p>
        </div>

        <div class="login-footer">
            <p>&copy; 2025 Sistema de Control de Vehículos</p>
        </div>
    </div>
</body>
</html>
