# 🚗 Sistema de Control de Vehículos

Sistema web completo para el control de entrada y salida de vehículos en una cochera, desarrollado en PHP con MySQL.

## ✨ Características

- **Registro de Entrada/Salida**: Control completo de movimientos vehiculares
- **Gestión de Vehículos**: Registro de vehículos y propietarios
- **Reportes y Consultas**: Historial detallado con filtros por fecha
- **Interfaz Moderna**: Diseño responsive y atractivo
- **Validaciones**: Validación de datos en tiempo real
- **Estadísticas**: Dashboard con métricas en tiempo real

## 🚀 Instalación

### Requisitos Previos
- XAMPP (Apache + MySQL + PHP)
- Navegador web moderno

### Pasos de Instalación

1. **Clonar/Descargar el proyecto**
   ```bash
   # Colocar los archivos en la carpeta htdocs de XAMPP
   C:\xampp\htdocs\Sistema_cochera\
   ```

2. **Configurar la Base de Datos**
   - Abrir XAMPP Control Panel
   - Iniciar Apache y MySQL
   - Abrir phpMyAdmin (http://localhost/phpmyadmin)
   - Importar el archivo `database.sql` o ejecutar las consultas SQL

3. **Configurar Conexión a Base de Datos**
   - Editar el archivo `config/database.php` si es necesario
   - Verificar credenciales de MySQL (por defecto: usuario 'root', sin contraseña)

4. **Acceder al Sistema**
   - Abrir navegador web
   - Ir a: `http://localhost/Sistema_cochera/`

## 📋 Estructura del Proyecto

```
Sistema_cochera/
├── config/
│   └── database.php          # Configuración de base de datos
├── css/
│   └── style.css            # Estilos CSS
├── includes/
│   └── functions.php        # Funciones PHP auxiliares
├── js/
│   └── script.js           # JavaScript
├── index.php               # Página principal (registro)
├── vehiculos.php          # Gestión de vehículos
├── reportes.php           # Reportes y consultas
├── database.sql           # Estructura de base de datos
└── README.md             # Este archivo
```

## 🗄️ Base de Datos

### Tablas Principales

1. **usuarios**: Información de conductores/propietarios
   - id, dni, nombre, apellido, telefono, email

2. **vehiculos**: Información de vehículos
   - id, placa, tipo_vehiculo, marca, modelo, color, usuario_id

3. **movimientos**: Registro de entrada/salida
   - id, vehiculo_id, usuario_id, tipo_movimiento, fecha_hora_entrada, fecha_hora_salida, observaciones, estado

## 🎯 Uso del Sistema

### 1. Registro de Vehículos
- Ir a la pestaña "Vehículos"
- Completar formulario con datos del vehículo y propietario
- El sistema validará automáticamente los datos

### 2. Control de Entrada/Salida
- En la página principal, ingresar placa y DNI
- Seleccionar tipo de movimiento (Entrada/Salida)
- El sistema verificará que el DNI coincida con el propietario registrado

### 3. Consulta de Reportes
- Ir a la pestaña "Reportes"
- Filtrar por fechas o ver todos los registros
- Exportar datos a CSV o imprimir reportes

## 🔧 Funcionalidades Técnicas

### Validaciones
- **Placa**: Múltiples formatos aceptados:
  - ABC123 (3 letras + 3 números)
  - AB-1234 (2 letras + guión + 4 números)
  - A12345 (1 letra + 5 números)
  - 123ABC (3 números + 3 letras)
  - Cualquier combinación de 6-8 caracteres alfanuméricos
- **DNI**: 8 dígitos numéricos
- **Email**: Formato válido de correo electrónico
- **Verificación**: DNI debe coincidir con propietario del vehículo

### Sistema de Precios (Tarifa Diaria)
- **Moto**: S/ 4.00 por día
- **Auto**: S/ 10.00 por día
- **Camioneta**: S/ 12.00 por día
- **Otro**: S/ 8.00 por día

**Lógica de cobro:**
- Si entra y sale el mismo día: S/ 1 día
- Si pasa al día siguiente: S/ 2 días (independiente de las horas)
- Si pasa más de 24 horas: S/ días completos + 1

### Características de Seguridad
- Validación de entrada de datos
- Prevención de inyección SQL (PDO prepared statements)
- Sanitización de datos de salida
- Verificación de propietario antes de registrar movimientos

### Interfaz de Usuario
- Diseño responsive (móvil y desktop)
- Validación en tiempo real
- Notificaciones visuales
- Animaciones suaves
- Tema moderno con gradientes

## 📊 Tipos de Vehículos Soportados

- 🚗 **Auto**: Automóviles particulares
- 🏍️ **Moto**: Motocicletas
- 🚛 **Camioneta**: Vehículos comerciales
- 🚙 **Otro**: Otros tipos de vehículos

## 🔍 Consultas y Reportes

### Filtros Disponibles
- **Por Fecha**: Rango de fechas específico
- **Todos los Registros**: Historial completo
- **Estados**: Vehículos activos o finalizados

### Estadísticas
- Total de movimientos
- Número de entradas
- Número de salidas
- Vehículos únicos

## 🛠️ Personalización

### Modificar Estilos
Editar el archivo `css/style.css` para personalizar:
- Colores del tema
- Tipografías
- Espaciados
- Efectos visuales

### Agregar Funcionalidades
- Modificar `includes/functions.php` para nuevas funciones
- Actualizar `js/script.js` para funcionalidades JavaScript
- Crear nuevas páginas PHP siguiendo la estructura existente

## 🐛 Solución de Problemas

### Error de Conexión a Base de Datos
- Verificar que MySQL esté ejecutándose en XAMPP
- Revisar credenciales en `config/database.php`
- Confirmar que la base de datos `sistema_cochera` existe

### Página No Carga
- Verificar que Apache esté ejecutándose
- Confirmar que los archivos están en la carpeta correcta
- Revisar la URL en el navegador

### Validaciones No Funcionan
- Verificar que JavaScript esté habilitado en el navegador
- Revisar la consola del navegador para errores
- Confirmar que `js/script.js` se está cargando correctamente

## 📝 Notas de Desarrollo

- **PHP**: Versión 7.4 o superior recomendada
- **MySQL**: Versión 5.7 o superior
- **Navegadores**: Chrome, Firefox, Safari, Edge (versiones modernas)
- **Responsive**: Optimizado para dispositivos móviles

## 🤝 Contribuciones

Para contribuir al proyecto:
1. Fork del repositorio
2. Crear rama para nueva funcionalidad
3. Realizar cambios y pruebas
4. Enviar pull request

## 📄 Licencia

Este proyecto está bajo licencia MIT. Ver archivo LICENSE para más detalles.

## 📞 Soporte

Para soporte técnico o consultas:
- Crear issue en el repositorio
- Revisar documentación
- Verificar configuración de XAMPP

---

**¡Sistema listo para usar! 🚗✨**
