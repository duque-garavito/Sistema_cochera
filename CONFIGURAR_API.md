# 🔧 Configuración de API de DNI

Este documento explica cómo configurar la integración con una API externa para autocompletar nombres y apellidos basados en el DNI.

## 📋 Pasos de Configuración

### 1. Editar el archivo de configuración

Abre el archivo `config/api_config.php` y modifica las siguientes constantes:

```php
// URL de tu API (reemplazar con la URL real)
define('API_DNI_URL', 'https://apiperu.dev/api/dni');

// Activar/desactivar la consulta a la API
define('API_DNI_ENABLED', true);

// Timeout en segundos
define('API_DNI_TIMEOUT', 10);

// Headers HTTP personalizados (si tu API los requiere)
define('API_DNI_HEADERS', [
    'Content-Type: application/json',
    'User-Agent: Sistema-Cochera/1.0',
    // 'Authorization: 99051b1bfcffcb7e7ae07c6cdfb8506956ae281162353f8408ffe0a4cd2c0e8f'  // Descomentar si necesitas autenticación
]);
```

### 2. Configurar el formato de respuesta

Abre el archivo `api/consultar_dni.php` y ajusta el mapeo de datos según el formato de respuesta de tu API.

#### Ejemplo 1: API que devuelve nombres y apellidos separados

```json
{
  "nombres": "Juan Carlos",
  "apellidos": "Pérez García"
}
```

#### Ejemplo 2: API que devuelve nombre completo

```json
{
  "nombreCompleto": "Pérez García Juan Carlos"
}
```

#### Ejemplo 3: API con estructura personalizada

```json
{
  "primerNombre": "Juan",
  "segundoNombre": "Carlos",
  "apellidoPaterno": "Pérez",
  "apellidoMaterno": "García"
}
```

### 3. Personalizar el mapeo de datos

En el archivo `api/consultar_dni.php`, ajusta la sección de extracción de datos:

```php
// Ejemplo para una API que usa "nombre" y "apellido"
if (isset($api_data['nombre'])) {
    $nombre = $api_data['nombre'];
}

if (isset($api_data['apellido'])) {
    $apellido = $api_data['apellido'];
}
```

## 🧪 Probar la Configuración

### Opción 1: Usar el script de prueba

Crea un archivo `test_api.php` en la raíz del proyecto:

```php
<?php
require_once 'config/api_config.php';

$dni = '12345678'; // DNI de prueba
echo "Consultando DNI: " . $dni . "\n";
echo "URL: " . API_DNI_URL . $dni . "\n";

$result = consultarAPI_DNI($dni);
print_r($result);
?>
```

### Opción 2: Probar desde el navegador

1. Abre el formulario de registro de vehículos
2. Ingresa un DNI válido (8 dígitos)
3. Espera 500ms después de ingresar el último dígito
4. Los campos de nombre y apellido deberían autocompletarse

## 🔍 Solución de Problemas

### La API no se consulta

- Verifica que `API_DNI_ENABLED` esté en `true`
- Revisa la consola del navegador (F12) para ver errores
- Verifica que la URL de la API sea correcta

### Los campos no se autocompletan

- Verifica el formato de respuesta de tu API
- Ajusta el mapeo de datos en `api/consultar_dni.php`
- Revisa los logs en la consola del navegador

### Error de conexión

- Verifica que tu servidor tenga acceso a internet
- Verifica que la URL de la API sea accesible
- Verifica que no haya firewall bloqueando la conexión

## 📝 Ejemplos de APIs Comunes

### API de Reniec (Perú)

```php
define('API_DNI_URL', 'https://api.reniec.gob.pe/dni/');
```

### API Personalizada

```php
define('API_DNI_URL', 'https://tu-dominio.com/api/v1/consultar-dni/');
define('API_DNI_HEADERS', [
    'Content-Type: application/json',
    'Authorization: 99051b1bfcffcb7e7ae07c6cdfb8506956ae281162353f8408ffe0a4cd2c0e8f'
]);
```

## ⚙️ Funcionalidades

- ✅ Autocompletado automático al ingresar 8 dígitos
- ✅ Consulta cuando el campo pierde el foco (blur)
- ✅ Indicadores visuales de carga
- ✅ Manejo de errores silencioso
- ✅ No bloquea la edición manual si la API falla

## 🔒 Seguridad

- Los datos se consultan solo cuando el DNI tiene 8 dígitos
- Los errores de API no bloquean el uso del formulario
- El usuario puede editar manualmente los campos en cualquier momento
- No se almacenan las respuestas de la API en cache

---

**Nota:** Asegúrate de tener permisos y una suscripción válida para usar la API externa que elijas.
