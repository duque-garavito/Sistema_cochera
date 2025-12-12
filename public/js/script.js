// Sistema de Control de Vehículos - JavaScript

document.addEventListener("DOMContentLoaded", function () {
  // Inicializar funcionalidades
  inicializarFormularios();
  inicializarValidaciones();
  inicializarAnimaciones();
  inicializarBusquedaRapida();
});

/**
 * Inicializar formularios con funcionalidades mejoradas
 */
function inicializarFormularios() {
  // Auto-formatear placa mientras se escribe
  const inputPlaca = document.getElementById("placa");
  if (inputPlaca) {
    inputPlaca.addEventListener("input", function (e) {
      // Permitir letras, números y guiones, convertir a mayúsculas
      let valor = e.target.value.replace(/[^A-Za-z0-9-]/g, "").toUpperCase();

      // Limitar longitud máxima a 10 caracteres
      if (valor.length > 10) {
        valor = valor.substring(0, 10);
      }

      e.target.value = valor;
    });

    // También agregar evento keypress para mejor control
    inputPlaca.addEventListener("keypress", function (e) {
      // Permitir teclas de control (backspace, delete, tab, etc.)
      if (e.which < 32) return;

      // Permitir letras, números y guiones
      const char = String.fromCharCode(e.which);
      if (!/[A-Za-z0-9-]/.test(char)) {
        e.preventDefault();
      }
    });
  }

  // Auto-formatear DNI y consultar API
  const inputDNI = document.getElementById("dni");
  const inputNombre = document.getElementById("nombre");
  const inputApellido = document.getElementById("apellido");

  let dniTimeout;

  if (inputDNI) {
    inputDNI.addEventListener("input", function (e) {
      e.target.value = e.target.value.replace(/[^0-9]/g, "");
      if (e.target.value.length > 8) {
        e.target.value = e.target.value.substring(0, 8);
      }

      // Limpiar timeout anterior
      clearTimeout(dniTimeout);

      // Consultar API cuando se complete el DNI (8 dígitos)
      const dni = e.target.value.trim();
      if (dni.length === 8) {
        // Esperar 500ms después de que el usuario deje de escribir
        dniTimeout = setTimeout(() => {
          consultarDNI(dni);
        }, 500);
      } else {
        // Si el DNI está incompleto, limpiar campos
        if (inputNombre) inputNombre.value = "";
        if (inputApellido) inputApellido.value = "";
      }
    });

    // También consultar cuando el campo pierde el foco
    inputDNI.addEventListener("blur", function () {
      const dni = this.value.trim();
      if (dni.length === 8) {
        consultarDNI(dni);
      }
    });
  }

  // Auto-formatear teléfono
  const inputTelefono = document.getElementById("telefono");
  if (inputTelefono) {
    inputTelefono.addEventListener("input", function (e) {
      e.target.value = e.target.value.replace(/[^0-9]/g, "");
    });
  }

  // Limpiar formularios después de envío exitoso
  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      // Verificar si el envío fue exitoso (esto se puede mejorar con AJAX)
      setTimeout(() => {
        const alert = document.querySelector(".alert-success");
        if (alert) {
          // Limpiar formulario después de 3 segundos
          setTimeout(() => {
            form.reset();
          }, 3000);
        }
      }, 1000);
    });
  });
}

/**
 * Inicializar validaciones en tiempo real
 */
function inicializarValidaciones() {
  // Validar placa en tiempo real
  const inputPlaca = document.getElementById("placa");
  if (inputPlaca) {
    inputPlaca.addEventListener("blur", function () {
      const placa = this.value.trim();

      if (placa && !validarFormatoPlaca(placa)) {
        mostrarError(
          this,
          "Formato de placa inválido. Ejemplos válidos: ABC123, AB-1234, 123ABC"
        );
      } else {
        limpiarError(this);
      }
    });
  }

  // Validar DNI en tiempo real
  const inputDNI = document.getElementById("dni");
  if (inputDNI) {
    inputDNI.addEventListener("blur", function () {
      const dni = this.value;

      if (dni && (dni.length !== 8 || !/^[0-9]{8}$/.test(dni))) {
        mostrarError(this, "DNI debe tener exactamente 8 dígitos");
      } else {
        limpiarError(this);
      }
    });
  }

  // Validar email en tiempo real
  const inputEmail = document.getElementById("email");
  if (inputEmail) {
    inputEmail.addEventListener("blur", function () {
      const email = this.value;
      const patron = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (email && !patron.test(email)) {
        mostrarError(this, "Formato de email inválido");
      } else {
        limpiarError(this);
      }
    });
  }
}

