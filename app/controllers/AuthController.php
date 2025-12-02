<?php

require_once __DIR__ . '/../models/Usuario.php';

/**
 * AuthController
 * Maneja autenticación (login, logout, recuperar password)
 */
class AuthController
{
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function login()
    {
        session_start();

        // Detectar base_url dinámicamente
        $host = $_SERVER['HTTP_HOST'];
        if (preg_match('/^localhost:\d+$/', $host)) {
            $base_url = ''; // En la raíz
        } else {
            $base_url = '/Sistema_cochera';
        }

        if (isset($_SESSION['usuario_id'])) {
            header('Location: ' . $base_url . '/public/index.php/dashboard');
            exit();
        }

        $mensaje = '';
        $tipo_mensaje = '';

        $logFile = __DIR__ . '/../../debug_login.txt';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = trim($_POST['usuario'] ?? '');
            $password = $_POST['password'] ?? '';
            
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Login attempt for user: " . $usuario . "\n", FILE_APPEND);

            try {
                $admin = $this->usuarioModel->autenticar($usuario, $password);

                if ($admin) {
                    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Authentication successful for user: " . $usuario . "\n", FILE_APPEND);
                    $_SESSION['usuario_id'] = $admin['id'];
                    $_SESSION['nombre'] = $admin['nombre'];
                    $_SESSION['usuario'] = $admin['usuario'];
                    $_SESSION['rol'] = $admin['rol'];

                    $redirectUrl = $base_url . '/public/index.php/dashboard';
                    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Redirecting to: " . $redirectUrl . "\n", FILE_APPEND);
                    header('Location: ' . $redirectUrl);
                    exit();
                } else {
                    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Authentication failed for user: " . $usuario . "\n", FILE_APPEND);
                    $mensaje = 'Usuario o contraseña incorrectos';
                    $tipo_mensaje = 'error';
                }
            } catch (Exception $e) {
                file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Login error: " . $e->getMessage() . "\n", FILE_APPEND);
                $mensaje = 'Error: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
        } else {
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Login page accessed via GET\n", FILE_APPEND);
        }

        require __DIR__ . '/../views/auth/login.php';
    }

    public function logout()
    {
        session_start();
        session_destroy();
        
        // Detectar base_url dinámicamente
        $host = $_SERVER['HTTP_HOST'];
        if (preg_match('/^localhost:\d+$/', $host)) {
            $base_url = ''; // En la raíz
        } else {
            $base_url = '/Sistema_cochera';
        }
        
        header('Location: ' . $base_url . '/public/index.php/login');
        exit();
    }
}
