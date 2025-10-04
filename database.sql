-- Base de datos para Sistema de Control de Vehículos
CREATE DATABASE IF NOT EXISTS sistema_cochera;
USE sistema_cochera;

-- Tabla para almacenar información de usuarios/conductores
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dni VARCHAR(8) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    telefono VARCHAR(15),
    email VARCHAR(100),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla para almacenar información de vehículos
CREATE TABLE IF NOT EXISTS vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(15) NOT NULL UNIQUE,
    tipo_vehiculo ENUM('Auto', 'Moto', 'Camioneta', 'Otro') NOT NULL,
    marca VARCHAR(50),
    modelo VARCHAR(50),
    color VARCHAR(30),
    usuario_id INT,
    precio_por_dia DECIMAL(5,2) DEFAULT 0.00,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla para registrar movimientos de entrada y salida
CREATE TABLE IF NOT EXISTS movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehiculo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo_movimiento ENUM('Entrada', 'Salida') NOT NULL,
    fecha_hora_entrada TIMESTAMP NULL,
    fecha_hora_salida TIMESTAMP NULL,
    observaciones TEXT,
    estado ENUM('Activo', 'Finalizado') DEFAULT 'Activo',
    precio_total DECIMAL(8,2) DEFAULT 0.00,
    tiempo_estacionado INT DEFAULT 0,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Insertar algunos datos de ejemplo
INSERT INTO usuarios (dni, nombre, apellido, telefono, email) VALUES
('12345678', 'Juan', 'Pérez', '987654321', 'juan.perez@email.com'),
('87654321', 'María', 'García', '987654322', 'maria.garcia@email.com'),
('11223344', 'Carlos', 'López', '987654323', 'carlos.lopez@email.com');

INSERT INTO vehiculos (placa, tipo_vehiculo, marca, modelo, color, usuario_id, precio_por_dia) VALUES
('ABC123', 'Auto', 'Toyota', 'Corolla', 'Blanco', 1, 10.00),
('XY-4567', 'Moto', 'Honda', 'CB190R', 'Rojo', 2, 4.00),
('123ABC', 'Auto', 'Nissan', 'Sentra', 'Negro', 3, 10.00);

-- Tabla para usuarios administrativos del sistema
CREATE TABLE IF NOT EXISTS administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    rol ENUM('admin', 'operador') DEFAULT 'operador',
    activo TINYINT(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL
);

-- Insertar administrador por defecto (usuario: admin, contraseña: admin123)
INSERT INTO administradores (usuario, password, nombre, email, rol) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin@sistema.com', 'admin'),
('operador', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Operador', 'operador@sistema.com', 'operador');