/**
 * Mostrar error en campo de formulario
 */
function mostrarError(input, mensaje) {
  limpiarError(input);

  input.style.borderColor = "#e74c3c";
  input.style.boxShadow = "0 0 0 3px rgba(231, 76, 60, 0.1)";

  const errorDiv = document.createElement("div");
  errorDiv.className = "field-error";
  errorDiv.style.color = "#e74c3c";
  errorDiv.style.fontSize = "0.8rem";
  errorDiv.style.marginTop = "5px";
  errorDiv.textContent = mensaje;

  input.parentNode.appendChild(errorDiv);
}

/**
 * Limpiar error de campo de formulario
 */
function limpiarError(input) {
  input.style.borderColor = "";
  input.style.boxShadow = "";

  const errorDiv = input.parentNode.querySelector(".field-error");
  if (errorDiv) {
    errorDiv.remove();
  }
}

/**
 * Inicializar animaciones y efectos visuales
 */
function inicializarAnimaciones() {
  // Animación de entrada para las cards
  const cards = document.querySelectorAll(".card");
  cards.forEach((card, index) => {
    card.style.opacity = "0";
    card.style.transform = "translateY(20px)";

    setTimeout(() => {
      card.style.transition = "all 0.6s ease";
      card.style.opacity = "1";
      card.style.transform = "translateY(0)";
    }, index * 100);
  });

  // Efecto hover mejorado para botones
  const buttons = document.querySelectorAll(".btn");
  buttons.forEach((button) => {
    button.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-2px) scale(1.02)";
    });

    button.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0) scale(1)";
    });
  });

  // Efecto de carga para formularios
  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    form.addEventListener("submit", function () {
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn) {
        const originalText = submitBtn.textContent;
        submitBtn.textContent = "⏳ Procesando...";
        submitBtn.disabled = true;

        // Restaurar botón después de 5 segundos (fallback)
        setTimeout(() => {
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        }, 5000);
      }
    });
  });
}

/**
 * Función para mostrar notificaciones toast
 */
