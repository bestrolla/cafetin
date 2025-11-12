// Variables globales
let facturas = [];
let facturasFiltradas = [];

// Utilidades de moneda
function getMonedaActual() {
    return localStorage.getItem('monedaActual') || 'USD';
}

function getTasaCambio() {
    const valor = parseFloat(localStorage.getItem('tasaCambio'));
    return Number.isFinite(valor) && valor > 0 ? valor : 36;
}

// Cargar tasa de cambio desde Configuración (fuente: BD configuraciones)
async function cargarTasaDesdeConfiguracionCajero() {
    try {
        // Endpoint que lee 'configuraciones.tasa_dolar' sin restricción de rol
        const resp = await fetch('../lobby/logica/obtener_tasa_cambio.php');
        const data = await resp.json();
        if (data && data.success && data.tasa_cambio) {
            const tasa = parseFloat(data.tasa_cambio);
            if (Number.isFinite(tasa) && tasa > 0) {
                localStorage.setItem('tasaCambio', tasa.toString());
                // Actualizar vistas si están abiertas
                try { actualizarEquivalenteAbono(); } catch (_) {}
                try { mostrarCuentas(); actualizarResumen(); } catch (_) {}
            }
        }
    } catch (err) {
        console.warn('No se pudo cargar tasa desde configuración (cajero):', err);
    }
}

function formatMonto(monto) {
    const moneda = getMonedaActual();
    const tasa = getTasaCambio();
    const num = parseFloat(monto) || 0;
    if (moneda === 'USD') {
        return '$' + num.toFixed(2);
    }
    return 'Bs ' + (num * tasa).toFixed(2);
}

// Función para cargar las facturas
function cargarCuentas() {
    fetch('../logica/obtener_cuentas.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                mostrarMensaje('Error al cargar las facturas: ' + data.error, 'error');
                return;
            }
            
            facturas = data;
            facturasFiltradas = data;
            mostrarCuentas();
            actualizarResumen();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error al cargar las facturas', 'error');
        });
}

// Actualiza el total equivalente (USD y Bs) en el modal de Abono
function actualizarEquivalenteAbono() {
    const el = document.getElementById('equivalenteAbono');
    if (!el) return;
    const tasa = getTasaCambio();
    if (!(tasa > 0)) {
        el.textContent = 'Configura la tasa de cambio para ver el total equivalente';
        return;
    }
    const usd = parseFloat(document.getElementById('montoAbonoUsd')?.value || '0') || 0;
    const bs = parseFloat(document.getElementById('montoAbonoBs')?.value || '0') || 0;
    const totalUsd = usd + (bs / tasa);
    const totalBs = (usd * tasa) + bs;
    el.textContent = `Total equivalente: USD $${totalUsd.toFixed(2)} | Bs ${totalBs.toFixed(2)}`;
}

