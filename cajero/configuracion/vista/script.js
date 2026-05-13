// Configuración de Cajero - JavaScript

document.addEventListener('DOMContentLoaded', function() {
    cargarDatosIniciales();
    configurarEventos();
});

// Funciones para tabs
function showTab(tabId, buttonEl) {
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(tab => tab.classList.remove('active'));

    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => button.classList.remove('active'));

    const currentTab = document.getElementById(tabId);
    if (currentTab) currentTab.classList.add('active');
    if (buttonEl) buttonEl.classList.add('active');

    // Cargar preguntas de seguridad solo cuando el tab de seguridad está activo
    if (tabId === 'tab-seguridad') {
        cargarPreguntasSeguridadCajero();
    }
}

// Cargar datos iniciales
function cargarDatosIniciales() {
    cargarTasaActual();
    cargarDatosUsuario();
    cargarConfiguracionEmpresa();
    cargarPreferenciasUsuario();
}

// Configurar eventos de formularios
function configurarEventos() {
    // Formulario de perfil
    document.getElementById('form-perfil').addEventListener('submit', function(e) {
        e.preventDefault();
        actualizarPerfil();
    });

    // Formulario de contraseña
document.getElementById('form-password').addEventListener('submit', function(e) {
        e.preventDefault();
        cambiarContrasena();
});

// Mostrar/Ocultar contraseña para campos en configuración (global)
window.toggleConfigPassword = function(id, trigger) {
  const input = document.getElementById(id);
  if (!input) return;
  const eyeOpen = trigger.querySelector('.icon-eye');
  const eyeOff = trigger.querySelector('.icon-eye-off');
  if (input.type === 'password') {
    input.type = 'text';
    if (eyeOpen && eyeOff) { eyeOpen.style.display = 'none'; eyeOff.style.display = 'block'; }
  } else {
    input.type = 'password';
    if (eyeOpen && eyeOff) { eyeOpen.style.display = 'block'; eyeOff.style.display = 'none'; }
  }
};

    // Formulario de preferencias
    document.getElementById('form-preferencias').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarPreferencias();
    });

    // Formulario de seguridad
    const formSeguridad = document.getElementById('form-seguridad-cajero');
    if (formSeguridad) {
        cargarPreguntasSeguridadCajero();
        formSeguridad.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarPreguntaSeguridadCajero();
        });
    }
}

