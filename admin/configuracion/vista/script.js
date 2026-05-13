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


    const formImport = document.getElementById('form-importar-clientes');
    if (formImport) {
        formImport.addEventListener('submit', manejarImportacionClientes);
    }
});

// Funciones para tabs
function showTab(tabName, btn) {
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(tab => tab.classList.remove('active'));
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => button.classList.remove('active'));
    document.getElementById(tabName).classList.add('active');
    if (btn && btn.classList) btn.classList.add('active');
}

async function manejarImportacionClientes(e) {
    e.preventDefault();
    const input = document.getElementById('archivo-clientes');
    if (!input || !input.files || !input.files.length) {
        mostrarAlerta('Seleccione un archivo CSV', 'warning');
        return;
    }
    const fd = new FormData();
    fd.append('archivo', input.files[0]);
    try {
        const resp = await fetch('../logica/importar_clientes.php', { method: 'POST', body: fd });
        const data = await resp.json();
        if (!data.success) {
            mostrarAlerta(data.message || 'Error en importación', 'error');
            return;
        }
        mostrarAlerta('Importación completada', 'success');
        const cont = document.getElementById('resultado-importacion');
        if (cont) {
            const t = data.totales || {};
            const detalles = Array.isArray(data.detalles) ? data.detalles.slice(0, 50) : [];
            let html = '';
            html += `<div class="summary-grid">`;
            html += `<div class="summary-item">Insertados: <strong>${t.insertados || 0}</strong></div>`;
            html += `<div class="summary-item">Actualizados: <strong>${t.actualizados || 0}</strong></div>`;
            html += `<div class="summary-item">Omitidos: <strong>${t.omitidos || 0}</strong></div>`;
            html += `<div class="summary-item">Errores: <strong>${t.errores || 0}</strong></div>`;
            html += `<div class="summary-item">Créditos creados: <strong>${t.creditos || 0}</strong></div>`;
            html += `</div>`;
            if (detalles.length) {
                html += `<table class="table"><thead><tr><th>Fila</th><th>Estado</th><th>Mensaje</th></tr></thead><tbody>`;
                detalles.forEach(d => {
                    html += `<tr><td>${d.fila || ''}</td><td>${d.estado || ''}</td><td>${d.mensaje || ''}</td></tr>`;
                });
                html += `</tbody></table>`;
            }
            cont.innerHTML = html;
        }
        formImport.reset();
    } catch (err) {
        mostrarAlerta('Error de conexión al importar', 'error');
    }
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
    const elIva = document.getElementById('iva-porcentaje');
    if (elIva && configuraciones.iva_porcentaje) {
        elIva.value = configuraciones.iva_porcentaje;
    }
    const elDesc = document.getElementById('descuento-maximo');
    if (elDesc && configuraciones.descuento_maximo) {
        elDesc.value = configuraciones.descuento_maximo;
    }
    if (configuraciones.inventario_umbral_bajo) {
        document.getElementById('inventario-umbral-bajo').value = configuraciones.inventario_umbral_bajo;
    } else {
        const umbralInput = document.getElementById('inventario-umbral-bajo');
        if (umbralInput && !umbralInput.value) {
            umbralInput.value = 50;
        }
    }
    const elBack = document.getElementById('backup-automatico');
    if (elBack && configuraciones.backup_automatico) {
        elBack.checked = configuraciones.backup_automatico === 'true';
    }
    const elNotifEmail = document.getElementById('notificaciones-email');
    if (elNotifEmail && configuraciones.notificaciones_email) {
        elNotifEmail.checked = configuraciones.notificaciones_email === 'true';
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
        inventario_umbral_bajo: document.getElementById('inventario-umbral-bajo').value || '50',
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
    const elIva = document.getElementById('iva-porcentaje');
    if (elIva) configuracionSistema.iva_porcentaje = elIva.value;
    const elDesc = document.getElementById('descuento-maximo');
    if (elDesc) configuracionSistema.descuento_maximo = elDesc.value;
    const elBack = document.getElementById('backup-automatico');
    if (elBack) configuracionSistema.backup_automatico = elBack.checked ? 'true' : 'false';
    const elNotifEmail = document.getElementById('notificaciones-email');
    if (elNotifEmail) configuracionSistema.notificaciones_email = elNotifEmail.checked ? 'true' : 'false';
    
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


function mostrarAlerta(mensaje, tipo) {
    // Remover solo notificaciones tipo toast previas
    const alertasExistentes = document.querySelectorAll('.toast-config');
    alertasExistentes.forEach(alerta => alerta.remove());
    
    // Crear nueva alerta en esquina superior derecha
    const alerta = document.createElement('div');
    alerta.className = `toast-config alert alert-${tipo}`;
    alerta.textContent = mensaje;
    alerta.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        left: auto;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 12000;
        max-width: min(340px, calc(100vw - 40px));
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        animation: toastSlideIn 0.25s ease;
    `;

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
            break;
    }
    
    // Agregar al DOM
    document.body.appendChild(alerta);
    
    // Auto-remover después de 4 segundos
    setTimeout(() => {
        if (alerta.parentNode) {
            alerta.style.animation = 'toastSlideOut 0.25s ease';
            setTimeout(() => {
                if (alerta.parentNode) {
                    alerta.remove();
                }
            }, 250);
        }
    }, 4000);
}

const toastStyle = document.createElement('style');
toastStyle.textContent = `
    @keyframes toastSlideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes toastSlideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(toastStyle);

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