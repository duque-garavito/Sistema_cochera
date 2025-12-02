<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once 'config/api_config.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de API de DNI</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .test-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }

        .test-result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
        }

        .test-result.success {
            background: rgba(46, 204, 113, 0.1);
            border: 2px solid #27ae60;
        }

        .test-result.error {
            background: rgba(231, 76, 60, 0.1);
            border: 2px solid #e74c3c;
        }

        .test-result.info {
            background: rgba(52, 152, 219, 0.1);
            border: 2px solid #3498db;
        }

        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="test-container card">
            <h1>🧪 Prueba de API de DNI</h1>

            <div class="form-group">
                <label for="dni_test">Ingrese un DNI (8 dígitos):</label>
                <input type="text" id="dni_test" placeholder="12345678" maxlength="8"
                    pattern="[0-9]{8}" style="width: 100%; padding: 12px;">
                <button onclick="probarAPI()" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                    🔍 Consultar API
                </button>
            </div>

            <div id="resultado"></div>

            <hr style="margin: 30px 0;">

            <div class="test-result info">
                <h3>📋 Configuración Actual:</h3>
                <p><strong>URL de la API:</strong> <?php echo API_DNI_URL; ?></p>
                <p><strong>API Habilitada:</strong>
                    <?php if (API_DNI_ENABLED): ?>
                        ✅ Sí
                    <?php else: ?>
                        ❌ No - <a href="config/api_config.php" target="_blank" style="color: #3498db;">Editar configuración</a>
                    <?php endif; ?>
                </p>
                <p><strong>Timeout:</strong> <?php echo API_DNI_TIMEOUT; ?> segundos</p>
                <p><strong>Token configurado:</strong> <?php echo !empty(API_DNI_TOKEN) ? '✅ Sí' : '❌ No (puede ser necesario)'; ?></p>
                <p><strong>cURL disponible:</strong> <?php echo function_exists('curl_init') ? '✅ Sí' : '❌ No'; ?></p>
                <p><strong>Headers:</strong></p>
                <ul>
                    <?php foreach (getAPI_DNI_Headers() as $header): ?>
                        <li><?php echo htmlspecialchars($header); ?></li>
                    <?php endforeach; ?>
                </ul>

                <?php if (!API_DNI_ENABLED): ?>
                    <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 15px; border-radius: 5px; margin-top: 15px;">
                        <strong>⚠️ API Deshabilitada</strong>
                        <p>Para habilitar la consulta de API, edita el archivo <code>config/api_config.php</code> y cambia:</p>
                        <code>define('API_DNI_ENABLED', true);</code>
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin-top: 20px;">
                <p><a href="vehiculos.php">← Volver al registro de vehículos</a></p>
            </div>
        </div>
    </div>

    <script>
        function probarAPI() {
            const dni = document.getElementById('dni_test').value.trim();
            const resultado = document.getElementById('resultado');

            if (dni.length !== 8 || !/^[0-9]{8}$/.test(dni)) {
                resultado.innerHTML = `
                    <div class="test-result error">
                        <strong>❌ Error:</strong> El DNI debe tener exactamente 8 dígitos
                    </div>
                `;
                return;
            }

            resultado.innerHTML = `
                <div class="test-result info">
                    <strong>⏳ Consultando...</strong> Por favor espere...
                </div>
            `;

            fetch(`api/consultar_dni.php?dni=${encodeURIComponent(dni)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultado.innerHTML = `
                            <div class="test-result success">
                                <strong>✅ Éxito:</strong>
                                <p><strong>DNI:</strong> ${data.data.dni}</p>
                                <p><strong>Nombre:</strong> ${data.data.nombre || '(no disponible)'}</p>
                                <p><strong>Apellido:</strong> ${data.data.apellido || '(no disponible)'}</p>
                                <h4>Respuesta completa de la API:</h4>
                                <pre>${JSON.stringify(data, null, 2)}</pre>
                            </div>
                        `;
                    } else {
                        let errorHtml = `<div class="test-result error">`;
                        errorHtml += `<strong>❌ Error:</strong> ${data.error || 'Error desconocido'}`;

                        if (data.debug) {
                            errorHtml += `<h4>🔍 Información de Debug:</h4>`;
                            errorHtml += `<ul>`;
                            if (data.debug.api_enabled !== undefined) {
                                errorHtml += `<li><strong>API Habilitada:</strong> ${data.debug.api_enabled ? '✅ Sí' : '❌ No'}</li>`;
                            }
                            if (data.debug.api_url) {
                                errorHtml += `<li><strong>URL de la API:</strong> ${data.debug.api_url}</li>`;
                            }
                            if (data.debug.has_token !== undefined) {
                                errorHtml += `<li><strong>Token configurado:</strong> ${data.debug.has_token ? '✅ Sí' : '❌ No'}</li>`;
                            }
                            if (data.debug.curl_available !== undefined) {
                                errorHtml += `<li><strong>cURL disponible:</strong> ${data.debug.curl_available ? '✅ Sí' : '❌ No'}</li>`;
                            }
                            errorHtml += `</ul>`;

                            if (!data.debug.api_enabled) {
                                errorHtml += `<div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-top: 10px;">
                                    <strong>💡 Solución:</strong> Habilita la API editando <code>config/api_config.php</code> y cambiando <code>API_DNI_ENABLED</code> a <code>true</code>
                                </div>`;
                            } else if (!data.debug.has_token) {
                                errorHtml += `<div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-top: 10px;">
                                    <strong>💡 Solución:</strong> La API puede requerir un token de autenticación. Configura <code>API_DNI_TOKEN</code> en <code>config/api_config.php</code>
                                </div>`;
                            }

                            errorHtml += `<details style="margin-top: 10px;"><summary>Ver detalles técnicos</summary><pre>${JSON.stringify(data.debug, null, 2)}</pre></details>`;
                        }

                        errorHtml += `</div>`;
                        resultado.innerHTML = errorHtml;
                    }
                })
                .catch(error => {
                    resultado.innerHTML = `
                        <div class="test-result error">
                            <strong>❌ Error de conexión:</strong>
                            <p>${error.message}</p>
                            <p>Verifica que la URL de la API sea correcta y que tengas conexión a internet.</p>
                        </div>
                    `;
                });
        }

        // Permitir consultar con Enter
        document.getElementById('dni_test').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                probarAPI();
            }
        });
    </script>
</body>

</html>