// Cargar tasa actual
async function cargarTasaActual() {
    try {
        const response = await fetch('../logica/obtener_tasa.php');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('current-rate').textContent = parseFloat(data.tasa_cambio).toFixed(2);
            document.getElementById('last-update').textContent = formatearFecha(data.fecha_actualizacion);
        } else {
            mostrarAlerta('Error al cargar la tasa de cambio', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al cargar la tasa', 'error');
    }
}

// Actualizar tasa (solo obtener la más reciente)
async function actualizarTasa() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="icon-refresh"></i> Actualizando...';
    btn.disabled = true;

    try {
        await cargarTasaActual();
        mostrarAlerta('Tasa actualizada correctamente', 'success');
    } catch (error) {
        mostrarAlerta('Error al actualizar la tasa', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

// Cargar datos del usuario
async function cargarDatosUsuario() {
    try {
        const response = await fetch('../logica/obtener_datos_usuario.php');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('nombre-usuario').value = data.usuario.nombre || '';
            document.getElementById('email-usuario').value = data.usuario.email || '';
            document.getElementById('telefono-usuario').value = data.usuario.telefono || '';
            document.getElementById('ultima-sesion').textContent = formatearFecha(data.usuario.ultima_sesion);
        }
    } catch (error) {
        console.error('Error al cargar datos del usuario:', error);
    }
}

// Actualizar perfil
async function actualizarPerfil() {
    const formData = {
        email: document.getElementById('email-usuario').value,
        telefono: document.getElementById('telefono-usuario').value
    };

    try {
        const response = await fetch('../logica/actualizar_perfil.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();
        
        if (data.success) {
            mostrarAlerta('Perfil actualizado correctamente', 'success');
        } else {
            mostrarAlerta(data.message || 'Error al actualizar perfil', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión', 'error');
    }
}

// Cambiar contraseña
async function cambiarContrasena() {
    const passwordActual = document.getElementById('password-actual').value;
    const passwordNueva = document.getElementById('password-nueva').value;
    const passwordConfirmar = document.getElementById('password-confirmar').value;

    // Validar que las contraseñas coincidan
    if (passwordNueva !== passwordConfirmar) {
        mostrarAlerta('Las contraseñas no coinciden', 'error');
        return;
    }

    // Validar longitud mínima
    if (passwordNueva.length < 6) {
        mostrarAlerta('La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }

    const formData = {
        password_actual: passwordActual,
        password_nueva: passwordNueva
    };

    try {
        const response = await fetch('../logica/cambiar_contrasena.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();
        
        if (data.success) {
            mostrarAlerta('Contraseña cambiada correctamente', 'success');
            document.getElementById('form-password').reset();
        } else {
            mostrarAlerta(data.message || 'Error al cambiar contraseña', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión', 'error');
    }
}

// Cargar configuración de empresa
async function cargarConfiguracionEmpresa() {
    try {
        const response = await fetch('../logica/obtener_config_empresa.php');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('nombre-empresa').textContent = data.configuracion.nombre_empresa || 'No configurado';
        }
    } catch (error) {
        console.error('Error al cargar configuración de empresa:', error);
    }
}

// Cargar preferencias del usuario
async function cargarPreferenciasUsuario() {
    try {
        const response = await fetch('../logica/obtener_preferencias.php');
        const data = await response.json();
        
        if (data.success) {
            const prefs = data.preferencias;
            document.getElementById('moneda-preferida').value = prefs.moneda_preferida || 'BS';
            document.getElementById('sonidos-notificacion').checked = prefs.sonidos_notificacion === '1';
            document.getElementById('confirmacion-ventas').checked = prefs.confirmacion_ventas === '1';
            document.getElementById('auto-imprimir').checked = prefs.auto_imprimir === '1';
        }
    } catch (error) {
        console.error('Error al cargar preferencias:', error);
    }
}

// Guardar preferencias
async function guardarPreferencias() {
    const formData = {
        moneda_preferida: document.getElementById('moneda-preferida').value,
        sonidos_notificacion: document.getElementById('sonidos-notificacion').checked ? '1' : '0',
        confirmacion_ventas: document.getElementById('confirmacion-ventas').checked ? '1' : '0',
        auto_imprimir: document.getElementById('auto-imprimir').checked ? '1' : '0'
    };

    try {
        const response = await fetch('../logica/guardar_preferencias.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();
        
        if (data.success) {
            mostrarAlerta('Preferencias guardadas correctamente', 'success');
        } else {
            mostrarAlerta(data.message || 'Error al guardar preferencias', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión', 'error');
    }
}

// Utilidades
function formatearFecha(fecha) {
    if (!fecha) return '--';
    
    const date = new Date(fecha);
    return date.toLocaleString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function mostrarAlerta(mensaje, tipo = 'info') {
    // Crear elemento de alerta
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo}`;
    alerta.textContent = mensaje;
    
    // Estilos para la alerta
    alerta.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        max-width: 300px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        animation: slideIn 0.3s ease;
    `;
    
    // Colores según el tipo
    switch (tipo) {
        case 'success':
            alerta.style.background = 'linear-gradient(135deg, #27ae60, #2ecc71)';
            break;
        case 'error':
            alerta.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
            break;
        case 'warning':
            alerta.style.background = 'linear-gradient(135deg, #f39c12, #e67e22)';
            break;
        default:
            alerta.style.background = 'linear-gradient(135deg, #3498db, #2980b9)';
    }
    
    // Agregar al DOM
    document.body.appendChild(alerta);
    
    // Remover después de 4 segundos
    setTimeout(() => {
        alerta.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (alerta.parentNode) {
                alerta.parentNode.removeChild(alerta);
            }
        }, 300);
    }, 4000);
}

// Agregar estilos de animación
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// --- Seguridad (Cajero) ---
async function cargarPreguntasSeguridadCajero() {
    try {
        const response = await fetch('../logica/obtener_preguntas.php');
        const data = await response.json();
        const selects = [
            document.getElementById('pregunta-seguridad-1'),
            document.getElementById('pregunta-seguridad-2'),
            document.getElementById('pregunta-seguridad-3')
        ];
        selects.forEach(s => s.innerHTML = '');
        if (data.success) {
            selects.forEach(s => {
                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = 'Seleccione una pregunta';
                s.appendChild(defaultOpt);
                data.preguntas.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id_pregunta;
                    opt.textContent = p.pregunta;
                    s.appendChild(opt);
                });
            });
            if (Array.isArray(data.selecciones)) {
                data.selecciones.slice(0, 3).forEach((sel, idx) => {
                    if (selects[idx]) selects[idx].value = sel.id_pregunta;
                });
            }
        } else {
            selects.forEach(s => {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'Error al cargar preguntas';
                s.appendChild(opt);
            });
        }
    } catch (error) {
        console.error('Error:', error);
        const selects = [
            document.getElementById('pregunta-seguridad-1'),
            document.getElementById('pregunta-seguridad-2'),
            document.getElementById('pregunta-seguridad-3')
        ];
        selects.forEach(s => s.innerHTML = '<option value="">Error de conexión</option>');
    }
}

async function guardarPreguntaSeguridadCajero() {
    const ids = [
        document.getElementById('pregunta-seguridad-1').value,
        document.getElementById('pregunta-seguridad-2').value,
        document.getElementById('pregunta-seguridad-3').value
    ];
    const respuestas = [
        document.getElementById('respuesta-seguridad-1').value,
        document.getElementById('respuesta-seguridad-2').value,
        document.getElementById('respuesta-seguridad-3').value
    ];
    // Validaciones
    if (ids.some(id => !id)) {
        mostrarAlerta('Seleccione las 3 preguntas de seguridad', 'warning');
        return;
    }
    // evitar preguntas repetidas
    const setIds = new Set(ids);
    if (setIds.size !== 3) {
        mostrarAlerta('No repita la misma pregunta. Deben ser 3 distintas.', 'warning');
        return;
    }
    if (respuestas.some(r => !r || r.trim().length < 2)) {
        mostrarAlerta('Ingrese las 3 respuestas válidas', 'warning');
        return;
    }
    const payload = {
        respuestas: ids.map((id, idx) => ({ id_pregunta: id, respuesta: respuestas[idx] }))
    };
    try {
        const response = await fetch('../logica/guardar_pregunta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.success) {
            mostrarAlerta('Preguntas de seguridad guardadas correctamente', 'success');
            document.getElementById('form-seguridad-cajero').reset();
            cargarPreguntasSeguridadCajero();
        } else {
            mostrarAlerta('Error al guardar: ' + (data.message || 'Desconocido'), 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al guardar seguridad', 'error');
    }
}