-- Migración para cambiar de precios por hora a precios por día
-- Ejecutar este archivo si ya tienes una base de datos existente

USE sistema_cochera;

-- Renombrar la columna de precio_por_hora a precio_por_dia
ALTER TABLE vehiculos CHANGE COLUMN precio_por_hora precio_por_dia DECIMAL(5,2) DEFAULT 0.00;

-- Actualizar los precios existentes (mantener los mismos valores)
-- Los precios ya están correctos como tarifas diarias:
-- Auto: S/ 10.00 por día
-- Moto: S/ 4.00 por día  
-- Camioneta: S/ 12.00 por día
-- Otro: S/ 8.00 por día

-- Verificar que los cambios se aplicaron correctamente
SELECT 'Migración completada. Los precios ahora son por día:' as mensaje;
SELECT placa, tipo_vehiculo, precio_por_dia FROM vehiculos;
