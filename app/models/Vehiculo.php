<?php

require_once __DIR__ . '/Database.php';

/**
 * Vehiculo Model
 * Maneja todas las operaciones relacionadas con vehículos
 */
class Vehiculo
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Buscar vehículo por placa
     */
    public function buscarPorPlaca($placa)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT v.*, u.nombre, u.apellido FROM vehiculos v 
                                  LEFT JOIN usuarios u ON v.usuario_id = u.id 
                                  WHERE v.placa = ?");
            $stmt->execute([$placa]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error al buscar vehículo: " . $e->getMessage());
        }
    }

    /**
     * Crear nuevo vehículo
     */
    public function crear($placa, $tipo_vehiculo, $marca = null, $modelo = null, $color = null, $usuario_id)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO vehiculos (placa, tipo_vehiculo, marca, modelo, color, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$placa, $tipo_vehiculo, $marca, $modelo, $color, $usuario_id]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error al registrar vehículo: " . $e->getMessage());
        }
    }

    /**
     * Obtener todos los vehículos
     */
    public function obtenerTodos()
    {
        try {
            $stmt = $this->pdo->query("SELECT v.*, u.nombre, u.apellido, u.dni 
                                FROM vehiculos v 
                                LEFT JOIN usuarios u ON v.usuario_id = u.id 
                                ORDER BY v.placa");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener vehículos: " . $e->getMessage());
        }
    }

    /**
     * Obtener sugerencias de búsqueda
     */
    public function obtenerSugerencias($termino)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT DISTINCT v.id, v.placa, u.dni, u.nombre, u.apellido, v.tipo_vehiculo
                                  FROM vehiculos v 
                                  LEFT JOIN usuarios u ON v.usuario_id = u.id 
                                  WHERE v.placa LIKE ? OR u.dni LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ?
                                  LIMIT 10");
            $termino_busqueda = '%' . $termino . '%';
            $stmt->execute([$termino_busqueda, $termino_busqueda, $termino_busqueda, $termino_busqueda]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener sugerencias: " . $e->getMessage());
        }
    }

    /**
     * Actualizar precio por día
     */
    public function actualizarPrecio($id, $precio)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE vehiculos SET precio_por_dia = ? WHERE id = ?");
            $stmt->execute([$precio, $id]);
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar precio: " . $e->getMessage());
        }
    }

    /**
     * Obtener precio por tipo de vehículo
     */
    public static function obtenerPrecioPorTipo($tipo_vehiculo)
    {
        $precios = [
            'Auto' => 10.00,
            'Moto' => 4.00,
            'Camioneta' => 12.00,
            'Otro' => 8.00
        ];

        return $precios[$tipo_vehiculo] ?? 8.00;
    }

    /**
     * Validar formato de placa
     */
    public static function validarPlaca($placa)
    {
        $placa = strtoupper(str_replace(' ', '', $placa));

        $patrones = [
            '/^[A-Z]{3}[-]?[0-9]{3}$/',
            '/^[A-Z]{2}[-]?[0-9]{4}$/',
            '/^[A-Z][-]?[0-9]{5}$/',
            '/^[0-9]{3}[-]?[A-Z]{3}$/',
            '/^[A-Z0-9]{6}$/',
            '/^[A-Z0-9]{7}$/',
            '/^[A-Z0-9]{8}$/'
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $placa)) {
                return true;
            }
        }

        return false;
    }
    /**
     * Obtener conteo de vehículos por tipo
     */
    public function obtenerConteoPorTipo()
    {
        try {
            $stmt = $this->pdo->query("SELECT tipo_vehiculo, COUNT(*) as total FROM vehiculos GROUP BY tipo_vehiculo");
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener conteo por tipo: " . $e->getMessage());
        }
    }
}
