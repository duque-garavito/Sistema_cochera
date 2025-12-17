<?php

require_once __DIR__ . '/Database.php';

/**
 * Personal Model
 * Maneja la gestión de personal (Administradores y Operadores)
 * Mapea a la tabla 'administradores'
 */
class Personal
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todo el personal
     */
    public function obtenerTodos()
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM administradores ORDER BY nombre ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener personal: " . $e->getMessage());
        }
    }

    /**
     * Crear nuevo personal
     */
    public function crear($nombre, $usuario, $password, $rol, $email = '')
    {
        try {
            // Verificar si usuario ya existe
            $stmt = $this->pdo->prepare("SELECT id FROM administradores WHERE usuario = ?");
            $stmt->execute([$usuario]);
            if ($stmt->fetch()) {
                throw new Exception("El nombre de usuario ya existe.");
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare("INSERT INTO administradores (nombre, usuario, password, rol, email, activo) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$nombre, $usuario, $hash, $rol, $email]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error al registrar personal: " . $e->getMessage());
        }
    }

    /**
     * Eliminar personal
     */
    public function eliminar($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM administradores WHERE id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new Exception("Error al eliminar personal: " . $e->getMessage());
        }
    }

    /**
     * Buscar por ID
     */
    public function buscarPorId($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM administradores WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error al buscar personal: " . $e->getMessage());
        }
    }
}
