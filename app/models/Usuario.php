<?php

require_once __DIR__ . '/Database.php';

/**
 * Usuario Model
 * Maneja todas las operaciones relacionadas con usuarios
 */
class Usuario
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Buscar usuario por DNI
     */
    public function buscarPorDNI($dni)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE dni = ?");
            $stmt->execute([$dni]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error al buscar usuario: " . $e->getMessage());
        }
    }

    /**
     * Crear nuevo usuario
     */
    public function crear($dni, $nombre, $apellido, $telefono = null, $email = null)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (dni, nombre, apellido, telefono, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$dni, $nombre, $apellido, $telefono, $email]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error al registrar usuario: " . $e->getMessage());
        }
    }

    /**
     * Autenticar usuario (para login)
     */
    public function autenticar($usuario, $password)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM administradores WHERE usuario = ?");
            $stmt->execute([$usuario]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                return $admin;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error al autenticar: " . $e->getMessage());
        }
    }

    /**
     * Validar DNI (8 dígitos)
     */
    public static function validarDNI($dni)
    {
        return preg_match('/^[0-9]{8}$/', $dni);
    }
}