function mostrarNotificacion(mensaje, tipo = "info") {
  const notification = document.createElement("div");
  notification.className = `notification notification-${tipo}`;
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${
          tipo === "success"
            ? "#27ae60"
            : tipo === "error"
            ? "#e74c3c"
            : "#3498db"
        };
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 1000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
    `;
  notification.textContent = mensaje;

  document.body.appendChild(notification);

  // Animar entrada
  setTimeout(() => {
    notification.style.transform = "translateX(0)";
  }, 100);

  // Remover después de 4 segundos
  setTimeout(() => {
    notification.style.transform = "translateX(400px)";
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 300);
  }, 4000);
}

/**
 * Función para confirmar acciones importantes
 */
function confirmarAccion(mensaje, callback) {
  if (confirm(mensaje)) {
    callback();
  }
}

/**
 * Función para actualizar tiempo en tiempo real
 */
function actualizarTiempoReal() {
  const elementosTiempo = document.querySelectorAll(".tiempo-activo");

  elementosTiempo.forEach((elemento) => {
    const fechaInicio = elemento.dataset.inicio;
    if (fechaInicio) {
      const inicio = new Date(fechaInicio);
      const ahora = new Date();
      const diferencia = ahora - inicio;

      const horas = Math.floor(diferencia / (1000 * 60 * 60));
      const minutos = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
      const segundos = Math.floor((diferencia % (1000 * 60)) / 1000);

      let tiempo = "";
      if (horas > 0) tiempo += horas + "h ";
      if (minutos > 0) tiempo += minutos + "m ";
      tiempo += segundos + "s";

      elemento.textContent = tiempo;
    }
  });
}

// Actualizar tiempo cada segundo
setInterval(actualizarTiempoReal, 1000);

/**
 * Función para exportar datos a CSV
 */
function exportarCSV() {
  const tabla = document.querySelector(".data-table");
  if (!tabla) {
    mostrarNotificacion("No hay datos para exportar", "error");
    return;
  }

  let csv = "";
  const filas = tabla.querySelectorAll("tr");

  filas.forEach((fila) => {
    const celdas = fila.querySelectorAll("th, td");
    const filaData = Array.from(celdas).map((celda) => {
      let texto = celda.textContent.trim();
      // Escapar comillas dobles
      texto = texto.replace(/"/g, '""');
      return `"${texto}"`;
    });
    csv += filaData.join(",") + "\n";
  });

  // Crear y descargar archivo
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  const url = URL.createObjectURL(blob);
  link.setAttribute("href", url);
  link.setAttribute(
    "download",
    `reporte_vehiculos_${new Date().toISOString().split("T")[0]}.csv`
  );
  link.style.visibility = "hidden";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  mostrarNotificacion("Archivo CSV exportado exitosamente", "success");
}

/**
 * Función para imprimir reporte
 */
function imprimirReporte() {
  const contenido = document.querySelector(".data-table");
  if (!contenido) {
    mostrarNotificacion("No hay datos para imprimir", "error");
    return;
  }

  const ventanaImpresion = window.open("", "_blank");
  ventanaImpresion.document.write(`
        <html>
            <head>
                <title>Reporte de Vehículos</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    h1 { text-align: center; color: #333; }
                    .fecha { text-align: right; margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <h1>Reporte de Movimientos de Vehículos</h1>
                <div class="fecha">Fecha de generación: ${new Date().toLocaleDateString()}</div>
                ${contenido.outerHTML}
            </body>
        </html>
    `);
  ventanaImpresion.document.close();
  ventanaImpresion.print();
}

/**
 * Función para validar formato de placa en JavaScript
 */
function validarFormatoPlaca(placa) {
  // Remover espacios y convertir a mayúsculas
  placa = placa.replace(/\s/g, "").toUpperCase();

  // Patrones más flexibles para diferentes formatos de placas
  const patrones = [
    /^[A-Z]{3}[-]?[0-9]{3}$/, // ABC123 o ABC-123
    /^[A-Z]{2}[-]?[0-9]{4}$/, // AB1234 o AB-1234
    /^[A-Z][-]?[0-9]{5}$/, // A12345 o A-12345
    /^[0-9]{3}[-]?[A-Z]{3}$/, // 123ABC o 123-ABC
    /^[A-Z0-9]{6}$/, // Cualquier combinación de 6 caracteres
    /^[A-Z0-9]{7}$/, // Cualquier combinación de 7 caracteres
    /^[A-Z0-9]{8}$/, // Cualquier combinación de 8 caracteres
  ];

  return patrones.some((patron) => patron.test(placa));
}

/**
 * Inicializar autocompletado en campos placa y DNI
 */
function inicializarBusquedaRapida() {
  const placa = document.getElementById("placa");
  const dni = document.getElementById("dni");
  const tipoMovimiento = document.getElementById("tipo_movimiento");
  const precioDia = document.getElementById("precio_dia");
  const infoVehiculo = document.getElementById("info_vehiculo");
  const sugerencias = document.getElementById("sugerencias");

  let timeoutBusqueda;

  // Autocompletado para campo placa
  if (placa) {
    placa.addEventListener("input", function () {
      const termino = this.value.trim();

      // Limpiar timeout anterior
      clearTimeout(timeoutBusqueda);

      if (termino.length >= 2) {
        // Buscar después de 300ms de inactividad
        timeoutBusqueda = setTimeout(() => {
          buscarVehiculos(termino, "placa");
        }, 300);
      } else {
        ocultarSugerencias();
        limpiarFormulario();
      }
    });

    // Buscar exacto al salir del campo (blur)
    placa.addEventListener("blur", function () {
      const termino = this.value.trim();
      if (termino.length >= 3) {
        // Pequeño retraso para permitir que el click en sugerencia ocurra primero
        setTimeout(() => {
            buscarVehiculoExacto(termino);
        }, 200);
      }
    });
  }

  // Autocompletado para campo DNI
  if (dni) {
    dni.addEventListener("input", function () {
      const termino = this.value.trim();

      // Limpiar timeout anterior
      clearTimeout(timeoutBusqueda);

      if (termino.length >= 2) {
        // Buscar después de 300ms de inactividad
        timeoutBusqueda = setTimeout(() => {
          buscarVehiculos(termino, "dni");
        }, 300);
      } else {
        ocultarSugerencias();
        limpiarFormulario();
      }
    });
  }

  // Ocultar sugerencias al hacer clic fuera
  document.addEventListener("click", function (e) {
    if (
      !placa.contains(e.target) &&
      !dni.contains(e.target) &&
      !sugerencias.contains(e.target)
    ) {
      ocultarSugerencias();
    }
  });
}

/**
 * Buscar vehículos por término
 */
function buscarVehiculos(termino, tipoCampo) {
  const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : '';
  fetch(
    `${baseUrl}/api/buscar?termino=${encodeURIComponent(termino)}&tipo=${tipoCampo}`
  )
    .then((response) => {
      // Verificar que la respuesta sea JSON
      const contentType = response.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        throw new Error("La respuesta no es JSON válido");
      }
      return response.json();
    })
    .then((data) => {
      if (data && data.success) {
        mostrarSugerencias(data.data, tipoCampo);
      } else {
        ocultarSugerencias();
      }
    })
    .catch((error) => {
      console.error("Error en búsqueda:", error);
      ocultarSugerencias();
    });
}

/**
 * Buscar vehículo exacto y autocompletar
 */
function buscarVehiculoExacto(placa) {
  const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : '';
  fetch(
    `${baseUrl}/api/buscar?termino=${encodeURIComponent(placa)}&tipo=placa`
  )
    .then((response) => response.json())
    .then((data) => {
      if (data && data.success && data.data && data.data.length > 0) {
        // Buscar coincidencia exacta
        const exacto = data.data.find(v => v.placa === placa);
        
        if (exacto) {
            const estado = exacto.tiene_movimiento_activo ? "salida" : "entrada";
            seleccionarVehiculo(
                exacto.placa, 
                exacto.dni, 
                exacto.nombre, 
                exacto.tipo_vehiculo, 
                exacto.precio_dia, 
                exacto.tipo_movimiento_sugerido, 
                estado
            );
        }
      }
    })
    .catch((error) => {
      console.error("Error en búsqueda exacta:", error);
    });
}

/**
 * Mostrar sugerencias de búsqueda
 */
function mostrarSugerencias(resultados, tipoCampo) {
  const sugerencias = document.getElementById("sugerencias");

  // Verificar que el elemento exista
  if (!sugerencias) {
    console.warn("Elemento 'sugerencias' no encontrado en el DOM");
    return;
  }

  if (!resultados || resultados.length === 0) {
    ocultarSugerencias();
    return;
  }

  let html = '<div class="sugerencias">';

  resultados.forEach((resultado) => {
    const estado = resultado.tiene_movimiento_activo ? "salida" : "entrada";
    const estadoTexto = resultado.tiene_movimiento_activo
      ? "🚪 Salida sugerida"
      : "🚗 Entrada sugerida";

    html += `
            <div class="sugerencia-item" onclick="seleccionarVehiculo('${
              resultado.placa
            }', '${resultado.dni}', '${resultado.nombre}', '${
      resultado.tipo_vehiculo
    }', '${resultado.precio_dia}', '${
      resultado.tipo_movimiento_sugerido
    }', '${estado}')">
                <div class="sugerencia-header">
                    <span class="sugerencia-placa">${resultado.placa}</span>
                    <span class="sugerencia-tipo">${
                      resultado.tipo_vehiculo
                    }</span>
                </div>
                <div class="sugerencia-info">
                    ${resultado.nombre} - DNI: ${resultado.dni}
                </div>
                <div class="sugerencia-estado ${estado}">
                    ${estadoTexto} - S/ ${parseFloat(
      resultado.precio_dia
    ).toFixed(2)}/día
                </div>
            </div>
        `;
  });

  html += "</div>";
  sugerencias.innerHTML = html;
}

/**
 * Ocultar sugerencias
 */
function ocultarSugerencias() {
  const sugerencias = document.getElementById("sugerencias");
  if (sugerencias) {
    sugerencias.innerHTML = "";
  }
}

/**
 * Seleccionar vehículo de las sugerencias
 */
function seleccionarVehiculo(
  placa,
  dni,
  nombre,
  tipo,
  precio,
  tipoMovimiento,
  estado
) {
  // Rellenar campos del formulario
  const campoPlaca = document.getElementById("placa");
  const campoDni = document.getElementById("dni");
  const campoTipoMovimiento = document.getElementById("tipo_movimiento");
  const campoPrecio = document.getElementById("precio_dia");

  if (campoPlaca) campoPlaca.value = placa;
  if (campoDni) campoDni.value = dni;
  if (campoTipoMovimiento) campoTipoMovimiento.value = tipoMovimiento;
  if (campoPrecio) campoPrecio.value = `S/ ${parseFloat(precio).toFixed(2)}`;

  // Mostrar información del vehículo
  const infoNombre = document.getElementById("info_nombre");
  const infoTipo = document.getElementById("info_tipo");
  const infoEstado = document.getElementById("info_estado");
  const infoVehiculo = document.getElementById("info_vehiculo");

  if (infoNombre) infoNombre.textContent = nombre;
  if (infoTipo) infoTipo.textContent = tipo;
  if (infoEstado) {
    infoEstado.textContent =
      estado === "entrada"
        ? "Disponible para entrada"
        : "En cochera (requiere salida)";
  }
  if (infoVehiculo) infoVehiculo.style.display = "block";

  ocultarSugerencias();

  // Mostrar notificación
  if (typeof mostrarNotificacion === "function") {
    mostrarNotificacion(
      `Vehículo ${placa} seleccionado - ${tipoMovimiento} sugerido`,
      "success"
    );
  }
}

/**
 * Limpiar formulario
 */
function limpiarFormulario() {
  const infoVehiculo = document.getElementById("info_vehiculo");
  if (infoVehiculo) {
    infoVehiculo.style.display = "none";
  }
  //document.getElementById("precio_dia").value = "";
  // We save the element in a variable first
  const inputPrecio = document.getElementById("precio_dia");
  
  // We only try to clear it if it actually exists
  if (inputPrecio) {
    inputPrecio.value = ""; 
  } else {
    console.warn("Elemento 'precio_dia' no encontrado en el HTML");
  }
}

/**
 * Función para consultar la API de DNI
 */
function consultarDNI(dni) {
  const inputNombre = document.getElementById("nombre");
  const inputApellido = document.getElementById("apellido");

  // Verificar que existan los campos
  if (!inputNombre || !inputApellido) {
    console.warn("Campos de nombre/apellido no encontrados");
    return;
  }

  // Guardar valores originales por si hay error
  const nombreOriginal = inputNombre.value;
  const apellidoOriginal = inputApellido.value;

  // Mostrar indicador de carga
  inputNombre.style.background = "rgba(52, 152, 219, 0.1)";
  inputNombre.style.borderColor = "#3498db";
  inputNombre.setAttribute("readonly", true);
  inputNombre.value = "Consultando... ⏳";

  inputApellido.style.background = "rgba(52, 152, 219, 0.1)";
  inputApellido.style.borderColor = "#3498db";
  inputApellido.setAttribute("readonly", true);
  inputApellido.value = "Consultando... ⏳";

  // Realizar petición a la API
  /*const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : '';
  fetch(`${baseUrl}/api/consultar-dni?dni=${encodeURIComponent(dni)}`)
    .then((response) => {
      // Verificar que la respuesta sea JSON
      const contentType = response.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        return response.text().then((text) => {
          throw new Error(
            "La respuesta no es JSON válido: " + text.substring(0, 100)
          );
        });
      }
      return response.json();
    })
    .then((data) => {
      // Remover atributo readonly
      inputNombre.removeAttribute("readonly");
      inputApellido.removeAttribute("readonly");

      if (data.success && data.data) {
        // Autocompletar campos
        if (data.data.nombre) {
          inputNombre.value = data.data.nombre;
          inputNombre.style.background = "rgba(46, 204, 113, 0.1)";
          inputNombre.style.borderColor = "#27ae60";
        } else {
          inputNombre.value = nombreOriginal;
          inputNombre.style.background = "";
          inputNombre.style.borderColor = "";
        }

        if (data.data.apellido) {
          inputApellido.value = data.data.apellido;
          inputApellido.style.background = "rgba(46, 204, 113, 0.1)";
          inputApellido.style.borderColor = "#27ae60";
        } else {
          inputApellido.value = apellidoOriginal;
          inputApellido.style.background = "";
          inputApellido.style.borderColor = "";
        }

        // Mostrar notificación de éxito
        if (data.data.nombre || data.data.apellido) {
          mostrarNotificacion(
            "✅ Datos obtenidos de la API correctamente",
            "success"
          );
        }

        // Restaurar color normal después de 2 segundos
        setTimeout(() => {
          inputNombre.style.background = "";
          inputNombre.style.borderColor = "";
          inputApellido.style.background = "";
          inputApellido.style.borderColor = "";
        }, 2000);
      } else {
        // No se encontraron datos o error - restaurar valores originales
        inputNombre.value = nombreOriginal;
        inputNombre.style.background = "";
        inputNombre.style.borderColor = "";

        inputApellido.value = apellidoOriginal;
        inputApellido.style.background = "";
        inputApellido.style.borderColor = "";

        if (data.error) {
          console.warn("Error al consultar API:", data.error);

          // Mostrar mensaje informativo solo si la API está deshabilitada
          if (data.debug && data.debug.api_enabled === false) {
            console.info(
              "La API está deshabilitada. Para activarla, edita config/api_config.php"
            );
            // No mostrar notificación para no molestar al usuario si la API está intencionalmente deshabilitada
          } else {
            // Para otros errores, mostrar mensaje discreto
            console.error("Error de API:", data.error, data.debug || "");
          }
        }
      }
    })
    .catch((error) => {
      console.error("Error en la consulta:", error);

      // Restaurar valores y estado normal
      inputNombre.removeAttribute("readonly");
      inputApellido.removeAttribute("readonly");
      inputNombre.value = nombreOriginal;
      inputNombre.style.background = "";
      inputNombre.style.borderColor = "";
      inputApellido.value = apellidoOriginal;
      inputApellido.style.background = "";
      inputApellido.style.borderColor = "";
    });*/


    let baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : '/Sistema_cochera'; 
  
  // 2. Use the MVC route via public/index.php to ensure it works without the router script
  const apiUrl = `${baseUrl}/public/index.php/api/consultar-dni?dni=${encodeURIComponent(dni)}`;
  
  console.log("Consultando URL:", apiUrl); // This helps you see where it is connecting in the console
  
  fetch(apiUrl)
  // --- FIX END ---
    .then((response) => {
      const contentType = response.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        return response.text().then((text) => {
          // This logs the HTML error so you can read it in the console
          console.error("Respuesta del servidor (HTML):", text); 
          throw new Error(
            "La ruta no existe (404) o error de PHP. Revisa la consola."
          );
        });
      }
      return response.json();
    })
    .then((data) => {
      inputNombre.removeAttribute("readonly");
      inputApellido.removeAttribute("readonly");

      if (data.success && data.data) {
        if (data.data.nombre) {
          inputNombre.value = data.data.nombre;
          inputNombre.style.background = "rgba(46, 204, 113, 0.1)";
          inputNombre.style.borderColor = "#27ae60";
        } else {
          inputNombre.value = nombreOriginal;
          inputNombre.style.background = "";
          inputNombre.style.borderColor = "";
        }

        if (data.data.apellido) {
          inputApellido.value = data.data.apellido;
          inputApellido.style.background = "rgba(46, 204, 113, 0.1)";
          inputApellido.style.borderColor = "#27ae60";
        } else {
          inputApellido.value = apellidoOriginal;
          inputApellido.style.background = "";
          inputApellido.style.borderColor = "";
        }

        if (data.data.nombre || data.data.apellido) {
          mostrarNotificacion(
            "✅ Datos obtenidos de la API correctamente",
            "success"
          );
        }

        setTimeout(() => {
          inputNombre.style.background = "";
          inputNombre.style.borderColor = "";
          inputApellido.style.background = "";
          inputApellido.style.borderColor = "";
        }, 2000);
      } else {
        inputNombre.value = nombreOriginal;
        inputNombre.style.background = "";
        inputNombre.style.borderColor = "";

        inputApellido.value = apellidoOriginal;
        inputApellido.style.background = "";
        inputApellido.style.borderColor = "";

        if (data.error) {
          console.warn("Error al consultar API:", data.error);
          if (data.debug && data.debug.api_enabled === false) {
            console.info("La API está deshabilitada.");
          } else {
            console.error("Error de API:", data.error);
          }
        }
      }
    })
    .catch((error) => {
      console.error("Error en la consulta:", error);
      mostrarNotificacion("❌ Error de conexión con la API", "error"); // Visual feedback for user

      inputNombre.removeAttribute("readonly");
      inputApellido.removeAttribute("readonly");
      inputNombre.value = nombreOriginal;
      inputNombre.style.background = "";
      inputNombre.style.borderColor = "";
      inputApellido.value = apellidoOriginal;
      inputApellido.style.background = "";
      inputApellido.style.borderColor = "";
    });
}

// Funciones globales para uso en HTML
window.exportarCSV = exportarCSV;
window.imprimirReporte = imprimirReporte;
window.mostrarNotificacion = mostrarNotificacion;
window.validarFormatoPlaca = validarFormatoPlaca;
window.seleccionarVehiculo = seleccionarVehiculo;
window.consultarDNI = consultarDNI;
