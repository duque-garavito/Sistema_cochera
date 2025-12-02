<?php

/**
 * Database Model - Singleton Pattern
 * Maneja la conexión a la base de datos usando PDO
 */
class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        // Configuración de la base de datos
        $host = 'localhost';
        $dbname = 'sistema_cochera';
        $username = 'root';
        $password = '';

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    // Prevenir clonación
    private function __clone() {}

    // Prevenir unserialize (public para evitar warning, pero lanza excepción)
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a singleton.");
    }
}
