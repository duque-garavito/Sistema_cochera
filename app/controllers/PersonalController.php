<?php

require_once __DIR__ . '/../models/Personal.php';

class PersonalController
{
    private $personalModel;

    public function __construct()
    {
        $this->personalModel = new Personal();
    }

    public function index()
    {
        session_start();
        $this->verificarSesion();

        $mensaje = '';
        $tipo_mensaje = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
            try {
                if ($_POST['id'] == $_SESSION['usuario_id']) {
                    throw new Exception("No puedes eliminar tu propia cuenta.");
                }
                $this->personalModel->eliminar($_POST['id']);
                $mensaje = "Personal eliminado correctamente.";
                $tipo_mensaje = "success";
            } catch (Exception $e) {
                $mensaje = $e->getMessage();
                $tipo_mensaje = "error";
            }
        }

        $personal = $this->personalModel->obtenerTodos();
        require __DIR__ . '/../views/personal/index.php';
    }

    public function crear()
    {
        session_start();
        $this->verificarSesion();

        $mensaje = '';
        $tipo_mensaje = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nombre = trim($_POST['nombre']);
                $usuario = trim($_POST['usuario']);
                $password = $_POST['password'];
                $rol = $_POST['rol'];
                $email = trim($_POST['email'] ?? '');

                $this->personalModel->crear($nombre, $usuario, $password, $rol, $email);
                
                // Redireccionar al index con mensaje de éxito (usando parámetro GET o sesión flash si tuviéramos)
                // Por simplicidad, volvemos a cargar la vista con mensaje
                $mensaje = "Personal registrado exitosamente.";
                $tipo_mensaje = "success";
                
                // Limpiar campos
                $_POST = [];
            } catch (Exception $e) {
                $mensaje = $e->getMessage();
                $tipo_mensaje = "error";
            }
        }

        require __DIR__ . '/../views/personal/crear.php';
    }

    private function verificarSesion()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login');
            exit();
        }
        // Solo admins pueden gestionar personal?
        /* if ($_SESSION['rol'] !== 'admin') {
             die("Acceso denegado");
        } */
    }
}
