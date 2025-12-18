<?php

require_once __DIR__ . '/../models/Tarifa.php';

class TarifaController
{
    private $tarifaModel;

    public function __construct()
    {
        $this->tarifaModel = new Tarifa();
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar sesión
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login');
            exit();
        }

        try {
            $tarifas = $this->tarifaModel->obtenerTodas();
        } catch (Exception $e) {
            $error = $e->getMessage();
            $tarifas = [];
        }

        require __DIR__ . '/../views/tarifas/index.php';
    }

    public function guardar()
    {
        file_put_contents('debug_log.txt', "Entro a guardar\n", FILE_APPEND);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            file_put_contents('debug_log.txt', "Es POST\n", FILE_APPEND);
            try {
                $chk_id = $_POST['id'] ?? 'NULL';
                $chk_tipo = $_POST['tipo_vehiculo'] ?? 'NULL';
                file_put_contents('debug_log.txt', "Datos: $chk_id - $chk_tipo\n", FILE_APPEND);

                $id = $_POST['id'] ?? null;
                $tipo = trim($_POST['tipo_vehiculo']);
                $precio = floatval($_POST['precio_base']);

                if (empty($tipo) || $precio <= 0) {
                    throw new Exception("Datos inválidos.");
                }

                if ($id) {
                    file_put_contents('debug_log.txt', "Intentando actualizar\n", FILE_APPEND);
                    $this->tarifaModel->actualizar($id, $tipo, $precio);
                    $_SESSION['mensaje'] = "Tarifa actualizada correctamente.";
                } else {
                    file_put_contents('debug_log.txt', "Intentando crear\n", FILE_APPEND);
                    $this->tarifaModel->crear($tipo, $precio);
                    $_SESSION['mensaje'] = "Tarifa creada correctamente.";
                }
                file_put_contents('debug_log.txt', "Operacion exitosa\n", FILE_APPEND);
                
                $_SESSION['tipo_mensaje'] = "success";

            } catch (Exception $e) {
                file_put_contents('debug_log.txt', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
                $_SESSION['mensaje'] = "Error: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "error";
            }
        } else {
            file_put_contents('debug_log.txt', "No es POST\n", FILE_APPEND);
        }
        
        // Redirección robusta (Header + JS)
        if (!headers_sent()) {
            header('Location: /tarifas');
        } 
        echo '<script>window.location.href="/tarifas";</script>';
        exit();
    }

    public function eliminar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
             header('Location: /tarifas');
             exit();
        }

        try {
            $this->tarifaModel->eliminar($id);
            $_SESSION['mensaje'] = "Tarifa eliminada.";
            $_SESSION['tipo_mensaje'] = "success";
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "error";
        }
        
        // Redirección robusta
        if (!headers_sent()) {
            header('Location: /tarifas');
        }
        echo '<script>window.location.href="/tarifas";</script>';
        exit();
    }
}
