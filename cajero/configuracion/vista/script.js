// Configuración de Cajero - JavaScript

document.addEventListener('DOMContentLoaded', function() {
    cargarDatosIniciales();
    configurarEventos();
});

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

    // Formulario de preferencias
    document.getElementById('form-preferencias').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarPreferencias();
    });
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