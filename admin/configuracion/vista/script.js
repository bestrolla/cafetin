// Variables globales
let tasaActual = 36.00;
let configuraciones = {};

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    cargarConfiguraciones();
    cargarHistorialTasa();
    
    // Event listeners para formularios
    document.getElementById('form-tasa').addEventListener('submit', manejarCambioTasa);
    document.getElementById('form-empresa').addEventListener('submit', guardarConfiguracionEmpresa);
    document.getElementById('form-sistema').addEventListener('submit', guardarConfiguracionSistema);
    // Seguridad: cargar preguntas y manejar guardado
    const formSeguridad = document.getElementById('form-seguridad-admin');
    if (formSeguridad) {
        cargarPreguntasSeguridadAdmin();
        formSeguridad.addEventListener('submit', guardarPreguntaSeguridadAdmin);
    }

    // Inicializar notificaciones de inventario solo si no está el global
    if (!window.__globalNotifInitialized) {
        initInventarioNotificaciones();
    }
});

// Funciones para tabs
function showTab(tabName) {
    // Ocultar todos los tabs
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(tab => tab.classList.remove('active'));
    
    // Remover clase active de todos los botones
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => button.classList.remove('active'));
    
    // Mostrar el tab seleccionado
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

// Cargar configuraciones
async function cargarConfiguraciones() {
    try {
        const response = await fetch('../logica/obtener_configuraciones.php');
        const data = await response.json();
        
        if (data.success) {
            configuraciones = data.configuraciones;
            actualizarInterfaz();
        } else {
            mostrarAlerta('Error al cargar configuraciones: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al cargar configuraciones', 'error');
    }
}

// Actualizar interfaz con configuraciones
function actualizarInterfaz() {
    // Tasa actual
    if (configuraciones.tasa_dolar) {
        tasaActual = parseFloat(configuraciones.tasa_dolar);
        document.getElementById('current-rate').textContent = tasaActual.toFixed(2);
    }
    
    // Datos de empresa
    if (configuraciones.nombre_empresa) {
        document.getElementById('nombre-empresa').value = configuraciones.nombre_empresa;
    }
    if (configuraciones.direccion_empresa) {
        document.getElementById('direccion-empresa').value = configuraciones.direccion_empresa;
    }
    if (configuraciones.telefono_empresa) {
        document.getElementById('telefono-empresa').value = configuraciones.telefono_empresa;
    }
    if (configuraciones.email_empresa) {
        document.getElementById('email-empresa').value = configuraciones.email_empresa;
    }
    
    // Configuraciones de sistema
    if (configuraciones.moneda_principal) {
        document.getElementById('moneda-principal').value = configuraciones.moneda_principal;
    }
    if (configuraciones.iva_porcentaje) {
        document.getElementById('iva-porcentaje').value = configuraciones.iva_porcentaje;
    }
    if (configuraciones.descuento_maximo) {
        document.getElementById('descuento-maximo').value = configuraciones.descuento_maximo;
    }
    if (configuraciones.inventario_umbral_bajo) {
        document.getElementById('inventario-umbral-bajo').value = configuraciones.inventario_umbral_bajo;
    } else {
        const umbralInput = document.getElementById('inventario-umbral-bajo');
        if (umbralInput && !umbralInput.value) {
            umbralInput.value = 50;
        }
    }
    if (configuraciones.backup_automatico) {
        document.getElementById('backup-automatico').checked = configuraciones.backup_automatico === 'true';
    }
    if (configuraciones.notificaciones_email) {
        document.getElementById('notificaciones-email').checked = configuraciones.notificaciones_email === 'true';
    }
    // Líneas de gráfico: máximo y paso
    const gridMaxEl = document.getElementById('grafico-grid-max');
    const gridStepEl = document.getElementById('grafico-grid-step');
    if (gridMaxEl) gridMaxEl.value = configuraciones.grafico_grid_max || '100';
    if (gridStepEl) gridStepEl.value = configuraciones.grafico_grid_step || '10';
    // Días laborales (1=Lunes .. 7=Domingo) y flag de incluir días sin ventas
    const diasStr = configuraciones.dias_laborales || '1,2,3,4,5';
    const diasArr = diasStr.split(',').map(x => parseInt(x, 10)).filter(x => x>=1 && x<=7);
    for (let i=1;i<=7;i++) {
        const el = document.getElementById(`dias-laborales-${i}`);
        if (el) el.checked = diasArr.includes(i);
    }
    const incluirEl = document.getElementById('incluir-dias-sin-ventas');
    if (incluirEl) incluirEl.checked = (configuraciones.incluir_dias_sin_ventas || 'true') === 'true';
}

// Manejar cambio de tasa
function manejarCambioTasa(e) {
    e.preventDefault();
    
    const nuevaTasa = parseFloat(document.getElementById('nueva-tasa').value);
    
    if (nuevaTasa <= 0) {
        mostrarAlerta('La tasa debe ser mayor a 0', 'error');
        return;
    }
    
    if (nuevaTasa === tasaActual) {
        mostrarAlerta('La nueva tasa es igual a la actual', 'warning');
        return;
    }
    
    // Mostrar modal de confirmación
    document.getElementById('tasa-actual-modal').textContent = tasaActual.toFixed(2);
    document.getElementById('tasa-nueva-modal').textContent = nuevaTasa.toFixed(2);
    document.getElementById('modal-confirmacion').style.display = 'block';
}

// Confirmar cambio de tasa
async function confirmarCambioTasa() {
    const nuevaTasa = parseFloat(document.getElementById('nueva-tasa').value);
    const motivo = document.getElementById('motivo').value;
    
    try {
        const response = await fetch('../logica/actualizar_tasa.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                tasa_nueva: nuevaTasa,
                motivo: motivo
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            tasaActual = nuevaTasa;
            document.getElementById('current-rate').textContent = nuevaTasa.toFixed(2);
            document.getElementById('nueva-tasa').value = '';
            document.getElementById('motivo').value = '';
            
            mostrarAlerta('Tasa actualizada correctamente', 'success');
            cargarHistorialTasa();
        } else {
            mostrarAlerta('Error al actualizar tasa: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al actualizar tasa', 'error');
    }
    
    cerrarModal();
}

// Guardar configuración de empresa
async function guardarConfiguracionEmpresa(e) {
    e.preventDefault();
    
    const configuracionEmpresa = {
        nombre_empresa: document.getElementById('nombre-empresa').value,
        direccion_empresa: document.getElementById('direccion-empresa').value,
        telefono_empresa: document.getElementById('telefono-empresa').value,
        email_empresa: document.getElementById('email-empresa').value
    };
    
    await guardarConfiguraciones(configuracionEmpresa, 'Configuración de empresa guardada correctamente');
}

// Guardar configuración de sistema
async function guardarConfiguracionSistema(e) {
    e.preventDefault();
    
    const configuracionSistema = {
        moneda_principal: document.getElementById('moneda-principal').value,
        iva_porcentaje: document.getElementById('iva-porcentaje').value,
        descuento_maximo: document.getElementById('descuento-maximo').value,
        inventario_umbral_bajo: document.getElementById('inventario-umbral-bajo').value || '50',
        backup_automatico: document.getElementById('backup-automatico').checked ? 'true' : 'false',
        notificaciones_email: document.getElementById('notificaciones-email').checked ? 'true' : 'false',
        grafico_grid_max: (function(){
            const v = parseInt(document.getElementById('grafico-grid-max').value,10);
            return (isNaN(v) || v < 1) ? '100' : String(v);
        })(),
        grafico_grid_step: (function(){
            const v = parseInt(document.getElementById('grafico-grid-step').value,10);
            return (isNaN(v) || v < 1) ? '10' : String(v);
        })(),
        dias_laborales: (function(){
            const activos = [];
            for (let i=1;i<=7;i++) {
                const el = document.getElementById(`dias-laborales-${i}`);
                if (el && el.checked) activos.push(i);
            }
            return activos.length ? activos.join(',') : '1,2,3,4,5';
        })(),
        incluir_dias_sin_ventas: document.getElementById('incluir-dias-sin-ventas').checked ? 'true' : 'false'
    };
    
    await guardarConfiguraciones(configuracionSistema, 'Configuración de sistema guardada correctamente');
}

// Función genérica para guardar configuraciones
async function guardarConfiguraciones(configs, mensajeExito) {
    try {
        const response = await fetch('../logica/guardar_configuraciones.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(configs)
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarAlerta(mensajeExito, 'success');
            // Actualizar configuraciones locales
            Object.assign(configuraciones, configs);
        } else {
            mostrarAlerta('Error al guardar configuraciones: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al guardar configuraciones', 'error');
    }
}

// Cargar historial de tasa
async function cargarHistorialTasa() {
    try {
        const response = await fetch('../logica/obtener_historial_tasa.php');
        const data = await response.json();
        
        if (data.success) {
            mostrarHistorialTasa(data.historial);
        } else {
            console.error('Error al cargar historial:', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Mostrar historial en tabla
function mostrarHistorialTasa(historial) {
    const tbody = document.querySelector('#tabla-historial tbody');
    tbody.innerHTML = '';
    
    historial.forEach(registro => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${formatearFecha(registro.fecha_cambio)}</td>
            <td>${registro.tasa_anterior ? registro.tasa_anterior + ' Bs' : 'N/A'}</td>
            <td>${registro.tasa_nueva} Bs</td>
            <td>${registro.usuario}</td>
            <td>${registro.motivo || 'Sin especificar'}</td>
        `;
        tbody.appendChild(row);
    });
}

// Funciones de utilidad
function formatearFecha(fecha) {
    return new Date(fecha).toLocaleString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// =====================
// Notificaciones Inventario
// =====================
let notifTimer = null;
let lastNotifCount = 0;
let lastItemsHash = '';

function initInventarioNotificaciones() {
    const btn = document.getElementById('btn-notificaciones');
    if (btn) {
        btn.addEventListener('click', () => toggleNotifPanel());
    }
    // Primera carga inmediata y luego polling
    fetchInventarioNotificaciones(true);
    notifTimer = setInterval(() => fetchInventarioNotificaciones(false), 30000);
}

async function fetchInventarioNotificaciones(isFirstLoad = false) {
    try {
        const resp = await fetch('../logica/obtener_notificaciones_inventario.php');
        const data = await resp.json();
        if (!data.success) return;
        updateNotifUI(data);

        // Detectar cambios para sonido
        const hash = JSON.stringify(data.items);
        const hasNew = data.count > lastNotifCount || (hash !== lastItemsHash && data.count > 0);
        if (!isFirstLoad && hasNew) {
            playNotifSound();
        }
        lastNotifCount = data.count;
        lastItemsHash = hash;
    } catch (e) {
        // Silencioso: no interrumpir la página si hay error de red
        console.error('Error notificaciones inventario:', e);
    }
}

function updateNotifUI(data) {
    const dot = document.getElementById('notif-dot');
    const list = document.getElementById('notif-list');
    const summary = document.getElementById('notif-summary');
    if (!dot || !list || !summary) return;

    if (data.count > 0) {
        dot.hidden = false;
        summary.textContent = `${data.count} producto(s) bajo stock (≤ ${data.threshold})`;
        list.innerHTML = '';
        data.items.forEach(item => {
            const row = document.createElement('div');
            row.className = 'notif-item';
            row.innerHTML = `
                <span class="name">${item.nombre_produc}</span>
                <span class="qty">Stock: ${item.cantidad_total}</span>
                <span class="badge">Bajo</span>
            `;
            list.appendChild(row);
        });
    } else {
        dot.hidden = true;
        summary.textContent = 'Sin alertas';
        list.innerHTML = `<div class="notif-empty">No hay productos con stock bajo.</div>`;
    }
}

function toggleNotifPanel(force) {
    const panel = document.getElementById('notif-panel');
    if (!panel) return;
    const open = force === undefined ? !panel.classList.contains('open') : !!force;
    panel.classList.toggle('open', open);
    panel.setAttribute('aria-hidden', open ? 'false' : 'true');
}

// Sonido simple (beep) sin archivos externos
function playNotifSound() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const o = ctx.createOscillator();
        const g = ctx.createGain();
        o.type = 'sine';
        o.frequency.value = 880; // beep agudo
        g.gain.setValueAtTime(0.001, ctx.currentTime);
        g.gain.exponentialRampToValueAtTime(0.1, ctx.currentTime + 0.01);
        g.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.25);
        o.connect(g);
        g.connect(ctx.destination);
        o.start();
        o.stop(ctx.currentTime + 0.25);
    } catch (e) {
        // Sin sonido si el navegador bloquea reproducción
    }
}

function mostrarAlerta(mensaje, tipo) {
    // Remover alertas existentes
    const alertasExistentes = document.querySelectorAll('.alert');
    alertasExistentes.forEach(alerta => alerta.remove());
    
    // Crear nueva alerta
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo}`;
    alerta.textContent = mensaje;
    
    // Insertar al inicio del contenido
    const contentWrapper = document.querySelector('.content-wrapper');
    contentWrapper.insertBefore(alerta, contentWrapper.firstChild);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (alerta.parentNode) {
            alerta.remove();
        }
    }, 5000);
}

function cerrarModal() {
    document.getElementById('modal-confirmacion').style.display = 'none';
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('modal-confirmacion');
    if (event.target === modal) {
        cerrarModal();
    }
}

// --- Seguridad (Admin) ---
async function cargarPreguntasSeguridadAdmin() {
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
            // Opciones de preguntas para los 3 selects
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
            // Selecciones actuales
            if (Array.isArray(data.selecciones)) {
                data.selecciones.slice(0,3).forEach((sel, idx) => {
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

async function guardarPreguntaSeguridadAdmin(e) {
    e.preventDefault();
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
    if (ids.some(id => !id)) {
        mostrarAlerta('Seleccione las 3 preguntas de seguridad', 'warning');
        return;
    }
    const setIds = new Set(ids);
    if (setIds.size !== 3) {
        mostrarAlerta('No repita la misma pregunta. Deben ser 3 distintas.', 'warning');
        return;
    }
    if (respuestas.some(r => !r || r.trim().length < 2)) {
        mostrarAlerta('Ingrese las 3 respuestas válidas', 'warning');
        return;
    }
    const payload = { respuestas: ids.map((id, idx) => ({ id_pregunta: id, respuesta: respuestas[idx] })) };
    try {
        const response = await fetch('../logica/guardar_pregunta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.success) {
            mostrarAlerta('Preguntas de seguridad guardadas correctamente', 'success');
            document.getElementById('form-seguridad-admin').reset();
            cargarPreguntasSeguridadAdmin();
        } else {
            mostrarAlerta('Error al guardar: ' + (data.message || 'Desconocido'), 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al guardar seguridad', 'error');
    }
}