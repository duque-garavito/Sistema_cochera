# Manual de Usuario - Sistema de Control de Vehículos

Bienvenido al manual de usuario del Sistema de Control de Vehículos. Este documento le guiará a través de las funcionalidades principales del sistema.

## Tabla de Contenidos
1. [Acceso al Sistema](#1-acceso-al-sistema)
2. [Dashboard (Panel Principal)](#2-dashboard-panel-principal)
3. [Registro de Movimientos](#3-registro-de-movimientos)
4. [Gestión de Vehículos](#4-gestión-de-vehículos)
5. [Reportes](#5-reportes)

---

## 1. Acceso al Sistema

Para ingresar al sistema, debe autenticarse con sus credenciales:

- **Usuario**: Ingrese su nombre de usuario asignado.
- **Contraseña**: Ingrese su clave de acceso.

> **Nota:** Si olvida su contraseña, contacte al administrador del sistema.

---

## 2. Dashboard (Panel Principal)

Al iniciar sesión, accederá al Dashboard, donde encontrará un resumen de la actividad de la cochera:

### Tarjetas de Estadísticas
- **Total Vehículos**: Número total de vehículos registrados en el sistema.
- **Vehículos Activos**: Cantidad de vehículos que se encuentran actualmente dentro de la cochera.
- **Movimientos Hoy**: Total de entradas y salidas registradas en el día actual.
- **Ingresos Hoy**: Dinero recaudado durante el día.
- **Ingresos del Mes**: Dinero recaudado durante el mes en curso.

### Gráficos
- **Vehículos por Tipo**: Gráfico circular que muestra la distribución de vehículos (Autos, Motos, etc.).
- **Ingresos de los Últimos 7 Días**: Gráfico de barras que muestra la recaudación diaria de la última semana.

---

## 3. Registro de Movimientos

Esta es la sección principal para registrar entradas y salidas (`/movimientos`).

### Registrar Entrada/Salida
1. **Placa del Vehículo**: Escriba la placa del vehículo.
    - **✨ Autocompletado Inteligente**: Al terminar de escribir la placa y salir del campo (o presionar Tab), el sistema buscará automáticamente si el vehículo ya está registrado. Si lo encuentra, **rellenará automáticamente el DNI y nombre del conductor**.
2. **DNI del Conductor**: Si es un vehículo nuevo, ingrese el DNI. El sistema buscará los datos en la RENIEC automáticamente.
3. **Tipo de Movimiento**: Seleccione "Entrada" o "Salida".
    - El sistema sugerirá automáticamente el movimiento lógico (si el vehículo está adentro, sugerirá "Salida").
4. **Precio por Día**: Se calcula automáticamente según el tipo de vehículo.
5. **Observaciones**: Campo opcional para notas adicionales.
6. Haga clic en **"Registrar Movimiento"**.

### Vehículos Activos
En la parte inferior verá una lista de los vehículos que están actualmente en la cochera, con detalles como la hora de entrada y el tiempo transcurrido.

---

## 4. Gestión de Vehículos

En la sección "Vehículos" (`/vehiculos`) puede registrar vehículos frecuentes sin necesidad de crear un movimiento.

### Registrar Nuevo Vehículo
- Ingrese los datos del vehículo (Placa, Tipo, Marca, Modelo, Color).
- Ingrese los datos del propietario (DNI, Nombre, Apellido, Teléfono).
- El sistema validará que la placa no esté duplicada.

### Lista de Vehículos
Verá una lista de todos los vehículos registrados en la base de datos, útil para consultar propietarios o detalles de vehículos frecuentes.

---

## 5. Reportes

En la sección "Reportes" (`/reportes`) puede generar historiales de movimientos.

### Generar Reporte
1. Seleccione la **Fecha de Inicio**.
2. Seleccione la **Fecha de Fin**.
3. Haga clic en **"Generar Reporte"**.

### Resultados
El sistema mostrará una tabla con todos los movimientos en ese rango de fechas, incluyendo:
- Placa y Tipo de Vehículo.
- Propietario.
- Fecha/Hora de Entrada y Salida.
- Monto cobrado.

**Opciones de Exportación:**
- **🖨️ Imprimir**: Abre la vista de impresión del navegador.
- **📥 Exportar CSV**: Descarga un archivo compatible con Excel.

---

© 2025 Sistema de Control de Vehículos. Todos los derechos reservados.
