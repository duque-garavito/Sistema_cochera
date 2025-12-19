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
     * Buscar vehículos por término (Placa, DNI, Nombre)
     */
    public function buscar($termino)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT v.*, u.nombre, u.apellido, u.dni 
                                  FROM vehiculos v 
                                  LEFT JOIN usuarios u ON v.usuario_id = u.id 
                                  WHERE v.placa LIKE ? 
                                  OR u.dni LIKE ? 
                                  OR u.nombre LIKE ? 
                                  OR u.apellido LIKE ?
                                  ORDER BY v.en_lista_negra DESC, v.placa ASC");
            $like = '%' . $termino . '%';
            $stmt->execute([$like, $like, $like, $like]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error al buscar vehículos: " . $e->getMessage());
        }
    }

    /**
     * Crear nuevo vehículo
     */
    public function crear($placa, $tipo_vehiculo, $marca = null, $modelo = null, $color = null, $usuario_id = null)
    {
        if ($usuario_id === null) {
             throw new Exception("Error interno: El ID de usuario es obligatorio.");
        }

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
                                ORDER BY v.en_lista_negra DESC, v.placa ASC");
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
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT precio_base FROM tarifas WHERE tipo_vehiculo = ? AND estado = 'Activo'");
            $stmt->execute([$tipo_vehiculo]);
            $resultado = $stmt->fetch();
            
            if ($resultado) {
                return floatval($resultado['precio_base']);
            }
            
            // Fallback si no encuentra (o para tipos nuevos antes de config)
            return 8.00;
        } catch (Exception $e) {
            return 8.00;
        }
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

    /**
     * Alternar estado de lista negra
     */
    public function toggleListaNegra($id, $estado)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE vehiculos SET en_lista_negra = ? WHERE id = ?");
            $stmt->execute([$estado, $id]);
            return true;
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar lista negra: " . $e->getMessage());
        }
    }
}