// Función para mostrar las facturas en la tabla
function mostrarCuentas() {
    const tbody = document.querySelector('#tablaCuentas tbody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (facturasFiltradas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay facturas registradas</td></tr>';
        return;
    }

    // Orden alfabético por nombre del cliente
    facturasFiltradas.sort((a, b) => (a.cliente || '').localeCompare((b.cliente || ''), 'es', { sensitivity: 'base' }));

    const moneda = getMonedaActual();
    facturasFiltradas.forEach(factura => {
        const saldo = parseFloat(factura.saldo_pendiente);
        const estado = factura.estado_factura;
        const estadoTexto = estado === 'pagado' ? 'Pagado' : (estado === 'parcial' ? 'Parcial' : 'Pendiente');
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${factura.cliente}</td>
            <td>${factura.total_productos} factura(s)</td>
            <td>${formatMonto(factura.total_factura)}</td>
            <td>${formatMonto(factura.total_abonado)}</td>
            <td>${formatMonto(saldo)}</td>
            <td>
                <span class="badge ${estado === 'pagado' ? 'bg-success' : estado === 'parcial' ? 'bg-warning' : 'bg-danger'}">
                    ${estadoTexto}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="verDetalleCliente(${factura.id_cliente})">Ver Detalles</button>
                <button class="btn btn-sm btn-success ms-1" onclick="abrirModalAbonoCliente(${factura.id_cliente}, '${factura.cliente}', ${saldo.toFixed(2)})">Abonar</button>
                <button class="btn btn-sm btn-info ms-1" onclick="verHistorialCliente(${factura.id_cliente})">Historial</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Determinar estado de la cuenta
function determinarEstado(saldo) {
    if (saldo <= 0) {
        return 'Pagado';
    } else if (saldo > 0) {
        return 'Debes';
    }
    return 'Pendiente';
}

// Formatear fecha
function formatearFecha(fecha) {
    const date = new Date(fecha);
    return date.toLocaleDateString('es-ES');
}

// Función para filtrar facturas
function filtrarCuentas() {
    const filtroCliente = document.getElementById('filtroCliente').value.toLowerCase();
    const filtroEstado = document.getElementById('filtroEstado').value;
    const filtroFecha = document.getElementById('filtroFecha').value;

    facturasFiltradas = facturas.filter(factura => {
        const cumpleCliente = !filtroCliente || factura.cliente.toLowerCase().includes(filtroCliente);
        const cumpleEstado = !filtroEstado || factura.estado_factura === filtroEstado;
        // Al agrupar por cliente, no filtramos por fecha en esta tabla
        const cumpleFecha = true;
        
        return cumpleCliente && cumpleEstado && cumpleFecha;
    });

    mostrarCuentas();
    actualizarResumen();
}

// Función para limpiar filtros
function limpiarFiltros() {
    document.getElementById('filtroCliente').value = '';
    document.getElementById('filtroEstado').value = '';
    document.getElementById('filtroFecha').value = '';
    
    facturasFiltradas = facturas;
    mostrarCuentas();
    actualizarResumen();
}

// Función para actualizar el resumen
function actualizarResumen() {
    const totalFacturas = facturasFiltradas.length;
    const facturasPendientes = facturasFiltradas.filter(f => f.estado_factura === 'pendiente').length;
    const totalAdeudado = facturasFiltradas.reduce((sum, f) => sum + parseFloat(f.saldo_pendiente), 0);
    const totalAbonado = facturasFiltradas.reduce((sum, f) => sum + parseFloat(f.total_abonado), 0);

    document.getElementById('totalCuentas').textContent = totalFacturas;
    document.getElementById('cuentasPendientes').textContent = facturasPendientes;
    document.getElementById('totalAdeudado').textContent = formatMonto(totalAdeudado);
    document.getElementById('totalAbonado').textContent = formatMonto(totalAbonado);
}

// Abrir modal para abonar
function abrirModalAbono(idFactura) {
    const factura = facturas.find(f => f.id_factura == idFactura);
    if (!factura) return;
    
    facturaSeleccionada = factura;
    const saldo = parseFloat(factura.saldo_pendiente);
    
    document.getElementById('cliente-info').value = factura.cliente;
    document.getElementById('saldo-actual').value = `$${saldo.toFixed(2)}`;
    document.getElementById('monto-abono').value = '';
    document.getElementById('monto-abono').max = saldo.toFixed(2);
    document.getElementById('metodo-pago').value = '';
    document.getElementById('observaciones').value = '';
    
    document.getElementById('modal-abonar').style.display = 'block';
}

// Cerrar modal
function cerrarModal() {
    document.getElementById('modal-abonar').style.display = 'none';
    cuentaSeleccionada = null;
}

// Procesar abono
async function procesarAbono(event) {
    event.preventDefault();
    
    if (!cuentaSeleccionada) return;
    
    const montoAbono = parseFloat(document.getElementById('monto-abono').value);
    const metodoPago = document.getElementById('metodo-pago').value;
    const observaciones = document.getElementById('observaciones').value;
    
    const saldoActual = parseFloat(cuentaSeleccionada.total) - parseFloat(cuentaSeleccionada.abonado || 0);
    
    if (montoAbono <= 0 || montoAbono > saldoActual) {
        mostrarMensaje('El monto del abono debe ser mayor a 0 y no exceder el saldo actual', 'error');
        return;
    }
    
    if (!metodoPago) {
        mostrarMensaje('Debe seleccionar un método de pago', 'error');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('id_credito', cuentaSeleccionada.id_credito);
        formData.append('monto_abono', montoAbono);
        formData.append('metodo_pago', metodoPago);
        formData.append('observaciones', observaciones);

        const response = await fetch('../logica/procesar_abono.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarMensaje('Abono procesado exitosamente', 'success');
            cerrarModal();
            cargarCuentas(); // Recargar datos
        } else {
            mostrarMensaje(data.message || 'Error al procesar el abono', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('Error de conexión al procesar el abono', 'error');
    }
}

// Función para ver detalle de factura
function verDetalleFactura(idCliente, fechaFactura) {
    fetch(`../logica/obtener_detalle_factura.php?id_cliente=${idCliente}&fecha_factura=${fechaFactura}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                mostrarMensaje('Error al cargar el detalle: ' + data.error, 'error');
                return;
            }
            
            mostrarModalDetalle(data);
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error al cargar el detalle de la factura', 'error');
        });
}

// Historial por cliente (agrupado por fecha)
function verHistorialCliente(idCliente) {
    fetch(`../logica/obtener_historial_cliente.php?id_cliente=${idCliente}`)
        .then(response => response.json())
        .then(historial => {
            if (historial.error) {
                mostrarMensaje('Error al cargar el historial: ' + historial.error, 'error');
                return;
            }
            window.modalDetalleContext = 'historial';
            window.ultimoHistorialCuentas = historial;
            mostrarModalHistorial(historial);
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error al cargar el historial del cliente', 'error');
        });
}

function mostrarModalHistorial(historial) {
    const modal = document.getElementById('modalDetalle');
    const modalBody = modal.querySelector('.modal-body');
    const monedaLabel = getMonedaActual() === 'USD' ? 'USD' : 'Bs';

    let contenido = '';
    if (!historial || historial.length === 0) {
        contenido = '<p class="text-center">Sin historial de facturas pendientes.</p>';
    } else {
        historial.forEach(h => {
            let productosHtml = '';
            h.productos.forEach(p => {
                productosHtml += `
                    <tr>
                        <td>${p.producto}</td>
                        <td>${p.cantidad}</td>
                        <td>${formatMonto(p.subtotal)}</td>
                    </tr>
                `;
            });
            contenido += `
                <div class="mb-4">
                    <h6 class="bg-light p-2 rounded">
                        <strong>Fecha:</strong> ${h.fecha}
                        <span class="float-right">Saldo (${monedaLabel}): ${formatMonto(h.saldo_pendiente)}</span>
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal (${monedaLabel})</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${productosHtml}
                            </tbody>
                        </table>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-4"><strong>Total (${monedaLabel}):</strong> ${formatMonto(h.total_factura)}</div>
                        <div class="col-md-4"><strong>Abonado (${monedaLabel}):</strong> ${formatMonto(h.total_abonado)}</div>
                        <div class="col-md-4"><strong>Pendiente (${monedaLabel}):</strong> ${formatMonto(h.saldo_pendiente)}</div>
                    </div>
                </div>
            `;
        });
    }

    modalBody.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Historial de Facturas por Fecha</h5>
        </div>
        ${contenido}
    `;
    modal.style.display = 'block';
    window.modalDetalleContext = 'historial';
    // Sincronizar texto del botón de moneda en el encabezado del modal
    const btnToggleModalDetalle = document.getElementById('btnToggleMonedaCajeroDetalle');
    if (btnToggleModalDetalle) {
        const m = getMonedaActual();
        btnToggleModalDetalle.textContent = 'Moneda: ' + (m === 'USD' ? 'USD' : 'Bs');
    }
}

// Función para cerrar el modal de detalles
function cerrarModalDetalle() {
    document.getElementById('modalDetalle').style.display = 'none';
}

// Función para cerrar el modal de abono
function cerrarModalAbono() {
    document.getElementById('modalAbono').style.display = 'none';
}

// Función para mostrar el modal de detalle
function mostrarModalDetalle(data) {
    const modal = document.getElementById('modalDetalle');
    const modalBody = modal.querySelector('.modal-body');
    
    let productosHtml = '';
    
    // Mostrar productos agrupados por fecha
        data.productos.forEach(grupoFecha => {
            productosHtml += `
                <div class="fecha-grupo mb-4">
                    <h6 class="fecha-header bg-light p-2 rounded">
                        <strong>Fecha de Compra: ${new Date(grupoFecha.fecha).toLocaleDateString('es-ES')}</strong>
                        <span class="float-right">Total (${(localStorage.getItem('monedaActual')||'USD')==='USD' ? 'USD' : 'Bs'}): ${((localStorage.getItem('monedaActual')||'USD')==='USD' ? `$${parseFloat(grupoFecha.total_fecha).toFixed(2)}` : `Bs ${(parseFloat(grupoFecha.total_fecha)*(parseFloat(localStorage.getItem('tasaCambio'))||36)).toFixed(2)}`)}</span>
                    </h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-secondary">
                             <tr>
                                 <th>Fecha y Hora</th>
                                 <th>Producto</th>
                                 <th>Cantidad</th>
                                 <th>Subtotal (${(localStorage.getItem('monedaActual')||'USD')==='USD' ? 'USD' : 'Bs'})</th>
                             </tr>
                         </thead>
                        <tbody>
            `;
            
            grupoFecha.productos.forEach(producto => {
                 // Formatear fecha y hora
                 const fechaCompleta = new Date(grupoFecha.fecha + ' ' + producto.hora_compra);
                 const fechaFormateada = fechaCompleta.toLocaleDateString('es-ES') + ' ' + producto.hora_compra;
                 
                 productosHtml += `
                     <tr>
                         <td>${fechaFormateada}</td>
                         <td>${producto.producto}</td>
                         <td>${producto.cantidad}</td>
                         <td>${((localStorage.getItem('monedaActual')||'USD')==='USD' ? `$${parseFloat(producto.subtotal).toFixed(2)}` : `Bs ${(parseFloat(producto.subtotal)*(parseFloat(localStorage.getItem('tasaCambio'))||36)).toFixed(2)}`)}</td>
                     </tr>
                 `;
             });
            
            productosHtml += `
                        </tbody>
                    </table>
                </div>
            `;
        });
    
    let abonosHtml = '';
    if (data.abonos.length > 0) {
        data.abonos.forEach(abono => {
            abonosHtml += `
                <tr>
                    <td>${new Date(abono.fecha_abono).toLocaleDateString()}</td>
                    <td>${((localStorage.getItem('monedaActual')||'USD')==='USD' ? `$${parseFloat(abono.monto).toFixed(2)}` : `Bs ${(parseFloat(abono.monto)*(parseFloat(localStorage.getItem('tasaCambio'))||36)).toFixed(2)}`)}</td>
                    <td>${abono.metodo_pago}</td>
                    <td>${abono.observaciones || '-'}</td>
                </tr>
            `;
        });
    } else {
        abonosHtml = '<tr><td colspan="4" class="text-center">No hay abonos registrados</td></tr>';
    }
    
    modalBody.innerHTML = `
        <div class="cliente-info mb-4 p-3 bg-light rounded">
            <div class="row">
                <div class="col-md-6">
                    <strong>Cliente:</strong> ${data.resumen.cliente}
                </div>
                <div class="col-md-6">
                    <strong>Fecha Factura:</strong> ${data.resumen.fecha_factura}
                </div>
            </div>
        </div>
        
        <h5 class="mb-3">Productos por Fecha de Compra</h5>
        <div class="row">
            <div class="col-md-8">
                ${productosHtml}
            </div>
            <div class="col-md-4">
                <h5 class="mb-3">Historial de Abonos</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${abonosHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="resumen-totales mb-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="summary-card text-center" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                        <h6>Total Factura (${(localStorage.getItem('monedaActual')||'USD')==='USD' ? 'USD' : 'Bs'})</h6>
                        <h4>${((localStorage.getItem('monedaActual')||'USD')==='USD' ? `$${parseFloat(data.resumen.total_factura).toFixed(2)}` : `Bs ${(parseFloat(data.resumen.total_factura)*(parseFloat(localStorage.getItem('tasaCambio'))||36)).toFixed(2)}`)}</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card text-center" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                        <h6>Total Abonado (${(localStorage.getItem('monedaActual')||'USD')==='USD' ? 'USD' : 'Bs'})</h6>
                        <h4>${((localStorage.getItem('monedaActual')||'USD')==='USD' ? `$${parseFloat(data.resumen.total_abonado).toFixed(2)}` : `Bs ${(parseFloat(data.resumen.total_abonado)*(parseFloat(localStorage.getItem('tasaCambio'))||36)).toFixed(2)}`)}</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card text-center" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <h6>Saldo Pendiente (${(localStorage.getItem('monedaActual')||'USD')==='USD' ? 'USD' : 'Bs'})</h6>
                        <h4>${((localStorage.getItem('monedaActual')||'USD')==='USD' ? `$${parseFloat(data.resumen.saldo_pendiente).toFixed(2)}` : `Bs ${(parseFloat(data.resumen.saldo_pendiente)*(parseFloat(localStorage.getItem('tasaCambio'))||36)).toFixed(2)}`)}</h4>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
    window.modalDetalleContext = 'detalle';
    window.ultimoDetalleCuentasData = data;
    // Sincronizar texto del botón de moneda en el encabezado del modal
    const btnToggleModalDetalle2 = document.getElementById('btnToggleMonedaCajeroDetalle');
    if (btnToggleModalDetalle2) {
        const m = getMonedaActual();
        btnToggleModalDetalle2.textContent = 'Moneda: ' + (m === 'USD' ? 'USD' : 'Bs');
    }
    
    // Cerrar modal al hacer clic fuera de él
    modal.onclick = function(event) {
        if (event.target === modal) {
            cerrarModalDetalle();
        }
    }
}

// Función para mostrar mensajes
function mostrarMensaje(mensaje, tipo) {
    // Crear elemento de alerta
    const alert = document.createElement('div');
    alert.className = `alert alert-${tipo === 'error' ? 'danger' : 'success'} alert-dismissible fade show`;
    alert.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Insertar al inicio del container
    const container = document.querySelector('.container');
    container.insertBefore(alert, container.firstChild);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Precargar tasa desde configuración
    cargarTasaDesdeConfiguracionCajero();
    cargarCuentas();
    // Limitar fechas a hoy en los inputs date (evitar futuro)
    const hoyDate = new Date();
    const pad2 = n => String(n).padStart(2, '0');
    const hoyStr = `${hoyDate.getFullYear()}-${pad2(hoyDate.getMonth()+1)}-${pad2(hoyDate.getDate())}`;
    document.querySelectorAll('input[type="date"]').forEach(el => {
        el.max = hoyStr;
        if (el.value && el.value > hoyStr) {
            el.value = hoyStr;
        }
    });
    
    // Filtros
    document.getElementById('filtroCliente').addEventListener('input', filtrarCuentas);
    document.getElementById('filtroEstado').addEventListener('change', filtrarCuentas);
    document.getElementById('filtroFecha').addEventListener('change', filtrarCuentas);
    
    // Botón limpiar filtros
    document.getElementById('btn-limpiar-filtros').addEventListener('click', limpiarFiltros);

    // Toggle de moneda en cabecera
    const btnToggle = document.getElementById('btnToggleMonedaCuentas');
    if (btnToggle) {
        const syncBtnText = () => {
            const m = getMonedaActual();
            btnToggle.textContent = 'Moneda: ' + (m === 'USD' ? 'USD' : 'Bs');
        };
        syncBtnText();
        btnToggle.addEventListener('click', () => {
            const actual = getMonedaActual();
            const nuevo = actual === 'USD' ? 'Bs' : 'USD';
            localStorage.setItem('monedaActual', nuevo);
            syncBtnText();
            mostrarCuentas();
            actualizarResumen();
        });
    }

    // Toggle de moneda en modal Detalle/Historial
    const btnToggleModalDetalle = document.getElementById('btnToggleMonedaCajeroDetalle');
    if (btnToggleModalDetalle) {
        const syncBtnTextDetalle = () => {
            const m = getMonedaActual();
            btnToggleModalDetalle.textContent = 'Moneda: ' + (m === 'USD' ? 'USD' : 'Bs');
        };
        syncBtnTextDetalle();
        btnToggleModalDetalle.addEventListener('click', () => {
            const actual = getMonedaActual();
            const nuevo = actual === 'USD' ? 'Bs' : 'USD';
            localStorage.setItem('monedaActual', nuevo);
            syncBtnTextDetalle();
            if (window.modalDetalleContext === 'detalle' && window.ultimoDetalleCuentasData) {
                mostrarModalDetalle(window.ultimoDetalleCuentasData);
            } else if (window.modalDetalleContext === 'historial' && window.ultimoHistorialCuentas) {
                mostrarModalHistorial(window.ultimoHistorialCuentas);
            } else if (window.modalDetalleContext === 'combinado' && window.ultimoDetalleCuentasData && window.ultimoHistorialCuentas) {
                mostrarModalDetalleCombinado(window.ultimoDetalleCuentasData, window.ultimoHistorialCuentas);
            }
        });
    }

    // Toggle de moneda en modal Abono
    const btnToggleModalAbono = document.getElementById('btnToggleMonedaCajeroAbono');
    if (btnToggleModalAbono) {
        const syncBtnTextAbono = () => {
            const m = getMonedaActual();
            btnToggleModalAbono.textContent = 'Moneda: ' + (m === 'USD' ? 'USD' : 'Bs');
        };
        syncBtnTextAbono();
        btnToggleModalAbono.addEventListener('click', () => {
            const actual = getMonedaActual();
            const nuevo = actual === 'USD' ? 'Bs' : 'USD';
            localStorage.setItem('monedaActual', nuevo);
            syncBtnTextAbono();
            const saldoInput = document.getElementById('saldoAbono');
            if (saldoInput) {
                const num = parseFloat(saldoInput.dataset.saldoNumerico || document.getElementById('modalAbono').dataset.totalSaldo || '0');
                saldoInput.value = formatMonto(num);
            }
        });
    }

    // Validación de montos y observaciones en modal Abono (Cajero)
    const toDecimal = (str) => {
        const s = (str || '').replace(/[^0-9.,]/g, '').replace(/,/g, '.');
        const parts = s.split('.');
        if (parts.length > 2) {
            return parts[0] + '.' + parts.slice(1).join('');
        }
        return s;
    };
    const capitalizeFirst = (str) => {
        const s = (str || '').trim();
        if (!s) return '';
        return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();
    };
    const usdInput = document.getElementById('montoAbonoUsd');
    const bsInput = document.getElementById('montoAbonoBs');
    const obsInput = document.getElementById('observacionesAbono');
    [usdInput, bsInput].forEach(el => {
        if (!el) return;
        el.addEventListener('input', (e) => {
            const v = toDecimal(e.target.value);
            if (v !== e.target.value) e.target.value = v;
            actualizarEquivalenteAbono();
        });
        el.addEventListener('blur', (e) => {
            let n = parseFloat(e.target.value);
            if (isNaN(n) || n < 0) n = 0;
            const step = parseFloat(e.target.getAttribute('step') || '0.01');
            e.target.value = n.toFixed(step >= 1 ? 0 : 2);
            actualizarEquivalenteAbono();
        });
    });
    if (obsInput) {
        obsInput.addEventListener('blur', (e) => {
            e.target.value = capitalizeFirst(e.target.value);
        });
    }
});
function verDetalleCliente(idCliente) {
    Promise.all([
        fetch(`../logica/obtener_detalle_cliente.php?id_cliente=${idCliente}`).then(r => r.json()),
        fetch(`../logica/obtener_historial_cliente.php?id_cliente=${idCliente}`).then(r => r.json())
    ])
    .then(([data, historial]) => {
        if (data.error) { mostrarMensaje(data.error, 'error'); return; }
        if (historial.error) { mostrarMensaje(historial.error, 'error'); return; }
        // Guardar contexto combinado y re-renderizar con función dedicada
        window.modalDetalleContext = 'combinado';
        window.ultimoDetalleCuentasData = data;
        window.ultimoHistorialCuentas = historial;
        mostrarModalDetalleCombinado(data, historial);
    })
    .catch(e => { console.error(e); mostrarMensaje('Error al cargar el detalle del cliente', 'error'); });
}

// Render del modal con vista combinada (resumen + historial) con moneda dinámica
function mostrarModalDetalleCombinado(data, historial) {
    const modal = document.getElementById('modalDetalle');
    const body = modal.querySelector('.modal-body');
    const monedaLabel = getMonedaActual() === 'USD' ? 'USD' : 'Bs';

    const resumenHtml = `
        <div class="p-2">
            <h4>Resumen de Cuenta</h4>
            <p><strong>Total a pagar (${monedaLabel}):</strong> ${formatMonto(data.total_factura)}</p>
            <p><strong>Total abonado (${monedaLabel}):</strong> ${formatMonto(data.total_abonado)}</p>
            <p><strong>Saldo pendiente (${monedaLabel}):</strong> ${formatMonto(data.saldo_pendiente)}</p>
            <h5>Fechas de abono</h5>
            ${data.fechas_abono && data.fechas_abono.length ?
                `<ul>` + data.fechas_abono.map(f => `<li>${f.fecha} - ${formatMonto(f.monto)}</li>`).join('') + `</ul>`
                : '<p>Sin abonos registrados.</p>'}
        </div>`;

    const facturasHtml = `
        <div class="p-2">
            <h4>Facturas por fecha</h4>
            ${Array.isArray(historial) && historial.length ? `
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Total (${monedaLabel})</th>
                                <th>Abonado (${monedaLabel})</th>
                                <th>Saldo (${monedaLabel})</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${historial.map(h => `
                                <tr>
                                    <td>${h.fecha}</td>
                                    <td>${formatMonto(h.total_factura)}</td>
                                    <td>${formatMonto(h.total_abonado)}</td>
                                    <td>${formatMonto(h.saldo_pendiente)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            ` : '<p>No hay facturas registradas.</p>'}
        </div>`;

    body.innerHTML = resumenHtml + facturasHtml;
    modal.style.display = 'block';
    const btnToggleModalDetalle = document.getElementById('btnToggleMonedaCajeroDetalle');
    if (btnToggleModalDetalle) {
        const m = getMonedaActual();
        btnToggleModalDetalle.textContent = 'Moneda: ' + (m === 'USD' ? 'USD' : 'Bs');
    }
}

function abrirModalAbonoCliente(idCliente, nombreCliente) {
    fetch(`../logica/obtener_creditos_cliente.php?id_cliente=${idCliente}`)
        .then(r => r.json())
        .then(creditos => {
            if (creditos.error) { mostrarMensaje(creditos.error, 'error'); return; }
            document.getElementById('clienteAbono').value = nombreCliente;
            const select = document.getElementById('fechaFacturaAbono');
            select.innerHTML = '<option value="">Seleccione una factura...</option>';
            creditos.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_credito;
                opt.textContent = `${c.fecha} - Saldo $${parseFloat(c.saldo).toFixed(2)}`;
                opt.dataset.saldo = parseFloat(c.saldo).toFixed(2);
                select.appendChild(opt);
            });
            // Guardar id del cliente y saldo total para permitir abono general
            const totalSaldo = Array.isArray(creditos) ? creditos.reduce((sum, c) => sum + parseFloat(c.saldo || 0), 0) : 0;
            const modal = document.getElementById('modalAbono');
            modal.dataset.idCliente = idCliente;
            modal.dataset.totalSaldo = totalSaldo.toFixed(2);
            // Mostrar saldo total en el campo visual
            const saldoInput = document.getElementById('saldoAbono');
            saldoInput.value = `${formatMonto(totalSaldo)}`;
            saldoInput.dataset.saldoNumerico = totalSaldo;
            select.onchange = function() {
                const sel = select.options[select.selectedIndex];
                document.getElementById('idCreditoAbono').value = sel.value;
                const saldoNumerico = sel.dataset.saldo ? parseFloat(sel.dataset.saldo) : 0;
                const saldoInput = document.getElementById('saldoAbono');
                saldoInput.value = sel.dataset.saldo ? `${formatMonto(saldoNumerico)}` : '';
                saldoInput.dataset.saldoNumerico = saldoNumerico;
                const montoUsdInput = document.getElementById('montoAbonoUsd');
                const montoBsInput = document.getElementById('montoAbonoBs');
                if (montoUsdInput) {
                    montoUsdInput.max = sel.dataset.saldo ? saldoNumerico : totalSaldo.toFixed(2);
                }
                if (montoBsInput) {
                    const tasa = getTasaCambio();
                    const maxBs = (sel.dataset.saldo ? saldoNumerico : totalSaldo) * tasa;
                    montoBsInput.max = maxBs.toFixed(2);
                }
                actualizarEquivalenteAbono();
            };
            const montoUsdInput = document.getElementById('montoAbonoUsd');
            const montoBsInput = document.getElementById('montoAbonoBs');
            if (montoUsdInput) {
                montoUsdInput.value = '';
                montoUsdInput.max = totalSaldo.toFixed(2);
                montoUsdInput.addEventListener('input', actualizarEquivalenteAbono);
            }
            if (montoBsInput) {
                montoBsInput.value = '';
                const tasa = getTasaCambio();
                montoBsInput.max = (totalSaldo * tasa).toFixed(2);
                montoBsInput.addEventListener('input', actualizarEquivalenteAbono);
            }
            actualizarEquivalenteAbono();
            document.getElementById('metodoPago').value = '';
            document.getElementById('observacionesAbono').value = '';
            document.getElementById('idCreditoAbono').value = '';
            document.getElementById('modalAbono').style.display = 'block';
        })
        .catch(e => { console.error(e); mostrarMensaje('Error al preparar el abono', 'error'); });
}

async function procesarAbono() {
    const idCredito = document.getElementById('idCreditoAbono').value;
    const montoUsd = parseFloat(document.getElementById('montoAbonoUsd')?.value || '0') || 0;
    const montoBs = parseFloat(document.getElementById('montoAbonoBs')?.value || '0') || 0;
    const tasa = getTasaCambio();
    if (!(tasa > 0)) { mostrarMensaje('Configura la tasa de cambio en localStorage.tasaCambio', 'error'); return; }
    const montoBsEnUsd = montoBs > 0 ? (montoBs / tasa) : 0;
    const montoTotalUsd = montoUsd + montoBsEnUsd;
    const metodoPago = document.getElementById('metodoPago').value;
    const observaciones = document.getElementById('observacionesAbono').value;
    if (!(montoTotalUsd > 0)) { mostrarMensaje('Ingrese monto en USD o Bs', 'error'); return; }
    const modal = document.getElementById('modalAbono');
    const totalSaldoCliente = parseFloat(modal.dataset.totalSaldo || '0');
    try {
        if (idCredito) {
            const saldoNumerico = parseFloat(document.getElementById('saldoAbono').dataset.saldoNumerico || '0');
            if (montoTotalUsd > saldoNumerico) { mostrarMensaje('El total del abono excede el saldo pendiente', 'error'); return; }
            // Registrar parte USD
            if (montoUsd > 0) {
                const fdUsd = new FormData();
                fdUsd.append('id_credito', idCredito);
                fdUsd.append('monto_abono', montoUsd.toFixed(2));
                fdUsd.append('metodo_pago', metodoPago);
                fdUsd.append('observaciones', (observaciones ? observaciones + ' ' : '') + '(Parte en USD)');
                const r1 = await fetch('../logica/procesar_abono.php', { method: 'POST', body: fdUsd });
                const d1 = await r1.json();
                if (!d1.success) { mostrarMensaje(d1.message || 'Error al procesar abono USD', 'error'); return; }
            }
            // Registrar parte Bs (convertida a USD)
            if (montoBsEnUsd > 0) {
                const fdBs = new FormData();
                fdBs.append('id_credito', idCredito);
                fdBs.append('monto_abono', montoBsEnUsd.toFixed(2));
                fdBs.append('metodo_pago', metodoPago);
                fdBs.append('observaciones', (observaciones ? observaciones + ' ' : '') + `(Parte en Bs: Bs ${montoBs.toFixed(2)} a tasa ${tasa})`);
                const r2 = await fetch('../logica/procesar_abono.php', { method: 'POST', body: fdBs });
                const d2 = await r2.json();
                if (!d2.success) { mostrarMensaje(d2.message || 'Error al procesar abono en Bs', 'error'); return; }
            }
            mostrarMensaje('Abono procesado', 'success');
            cerrarModalAbono();
            cargarCuentas();
        } else {
            const idCliente = modal.dataset.idCliente;
            if (!idCliente) { mostrarMensaje('No se pudo identificar el cliente para el abono general', 'error'); return; }
            if (montoTotalUsd > totalSaldoCliente) { mostrarMensaje('El total del abono excede el saldo pendiente', 'error'); return; }
            if (montoUsd > 0) {
                const fdUsd = new FormData();
                fdUsd.append('id_cliente', idCliente);
                fdUsd.append('monto_abono', montoUsd.toFixed(2));
                fdUsd.append('metodo_pago', metodoPago);
                fdUsd.append('observaciones', (observaciones ? observaciones + ' ' : '') + '(Parte en USD)');
                const r1 = await fetch('../logica/procesar_abono_general.php', { method: 'POST', body: fdUsd });
                const d1 = await r1.json();
                if (!d1.success) { mostrarMensaje(d1.message || 'Error al procesar abono USD', 'error'); return; }
            }
            if (montoBsEnUsd > 0) {
                const fdBs = new FormData();
                fdBs.append('id_cliente', idCliente);
                fdBs.append('monto_abono', montoBsEnUsd.toFixed(2));
                fdBs.append('metodo_pago', metodoPago);
                fdBs.append('observaciones', (observaciones ? observaciones + ' ' : '') + `(Parte en Bs: Bs ${montoBs.toFixed(2)} a tasa ${tasa})`);
                const r2 = await fetch('../logica/procesar_abono_general.php', { method: 'POST', body: fdBs });
                const d2 = await r2.json();
                if (!d2.success) { mostrarMensaje(d2.message || 'Error al procesar abono en Bs', 'error'); return; }
            }
            cerrarModalAbono();
            cargarCuentas();
        }
    } catch (err) { console.error(err); mostrarMensaje('Error de conexión al procesar abono', 'error'); }
}