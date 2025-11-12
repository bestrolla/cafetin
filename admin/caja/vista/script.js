// Estado de moneda y tasa de cambio
let monedaActual = 'USD';
let tasaCambio = parseFloat(localStorage.getItem('tasaCambio')) || 36;
const monedaGuardada = localStorage.getItem('monedaActual');
if (monedaGuardada === 'USD' || monedaGuardada === 'VES') {
    monedaActual = monedaGuardada;
}

document.addEventListener('DOMContentLoaded', function() {
    // Cargar tasa desde Configuración (Admin) y sincronizar vista
    (async function cargarTasaDesdeConfiguracionAdminCaja(){
        try {
            const resp = await fetch('../../configuracion/logica/obtener_configuraciones.php');
            const data = await resp.json();
            if (data && data.success && data.configuraciones && data.configuraciones.tasa_dolar) {
                const tasa = parseFloat(data.configuraciones.tasa_dolar);
                if (Number.isFinite(tasa) && tasa > 0) {
                    localStorage.setItem('tasaCambio', tasa.toString());
                    tasaCambio = tasa; // actualizar variable local
                    try { aplicarMonedaEnUI(); } catch(_) {}
                }
            }
        } catch(err) {
            console.warn('No se pudo cargar la tasa desde configuración (Admin Caja):', err);
        }
    })();
    // Inicializar pestañas
    initializeTabs();
    // Ajustar visibilidad de filtros según pestaña inicial
    const initialTab = document.querySelector('.tab-button.active')?.dataset.tab;
    const filters = document.querySelector('.filters');
    if (filters && (initialTab === 'reportes' || initialTab === 'graficos')) {
        filters.style.display = 'none';
    }

    // Limitar inputs de fecha a hoy (no permitir fechas futuras)
    const hoyDate = new Date();
    const pad2 = n => String(n).padStart(2, '0');
    const hoyStr = `${hoyDate.getFullYear()}-${pad2(hoyDate.getMonth() + 1)}-${pad2(hoyDate.getDate())}`;
    document.querySelectorAll('input[type="date"]').forEach(el => {
        el.max = hoyStr;
        if (el.value && el.value > hoyStr) {
            el.value = hoyStr;
        }
    });

    // Establecer por defecto el rango de fechas generales a hoy
    const fechaInicioEl = document.getElementById('fecha_inicio');
    const fechaFinEl = document.getElementById('fecha_fin');
    if (fechaInicioEl && !fechaInicioEl.value) fechaInicioEl.value = hoyStr;
    if (fechaFinEl && !fechaFinEl.value) fechaFinEl.value = hoyStr;

    // Cargar datos iniciales
    cargarVentas();
    aplicarMonedaEnUI();
    const btnToggleMoneda = document.getElementById('btn-toggle-moneda');
    if (btnToggleMoneda) {
        btnToggleMoneda.addEventListener('click', toggleMoneda);
    }
    // Toggle de moneda en modal de detalle (Admin Caja)
    const btnToggleModalDetalle = document.getElementById('btnToggleMonedaAdminCajaDetalle');
    if (btnToggleModalDetalle) {
        const syncBtnText = () => {
            btnToggleModalDetalle.textContent = 'Moneda: ' + (monedaActual === 'USD' ? 'USD' : 'Bs');
        };
        syncBtnText();
        btnToggleModalDetalle.addEventListener('click', () => {
            toggleMoneda();
            if (window.ultimoDetalleCaja) {
                mostrarModalDetalle(window.ultimoDetalleCaja);
            }
        });
    }
    
    // Event listeners para filtros
    document.getElementById('filtrar').addEventListener('click', function() {
        const activeTab = document.querySelector('.tab-button.active').dataset.tab;
        if (activeTab === 'ventas') {
            cargarVentas();
        } else if (activeTab === 'deudas') {
            cargarDeudas();
        }
    });

    // Botón para borrar filtros (fechas y búsqueda)
    const btnLimpiar = document.getElementById('limpiar_filtros');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            const fechaInicioEl = document.getElementById('fecha_inicio');
            const fechaFinEl = document.getElementById('fecha_fin');
            const buscarEl = document.getElementById('buscar_general');
            if (fechaInicioEl) fechaInicioEl.value = '';
            if (fechaFinEl) fechaFinEl.value = '';
            if (buscarEl) buscarEl.value = '';

            const activeTab = document.querySelector('.tab-button.active').dataset.tab;
            if (activeTab === 'ventas') {
                cargarVentas();
            } else if (activeTab === 'deudas') {
                cargarDeudas();
            }
        });
    }

    // Filtros específicos de Deudas
    const dFiltrar = document.getElementById('deudas_filtrar');
    if (dFiltrar) {
        dFiltrar.addEventListener('click', function() {
            cargarDeudas();
        });
    }
    const dLimpiar = document.getElementById('deudas_limpiar');
    if (dLimpiar) {
        dLimpiar.addEventListener('click', function() {
            const dBuscar = document.getElementById('deudas_buscar');
            const dEst = document.getElementById('deudas_estado');
            const dOrd = document.getElementById('deudas_orden');
            if (dBuscar) dBuscar.value = '';
            if (dEst) dEst.value = 'todos';
            if (dOrd) dOrd.value = 'mas';
            cargarDeudas();
        });
    }

    // Controles Reportes (default y listeners)
    const rpPeriodo = document.getElementById('reporte_periodo');
    const rpFecha = document.getElementById('reporte_fecha');
    const rpBtn = document.getElementById('reporte_actualizar');
    const rpClear = document.getElementById('reporte_limpiar');
    // Setear fecha base por defecto = hoy si está vacío
    if (rpFecha && !rpFecha.value) {
        const hoy = new Date();
        const pad = n => String(n).padStart(2, '0');
        rpFecha.value = `${hoy.getFullYear()}-${pad(hoy.getMonth()+1)}-${pad(hoy.getDate())}`;
    }
    if (rpBtn) rpBtn.addEventListener('click', cargarReportes);
    if (rpClear) rpClear.addEventListener('click', function(){
        if (rpPeriodo) rpPeriodo.value = 'dia';
        if (rpFecha) rpFecha.value = '';
        const desdeEl = document.getElementById('reporte_rango_desde');
        const hastaEl = document.getElementById('reporte_rango_hasta');
        if (desdeEl) desdeEl.textContent = '—';
        if (hastaEl) hastaEl.textContent = '—';
        const totalUsdEl = document.getElementById('reporte-total-usd');
        const totalBsEl = document.getElementById('reporte-total-bs');
        const topEl = document.getElementById('reporte-top');
        const bottomEl = document.getElementById('reporte-bottom');
        const tbody = document.querySelector('#tabla-reporte-productos tbody');
        if (totalUsdEl) totalUsdEl.textContent = '$0.00';
        if (totalBsEl) totalBsEl.textContent = 'Bs 0.00';
        if (topEl) topEl.textContent = '—';
        if (bottomEl) bottomEl.textContent = '—';
        if (tbody) tbody.innerHTML = '';
    });
});

function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Remover clase active de todos los botones y contenidos
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Agregar clase active al botón y contenido seleccionado
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
            
            // Ocultar/mostrar filtros generales según la pestaña
            const filters = document.querySelector('.filters');
            if (filters) {
                filters.style.display = (targetTab === 'reportes' || targetTab === 'graficos' || targetTab === 'deudas') ? 'none' : '';
            }

            // Cargar datos según la pestaña seleccionada
            if (targetTab === 'ventas') {
                cargarVentas();
            } else if (targetTab === 'deudas') {
                cargarDeudas();
            } else if (targetTab === 'reportes') {
                cargarReportes();
            } else if (targetTab === 'graficos') {
                if (!window.graficosInicializado) {
                    initGraficosControls();
                    window.graficosInicializado = true;
                }
                actualizarGrafico();
            }
        });
    });
}

// --- Ventas: controles de modo y cabecera dinámica ---
function getVentasModo() {
    const sel = document.getElementById('ventas_modo');
    const stored = localStorage.getItem('ventas_modo');
    const value = sel ? sel.value : (stored || 'producto');
    return value === 'venta' ? 'venta' : 'producto';
}

function getVentasVentana() {
    const sel = document.getElementById('ventas_ventana');
    const stored = localStorage.getItem('ventas_ventana') || 'dia';
    const value = sel ? sel.value : stored;
    return value; // 'dia' | '60' | '30' | '10'
}

function actualizarCabeceraVentas() {
    const modo = getVentasModo();
    const theadRow = document.querySelector('#tabla-ventas thead tr');
    if (!theadRow) return;
    if (modo === 'producto') {
        theadRow.innerHTML = `
            <th>Producto</th>
            <th>Clientes</th>
            <th>Cantidad total</th>
            <th>Total</th>
            <th>Última venta</th>
            <th>Acción</th>
        `;
    } else {
        theadRow.innerHTML = `
            <th>Cliente</th>
            <th>Cajero</th>
            <th>Productos</th>
            <th>Cantidad total</th>
            <th>Total</th>
            <th>Fecha</th>
            <th>Ver</th>
        `;
    }
}

function initVentasControls() {
    const modoSel = document.getElementById('ventas_modo');
    const ventSel = document.getElementById('ventas_ventana');
    // Inicializar valores desde localStorage
    const storedModo = localStorage.getItem('ventas_modo');
    const storedVent = localStorage.getItem('ventas_ventana');
    if (modoSel) modoSel.value = storedModo || 'item';
    if (ventSel) ventSel.value = storedVent || 'dia';
    actualizarCabeceraVentas();
    // Eventos
    if (modoSel) modoSel.addEventListener('change', () => {
        localStorage.setItem('ventas_modo', modoSel.value);
        actualizarCabeceraVentas();
        cargarVentas();
    });
    if (ventSel) ventSel.addEventListener('change', () => {
        localStorage.setItem('ventas_ventana', ventSel.value);
        if (getVentasModo() === 'venta') {
            cargarVentas();
        }
    });
}

// Llamar init de controles al cargar pestañas o DOM
document.addEventListener('DOMContentLoaded', function() {
    initVentasControls();
});

function cargarVentas() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const buscarGeneral = document.getElementById('buscar_general')?.value || '';
    const modo = getVentasModo();
    const ventana = getVentasVentana();
    
    let url = '../logica/obtener_ventas.php';
    if (fechaInicio && fechaFin) {
        url += `?start_date=${fechaInicio}&end_date=${fechaFin}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#tabla-ventas tbody');
            tbody.innerHTML = '';

            if (data.error) {
                const colSpan = modo === 'producto' ? 6 : 7;
                tbody.innerHTML = `<tr><td colspan="${colSpan}">Error: ${data.error}</td></tr>`;
                return;
            }

            // Filtro texto único (nombre, apellido, fecha/hora, cajero, producto, id)
            const query = buscarGeneral.trim().toLowerCase();
            const ventasFiltradas = query ? data.filter(venta => {
                const nombreCompleto = `${venta.cliente_nombre || ''} ${venta.cliente_apellido || ''}`.trim().toLowerCase();
                const fechaStr = new Date(venta.fecha_venta).toLocaleString('es-ES').toLowerCase();
                const cajeroCompleto = `${venta.cajero_nombre || ''} ${venta.cajero_apellido || ''}`.trim().toLowerCase();
                const productoStr = (venta.producto_nombre || '').toLowerCase();
                const idStr = String(venta.id_venta || '').toLowerCase();
                return (
                    nombreCompleto.includes(query) ||
                    fechaStr.includes(query) ||
                    cajeroCompleto.includes(query) ||
                    productoStr.includes(query) ||
                    idStr.includes(query)
                );
            }) : data;
            actualizarCabeceraVentas();

            if (modo === 'producto') {
                // Agrupar por producto
                const byProducto = new Map();
                ventasFiltradas.forEach(v => {
                    const prod = v.producto_nombre || 'N/A';
                    const fecha = new Date(v.fecha_venta);
                    const key = prod;
                    const entry = byProducto.get(key) || {
                        producto: prod,
                        clientes: new Set(),
                        cantidadTotal: 0,
                        totalUSD: 0,
                        ultimaFecha: fecha
                    };
                    const clienteNombre = `${v.cliente_nombre || ''}${v.cliente_apellido ? ' ' + v.cliente_apellido : ''}`.trim();
                    if (clienteNombre) entry.clientes.add(clienteNombre);
                    entry.cantidadTotal += parseInt(v.cantidad || 0, 10);
                    entry.totalUSD += parseFloat(v.total || 0);
                    if (fecha > entry.ultimaFecha) entry.ultimaFecha = fecha;
                    byProducto.set(key, entry);
                });
                const grupos = Array.from(byProducto.values())
                    .sort((a,b) => b.ultimaFecha - a.ultimaFecha);

                grupos.forEach(g => {
                    const row = document.createElement('tr');
                    const totalBs = g.totalUSD * tasaCambio;
                    const productoArg = (g.producto || '').replace(/'/g, "\\'");
                    row.innerHTML = `
                        <td>${g.producto}</td>
                        <td>${g.clientes.size}</td>
                        <td>${g.cantidadTotal}</td>
                        <td>${monedaActual === 'USD' ? `$${g.totalUSD.toFixed(2)}` : `Bs ${totalBs.toFixed(2)}`}</td>
                        <td>${g.ultimaFecha.toLocaleString('es-ES')}</td>
                        <td><button class="btn" onclick="verDetalleProducto('${productoArg}')">Acción</button></td>
                    `;
                    tbody.appendChild(row);
                });
                if (grupos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#666;">Sin resultados</td></tr>';
                }
            } else {
                // Agrupar por ventana de tiempo dentro del mismo día y cliente
                const pad = n => String(n).padStart(2, '0');
                const toYMD = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
                const windowMinutes = ventana === 'dia' ? null : parseInt(ventana, 10);
                const windowMs = windowMinutes ? windowMinutes * 60 * 1000 : null;
                // Agrupar primero por cliente y día
                const byClientDay = new Map();
                ventasFiltradas.forEach(v => {
                    const nombre = `${v.cliente_nombre || ''}${v.cliente_apellido ? ' ' + v.cliente_apellido : ''}`.trim();
                    const fecha = new Date(v.fecha_venta);
                    const key = `${nombre}|${toYMD(fecha)}`;
                    const arr = byClientDay.get(key) || [];
                    arr.push({
                        id_venta: v.id_venta,
                        cliente: nombre,
                        cajero: `${v.cajero_nombre || ''}${v.cajero_apellido ? ' ' + v.cajero_apellido : ''}`.trim(),
                        cantidad: parseInt(v.cantidad || 0, 10),
                        totalUSD: parseFloat(v.total || 0),
                        fecha: fecha
                    });
                    byClientDay.set(key, arr);
                });

                const grupos = [];
                byClientDay.forEach(arr => {
                    // Ordenar por fecha asc para construir ventanas
                    arr.sort((a,b) => a.fecha - b.fecha);
                    if (!arr.length) return;
                    let current = {
                        idVentaRef: arr[0].id_venta,
                        cliente: arr[0].cliente,
                        cajero: arr[0].cajero,
                        productos: 0,
                        cantidadTotal: 0,
                        totalUSD: 0,
                        fechaInicio: arr[0].fecha,
                        fechaFin: arr[0].fecha
                    };
                    let lastTime = arr[0].fecha.getTime();
                    arr.forEach((it, idx) => {
                        if (idx === 0) {
                            current.productos += 1;
                            current.cantidadTotal += it.cantidad;
                            current.totalUSD += it.totalUSD;
                            return;
                        }
                        const t = it.fecha.getTime();
                        const diff = t - lastTime;
                        const sameWindow = windowMs === null || diff <= windowMs; // null => todo el día
                        if (!sameWindow) {
                            // cerrar grupo y abrir otro
                            grupos.push(current);
                            current = {
                                idVentaRef: it.id_venta,
                                cliente: it.cliente,
                                cajero: it.cajero,
                                productos: 1,
                                cantidadTotal: it.cantidad,
                                totalUSD: it.totalUSD,
                                fechaInicio: it.fecha,
                                fechaFin: it.fecha
                            };
                        } else {
                            current.productos += 1;
                            current.cantidadTotal += it.cantidad;
                            current.totalUSD += it.totalUSD;
                            current.fechaFin = it.fecha;
                        }
                        lastTime = t;
                    });
                    // push el último grupo
                    grupos.push(current);
                });

                const rows = grupos.sort((a,b) => b.fechaInicio - a.fechaInicio);
                rows.forEach(g => {
                    const row = document.createElement('tr');
                    const totalBs = g.totalUSD * tasaCambio;
                    const fechaStr = ventana === 'dia'
                        ? g.fechaInicio.toLocaleDateString('es-ES')
                        : `${g.fechaInicio.toLocaleTimeString('es-ES')} - ${g.fechaFin.toLocaleTimeString('es-ES')}`;
                    row.innerHTML = `
                        <td>${g.cliente}</td>
                        <td>${g.cajero}</td>
                        <td>${g.productos}</td>
                        <td>${g.cantidadTotal}</td>
                        <td>${monedaActual === 'USD' ? `$${g.totalUSD.toFixed(2)}` : `Bs ${totalBs.toFixed(2)}`}</td>
                        <td>${fechaStr}</td>
                        <td><button class="btn" onclick="verDetalleVenta(${g.idVentaRef})">Ver</button></td>
                    `;
                    tbody.appendChild(row);
                });
                if (rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#666;">Sin resultados</td></tr>';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const tbody = document.querySelector('#tabla-ventas tbody');
            const modo = getVentasModo();
            const colSpan = modo === 'producto' ? 6 : 7;
            tbody.innerHTML = `<tr><td colspan="${colSpan}">Error al cargar los datos</td></tr>`;
        });
}

function cargarDeudas() {
    // Deudas: usar filtros dedicados del módulo Deudas
    const buscarGeneral = document.getElementById('deudas_buscar')?.value.trim() || '';
    const filtroEstado = document.getElementById('deudas_estado')?.value || 'todos';
    const orden = document.getElementById('deudas_orden')?.value || 'mas';

    const params = new URLSearchParams();
    // Unifica búsqueda: si es todo dígitos, buscar por cédula; si no, aplica a nombre y apellido
    if (buscarGeneral) {
        const soloDigitos = /^\d+$/.test(buscarGeneral);
        if (soloDigitos) {
            params.append('buscar_cedula', buscarGeneral);
        } else {
            params.append('buscar_nombre', buscarGeneral);
            params.append('buscar_apellido', buscarGeneral);
        }
    }

    const url = `../logica/obtener_deudas.php${params.toString() ? '?' + params.toString() : ''}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data)) {
                console.error('Error:', data && data.error ? data.error : 'Respuesta inválida');
                const tbody = document.querySelector('#tabla-deudas tbody');
                if (tbody) tbody.innerHTML = '<tr><td colspan="9" class="text-center">Error al cargar deudas</td></tr>';
                return;
            }

            // Filtrado por estado (pendiente/parcial/todos)
            let lista = data;
            if (filtroEstado !== 'todos') {
                lista = lista.filter(d => (d.estado || '').toLowerCase() === filtroEstado);
            }

            // Ordenar por saldo pendiente (más/menos deuda)
            lista.sort((a, b) => {
                const sa = parseFloat(a.saldo_pendiente || 0);
                const sb = parseFloat(b.saldo_pendiente || 0);
                return orden === 'menos' ? sa - sb : sb - sa;
            });

            const tbody = document.querySelector('#tabla-deudas tbody');
            if (!tbody) return;
            tbody.innerHTML = '';

            if (lista.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center">No hay deudas registradas</td></tr>';
                return;
            }

            lista.forEach(deuda => {
                const saldo = parseFloat(deuda.saldo_pendiente);
                const estado = deuda.estado;
                let estadoClass = '';
                let estadoTexto = '';

                switch (estado) {
                    case 'pagado':
                        estadoClass = 'estado-pagado';
                        estadoTexto = 'Pagado';
                        break;
                    case 'parcial':
                        estadoClass = 'estado-parcial';
                        estadoTexto = 'Parcial';
                        break;
                    default:
                        estadoClass = 'estado-pendiente';
                        estadoTexto = 'Pendiente';
                }

                const row = document.createElement('tr');
                const fechaMostrar = deuda.fecha_factura ? new Date(deuda.fecha_factura).toLocaleDateString() : '';
                const totalUSD = parseFloat(deuda.total_factura);
                const abonadoUSD = parseFloat(deuda.total_abonado);
                const saldoUSD = parseFloat(saldo);
                const totalBs = totalUSD * tasaCambio;
                const abonadoBs = abonadoUSD * tasaCambio;
                const saldoBs = saldoUSD * tasaCambio;
                row.innerHTML = `
                    <td>${deuda.id_credito}</td>
                    <td>${deuda.cliente}</td>
                    <td>${fechaMostrar}</td>
                    <td>${deuda.total_productos} producto(s)</td>
                    <td>${monedaActual === 'USD' ? `$${totalUSD.toFixed(2)}` : `Bs ${totalBs.toFixed(2)}`}</td>
                    <td>${monedaActual === 'USD' ? `$${abonadoUSD.toFixed(2)}` : `Bs ${abonadoBs.toFixed(2)}`}</td>
                    <td>${monedaActual === 'USD' ? `$${saldoUSD.toFixed(2)}` : `Bs ${saldoBs.toFixed(2)}`}</td>
                    <td><span class="estado ${estadoClass}">${estadoTexto}</span></td>
                    <td>
                        <button class="btn" onclick="verDetalleDeuda(${deuda.id_cliente}, '${deuda.fecha_factura}')">Ver Detalle</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            const tbody = document.querySelector('#tabla-deudas tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="9" class="text-center">Error al cargar deudas</td></tr>';
        });
}

// ===== Reportes =====
function calcularRangoPorPeriodo(periodo, fechaBaseStr) {
    const hoy = new Date();
    let base = fechaBaseStr ? new Date(fechaBaseStr + 'T00:00:00') : hoy;
    // Si la fecha base es futura, usar hoy
    if (base > hoy) base = new Date(hoy);
    const pad = n => String(n).padStart(2, '0');
    const toYMD = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;

    let start = new Date(base);
    let end = new Date(base);

    switch (periodo) {
        case 'dia': {
            // Mostrar desde la fecha base seleccionada hasta hoy (granularidad diaria)
            start = new Date(base);
            end = new Date(hoy);
            break;
        }
        case 'semana': {
            // Mostrar todas las semanas del año base (del 1 de enero al 31 de diciembre)
            start = new Date(base.getFullYear(), 0, 1);
            end = new Date(base.getFullYear(), 11, 31);
            break;
        }
        case 'mes':
            // Mostrar todos los meses del año base (enero a diciembre)
            start = new Date(base.getFullYear(), 0, 1);
            end = new Date(base.getFullYear(), 11, 31);
            break;
        case 'anio':
            start = new Date(base.getFullYear(), 0, 1);
            end = new Date(base.getFullYear(), 11, 31);
            break;
        default:
            break;
    }
    // No permitir futuro para cualquier periodo que pueda calcularse hacia delante
    if (end > hoy) end = new Date(hoy);
    return { start: toYMD(start), end: toYMD(end) };
}

async function cargarReportes() {
    try {
        const periodo = document.getElementById('reporte_periodo')?.value || 'dia';
        const fechaBase = document.getElementById('reporte_fecha')?.value || '';
        const { start, end } = calcularRangoPorPeriodo(periodo, fechaBase);
        // Mostrar rango calculado
        const desdeEl = document.getElementById('reporte_rango_desde');
        const hastaEl = document.getElementById('reporte_rango_hasta');
        if (desdeEl) desdeEl.textContent = start;
        if (hastaEl) hastaEl.textContent = end;
        // Actualizar etiqueta de columna Total según moneda
        const colLabel = document.getElementById('reporte-col-total-label');
        if (colLabel) {
            colLabel.textContent = (monedaActual === 'USD') ? 'Total (USD)' : 'Total (Bs)';
        }

        const resp = await fetch(`../logica/obtener_reporte_ventas.php?start_date=${encodeURIComponent(start)}&end_date=${encodeURIComponent(end)}`);
        const data = await resp.json();
        if (!data || !data.success) {
            console.warn('No se pudo cargar el reporte:', data && data.error);
            return;
        }

        const totalUSD = parseFloat(data.total_usd || 0);
        const totalBs = totalUSD * tasaCambio;
        const totalUsdEl = document.getElementById('reporte-total-usd');
        const totalBsEl = document.getElementById('reporte-total-bs');
        if (totalUsdEl) totalUsdEl.textContent = `$${totalUSD.toFixed(2)}`;
        if (totalBsEl) totalBsEl.textContent = `Bs ${totalBs.toFixed(2)}`;

        const topEl = document.getElementById('reporte-top');
        const bottomEl = document.getElementById('reporte-bottom');
        if (topEl) {
            if (data.top_producto) {
                const t = data.top_producto;
                const tUSD = parseFloat(t.total_usd || 0);
                const tBs = tUSD * tasaCambio;
                const tStr = (monedaActual === 'USD') ? `$${tUSD.toFixed(2)}` : `Bs ${tBs.toFixed(2)}`;
                topEl.textContent = `${t.nombre_produc || '—'} · Cant: ${t.cantidad} · ${tStr}`;
            } else {
                topEl.textContent = '—';
            }
        }
        if (bottomEl) {
            if (data.bottom_producto) {
                const b = data.bottom_producto;
                const bUSD = parseFloat(b.total_usd || 0);
                const bBs = bUSD * tasaCambio;
                const bStr = (monedaActual === 'USD') ? `$${bUSD.toFixed(2)}` : `Bs ${bBs.toFixed(2)}`;
                bottomEl.textContent = `${b.nombre_produc || '—'} · Cant: ${b.cantidad} · ${bStr}`;
            } else {
                bottomEl.textContent = '—';
            }
        }

        const tbody = document.querySelector('#tabla-reporte-productos tbody');
        if (tbody) {
            tbody.innerHTML = '';
            const lista = (data.productos || []).sort((a,b) => parseFloat(b.cantidad||0) - parseFloat(a.cantidad||0));
            lista.slice(0, 50).forEach(p => {
                const tr = document.createElement('tr');
                const totalUSD = parseFloat(p.total_usd || 0);
                const totalBs = totalUSD * tasaCambio;
                const totalStr = (monedaActual === 'USD') ? `$${totalUSD.toFixed(2)}` : `Bs ${totalBs.toFixed(2)}`;
                tr.innerHTML = `
                    <td>${p.nombre_produc || '—'}</td>
                    <td>${p.cantidad}</td>
                    <td>${totalStr}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (err) {
        console.error('Error cargando reportes:', err);
    }
}

// =============================
// Gráficos: funciones y lógica
// =============================

function initGraficosControls() {
    const fechaEl = document.getElementById('grafico_fecha');
    const periodoEl = document.getElementById('grafico_periodo');
    const productoEl = document.getElementById('grafico_producto');
    const btnActualizar = document.getElementById('grafico_actualizar');
    const btnLimpiar = document.getElementById('grafico_limpiar');

    const hoy = new Date();
    const pad = n => String(n).padStart(2, '0');
    const hoyStr = `${hoy.getFullYear()}-${pad(hoy.getMonth()+1)}-${pad(hoy.getDate())}`;
    if (fechaEl) {
        fechaEl.max = hoyStr;
        if (!fechaEl.value) fechaEl.value = hoyStr;
        if (fechaEl.value > hoyStr) fechaEl.value = hoyStr;
    }
    if (periodoEl && !periodoEl.value) periodoEl.value = 'dia';

    cargarProductosGrafico();

    // Cargar configuraciones para gráficos (días laborales e incluir días sin ventas)
    (async () => {
        try {
            const resp = await fetch('../logica/obtener_configuraciones.php');
            const data = await resp.json();
            if (data && data.success && data.configuraciones) {
                const cfg = data.configuraciones;
                const diasStr = cfg.dias_laborales || '1,2,3,4,5';
                const diasArr = diasStr.split(',').map(x => parseInt(x,10)).filter(x => x>=1 && x<=7);
                window.__graficoDiasLaborales = diasArr;
                window.__graficoIncluirSinVentas = (cfg.incluir_dias_sin_ventas || 'true') === 'true';
                window.__graficoGridMax = parseInt(cfg.grafico_grid_max, 10);
                if (isNaN(window.__graficoGridMax) || window.__graficoGridMax < 1) window.__graficoGridMax = 100;
                window.__graficoGridStep = parseInt(cfg.grafico_grid_step, 10);
                if (isNaN(window.__graficoGridStep) || window.__graficoGridStep < 1) window.__graficoGridStep = 10;
            }
        } catch(err) {
            console.warn('No se pudieron cargar configuraciones del sistema para gráficos:', err);
            window.__graficoDiasLaborales = [1,2,3,4,5];
            window.__graficoIncluirSinVentas = true;
            window.__graficoGridMax = 100;
            window.__graficoGridStep = 10;
        }
    })();

    // Actualizar el gráfico automáticamente cuando cambie el periodo, la fecha o el producto
    if (periodoEl) {
        periodoEl.addEventListener('change', () => {
            // Si se selecciona 'dia' y la fecha está vacía, usar hoy
            if (periodoEl.value === 'dia' && fechaEl && !fechaEl.value) {
                fechaEl.value = hoyStr;
            }
            actualizarGrafico();
        });
    }
    if (fechaEl) {
        fechaEl.addEventListener('change', () => {
            // Asegurar que no se seleccione una fecha futura
            if (fechaEl.value && fechaEl.value > hoyStr) {
                fechaEl.value = hoyStr;
            }
            actualizarGrafico();
        });
    }
    if (productoEl) {
        productoEl.addEventListener('change', actualizarGrafico);
    }

    if (btnActualizar) btnActualizar.addEventListener('click', actualizarGrafico);
    if (btnLimpiar) btnLimpiar.addEventListener('click', () => {
        if (periodoEl) periodoEl.value = 'dia';
        if (fechaEl) fechaEl.value = '';
        if (productoEl) productoEl.value = '';
        limpiarGrafico();
    });
}

async function cargarProductosGrafico() {
    try {
        const resp = await fetch('../logica/obtener_productos.php');
        const data = await resp.json();
        const select = document.getElementById('grafico_producto');
        if (select) {
            select.innerHTML = '';
            const optTodos = document.createElement('option');
            optTodos.value = '';
            optTodos.textContent = 'Todos los productos';
            select.appendChild(optTodos);

            const productos = Array.isArray(data) ? data
                              : (data && data.success && Array.isArray(data.productos)) ? data.productos
                              : [];
            productos.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id_producto;
                opt.textContent = p.nombre_produc;
                select.appendChild(opt);
            });
        }
    } catch(err) {
        console.error('No se pudieron cargar productos:', err);
    }
}

function limpiarGrafico() {
    const contBarras = document.getElementById('grafico_barras');
    const contEtiquetas = document.getElementById('grafico_etiquetas');
    const totalEl = document.getElementById('grafico_total_unidades');
    if (contBarras) contBarras.innerHTML = '';
    if (contEtiquetas) contEtiquetas.innerHTML = '';
    if (totalEl) totalEl.textContent = '0';
}

async function actualizarGrafico() {
    try {
        const periodo = document.getElementById('grafico_periodo')?.value || 'dia';
        const fechaBase = document.getElementById('grafico_fecha')?.value || '';
        const producto = document.getElementById('grafico_producto')?.value || '';

        // Calcular rango por periodo usando helper existente
        let rango = calcularRangoPorPeriodo(periodo, fechaBase);
        let start = rango?.start || '';
        let end = rango?.end || '';

        // Para semana y mes: pedir datos del año completo del base
        const hoy = new Date();
        const pad = n => String(n).padStart(2, '0');
        const toYMD = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
        const baseDate = fechaBase ? new Date(fechaBase + 'T00:00:00') : hoy;
        const baseYear = baseDate.getFullYear();
        if (periodo === 'semana' || periodo === 'mes') {
            const yStart = new Date(baseYear, 0, 1);
            const yEnd = new Date(baseYear, 11, 31);
            start = toYMD(yStart);
            end = toYMD(yEnd);
        }

        let url = `../logica/obtener_grafico_ventas.php?period=${encodeURIComponent(periodo)}`;
        if (start && end) {
            url += `&start_date=${encodeURIComponent(start)}&end_date=${encodeURIComponent(end)}`;
        }
        if (producto) url += `&producto_id=${encodeURIComponent(producto)}`;

        const resp = await fetch(url);
        const data = await resp.json();
        if (data.error || data.success === false) {
            const msg = data.error || 'Respuesta inválida en gráfico';
            console.error('Error gráfico:', msg);
            limpiarGrafico();
            return;
        }
        // Adaptar a respuesta del backend existente
        let adapted = (data.labels && data.series) ? { labels: data.labels, datasets: { cantidad: data.series }, period: periodo } : { ...data, period: periodo };

        // Completar días sin ventas en periodo diario (llenar con 0) — siempre incluir el día seleccionado
        if (periodo === 'dia' && adapted && Array.isArray(adapted.labels) && Array.isArray(adapted.datasets?.cantidad)) {
            const rango = calcularRangoPorPeriodo(periodo, fechaBase);
            const pad = n => String(n).padStart(2, '0');
            const toYMD = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
            const startDate = new Date(rango.start + 'T00:00:00');
            const endDate = new Date(rango.end + 'T00:00:00');
            const mapCant = new Map();
            adapted.labels.forEach((lbl, i) => {
                mapCant.set(lbl, Number(adapted.datasets.cantidad[i]) || 0);
            });
            const fullLabels = [];
            const fullValues = [];
            for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                const ymd = toYMD(d);
                fullLabels.push(ymd);
                fullValues.push(mapCant.has(ymd) ? mapCant.get(ymd) : 0);
            }
            adapted = { labels: fullLabels, datasets: { cantidad: fullValues }, period: periodo };
        }

        // Completar semanas del año (llenar con 0 usando YEARWEEK ISO concatenado YYYYWW)
        if (periodo === 'semana' && adapted && Array.isArray(adapted.labels) && Array.isArray(adapted.datasets?.cantidad) && (window.__graficoIncluirSinVentas !== false)) {
            const targetYear = baseYear;
            const getISO = (date) => {
                const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
                const dayNum = d.getUTCDay() || 7;
                d.setUTCDate(d.getUTCDate() + 4 - dayNum);
                const yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
                const weekNo = Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
                const isoYear = d.getUTCFullYear();
                return { isoYear, isoWeek: weekNo };
            };
            const weeksInYear = getISO(new Date(Date.UTC(targetYear, 11, 28))).isoWeek;
            const mapCant = new Map();
            adapted.labels.forEach((lbl, i) => {
                const key = String(lbl);
                mapCant.set(key, Number(adapted.datasets.cantidad[i]) || 0);
            });
            const fullLabels = [];
            const fullValues = [];
            for (let w = 1; w <= weeksInYear; w++) {
                const key = String(`${targetYear}${String(w).padStart(2,'0')}`);
                fullLabels.push(key);
                fullValues.push(mapCant.has(key) ? mapCant.get(key) : 0);
            }
            adapted = { labels: fullLabels, datasets: { cantidad: fullValues }, period: periodo };
        }

        // Completar meses del año (llenar con 0 usando YYYY-MM)
        if (periodo === 'mes' && adapted && Array.isArray(adapted.labels) && Array.isArray(adapted.datasets?.cantidad) && (window.__graficoIncluirSinVentas !== false)) {
            const targetYear = baseYear;
            const mapCant = new Map();
            adapted.labels.forEach((lbl, i) => {
                const key = String(lbl);
                mapCant.set(key, Number(adapted.datasets.cantidad[i]) || 0);
            });
            const fullLabels = [];
            const fullValues = [];
            for (let m = 0; m <= 11; m++) {
                const key = `${targetYear}-${String(m+1).padStart(2,'0')}`;
                fullLabels.push(key);
                fullValues.push(mapCant.has(key) ? mapCant.get(key) : 0);
            }
            adapted = { labels: fullLabels, datasets: { cantidad: fullValues }, period: periodo };
        }

        // Completar años en el rango (llenar con 0 usando YYYY)
        if (periodo === 'anio' && adapted && Array.isArray(adapted.labels) && Array.isArray(adapted.datasets?.cantidad) && (window.__graficoIncluirSinVentas !== false)) {
            const startYear = new Date(rango.start + 'T00:00:00').getFullYear();
            const endYear = new Date(rango.end + 'T00:00:00').getFullYear();
            const mapCant = new Map();
            adapted.labels.forEach((lbl, i) => {
                const key = String(lbl);
                mapCant.set(key, Number(adapted.datasets.cantidad[i]) || 0);
            });
            const fullLabels = [];
            const fullValues = [];
            for (let y = startYear; y <= endYear; y++) {
                const key = String(y);
                fullLabels.push(key);
                fullValues.push(mapCant.has(key) ? mapCant.get(key) : 0);
            }
            adapted = { labels: fullLabels, datasets: { cantidad: fullValues }, period: periodo };
        }

        // Completar semanas del año (llenar con 0 usando YEARWEEK ISO concatenado YYYYWW)
        if (periodo === 'semana' && adapted && Array.isArray(adapted.labels) && Array.isArray(adapted.datasets?.cantidad) && (window.__graficoIncluirSinVentas !== false)) {
            const targetYear = baseYear;
            const getISO = (date) => {
                const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
                const dayNum = d.getUTCDay() || 7;
                d.setUTCDate(d.getUTCDate() + 4 - dayNum);
                const yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
                const weekNo = Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
                const isoYear = d.getUTCFullYear();
                return { isoYear, isoWeek: weekNo };
            };
            // Última semana ISO del año base: usar 28 de diciembre
            const lastWeekInYear = getISO(new Date(targetYear, 11, 28)).isoWeek;
            const mapCant = new Map();
            adapted.labels.forEach((lbl, i) => {
                const key = String(lbl);
                mapCant.set(key, Number(adapted.datasets.cantidad[i]) || 0);
            });
            const fullLabels = [];
            const fullValues = [];
            for (let w = 1; w <= lastWeekInYear; w++) {
                const key = String(`${targetYear}${String(w).padStart(2,'0')}`);
                fullLabels.push(key);
                fullValues.push(mapCant.has(key) ? mapCant.get(key) : 0);
            }
            adapted = { labels: fullLabels, datasets: { cantidad: fullValues }, period: periodo };
        }

        // Completar meses del año (llenar con 0 usando YYYY-MM)
        if (periodo === 'mes' && adapted && Array.isArray(adapted.labels) && Array.isArray(adapted.datasets?.cantidad) && (window.__graficoIncluirSinVentas !== false)) {
            const targetYear = baseYear;
            const lastMonthIndex = 11; // siempre enero..diciembre
            const mapCant = new Map();
            adapted.labels.forEach((lbl, i) => {
                const key = String(lbl);
                mapCant.set(key, Number(adapted.datasets.cantidad[i]) || 0);
            });
            const fullLabels = [];
            const fullValues = [];
            for (let m = 0; m <= lastMonthIndex; m++) {
                const key = `${targetYear}-${String(m+1).padStart(2,'0')}`;
                fullLabels.push(key);
                fullValues.push(mapCant.has(key) ? mapCant.get(key) : 0);
            }
            adapted = { labels: fullLabels, datasets: { cantidad: fullValues }, period: periodo };
        }

        // Completar años en el rango (llenar con 0 usando YYYY)
        if (periodo === 'anio' && adapted && Array.isArray(adapted.labels) && Array.isArray(adapted.datasets?.cantidad) && (window.__graficoIncluirSinVentas !== false)) {
            const startYear = new Date(rango.start + 'T00:00:00').getFullYear();
            const endYear = new Date(rango.end + 'T00:00:00').getFullYear();
            const mapCant = new Map();
            adapted.labels.forEach((lbl, i) => {
                const key = String(lbl);
                mapCant.set(key, Number(adapted.datasets.cantidad[i]) || 0);
            });
            const fullLabels = [];
            const fullValues = [];
            for (let y = startYear; y <= endYear; y++) {
                const key = String(y);
                fullLabels.push(key);
                fullValues.push(mapCant.has(key) ? mapCant.get(key) : 0);
            }
            adapted = { labels: fullLabels, datasets: { cantidad: fullValues }, period: periodo };
        }

        renderBarChart(adapted);
    } catch(err) {
        console.error('Error al actualizar gráfico:', err);
    }
}

function renderBarChart(data) {
    const contBarras = document.getElementById('grafico_barras');
    const contEtiquetas = document.getElementById('grafico_etiquetas');
    const totalEl = document.getElementById('grafico_total_unidades');
    if (!contBarras || !contEtiquetas || !totalEl) return;

    const labels = Array.isArray(data.labels) ? data.labels : [];
    const valores = (data.datasets && Array.isArray(data.datasets.cantidad)) ? data.datasets.cantidad : [];
    const maxVal = Math.max(1, ...valores.map(v => Number(v) || 0));
    const total = valores.reduce((acc, v) => acc + (Number(v) || 0), 0);

    contBarras.innerHTML = '';
    contEtiquetas.innerHTML = '';
    totalEl.textContent = String(total);
    // Ocultar etiquetas inferiores (fechas) para evitar aglomeración; usar solo tooltips
    try { contEtiquetas.style.display = 'none'; } catch(_) {}

    // Líneas horizontales de referencia por cantidades configurables
    try {
        contBarras.style.position = 'relative';
        contBarras.style.paddingLeft = '24px'; // espacio para etiquetas de línea
        contBarras.style.paddingBottom = '20px'; // espacio para marcador triangular
        contBarras.style.overflowX = 'hidden'; // evitar scroll horizontal
        const gridMax = (typeof window.__graficoGridMax === 'number' && window.__graficoGridMax > 0) ? window.__graficoGridMax : 100;
        const gridStep = (typeof window.__graficoGridStep === 'number' && window.__graficoGridStep > 0) ? window.__graficoGridStep : 10;
        for (let p = gridStep; p <= gridMax; p += gridStep) {
            const porcentaje = (p / maxVal) * 100;
            if (porcentaje <= 100) {
                const top = 100 - porcentaje;
                const line = document.createElement('div');
                line.style.position = 'absolute';
                line.style.left = '0';
                line.style.right = '0';
                line.style.top = `${top}%`;
                line.style.height = '1px';
                line.style.background = '#e0e0e0';
                line.style.pointerEvents = 'none';
                contBarras.appendChild(line);

                const label = document.createElement('span');
                label.textContent = String(p);
                label.style.position = 'absolute';
                label.style.left = '2px';
                label.style.top = `calc(${top}% - 8px)`;
                label.style.fontSize = '11px';
                label.style.color = '#9aa5b1';
                label.style.background = 'transparent';
                label.style.pointerEvents = 'none';
                contBarras.appendChild(label);
            }
        }
    } catch(_) {}

    // Identificador de hoy para resaltar barra en periodo 'dia'
    const hoy = new Date();
    const pad2 = n => String(n).padStart(2, '0');
    const hoyStr = `${hoy.getFullYear()}-${pad2(hoy.getMonth()+1)}-${pad2(hoy.getDate())}`;
    // Identificadores de semana y mes actuales para periodos 'semana' y 'mes'
    function getISOWeekYear(date) {
        const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
        const dayNum = d.getUTCDay() || 7; // 1..7 (lun..dom)
        d.setUTCDate(d.getUTCDate() + 4 - dayNum); // jueves de la semana ISO
        const isoYear = d.getUTCFullYear();
        const yearStart = new Date(Date.UTC(isoYear, 0, 1));
        const isoWeek = Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
        return { isoYear, isoWeek };
    }
    const { isoYear: semanaIsoYear, isoWeek: semanaIsoWeek } = getISOWeekYear(hoy);
    const semanaActualLbl = `${semanaIsoYear}${pad2(semanaIsoWeek)}`;
    const mesActualLbl = `${hoy.getFullYear()}-${pad2(hoy.getMonth()+1)}`;

    // Calcular ancho dinámico de barras para evitar scroll horizontal
    // Base estándar: 25px; disminuir según espacio disponible
    let gapPx = 8; // coincide con el gap del contenedor (definido en HTML)
    const containerInnerWidth = Math.max(0, contBarras.clientWidth - 24);
    let gapsTotal = Math.max(labels.length - 1, 0) * gapPx;
    let computedWidth = Math.floor((containerInnerWidth - gapsTotal) / Math.max(labels.length, 1));
    // Reducir gap si las barras están muy estrechas
    if (computedWidth < 6) {
        gapPx = 4;
        contBarras.style.gap = '4px';
        gapsTotal = Math.max(labels.length - 1, 0) * gapPx;
        computedWidth = Math.floor((containerInnerWidth - gapsTotal) / Math.max(labels.length, 1));
    }
    if (computedWidth < 3) {
        gapPx = 2;
        contBarras.style.gap = '2px';
        gapsTotal = Math.max(labels.length - 1, 0) * gapPx;
        computedWidth = Math.floor((containerInnerWidth - gapsTotal) / Math.max(labels.length, 1));
    }
    const baseWidth = 25;
    const minWidth = 2; // mínimo visible
    let barWidth = Math.max(minWidth, Math.min(baseWidth, computedWidth));
    contBarras.style.justifyContent = (labels.length === 1) ? 'center' : 'flex-start';

    labels.forEach((lbl, idx) => {
        const val = Number(valores[idx]) || 0;
        const alturaPct = Math.round((val / maxVal) * 100);
        const barra = document.createElement('div');
        if (alturaPct <= 0) {
            // Asegurar visibilidad para días con 0 ventas
            barra.style.height = '2px';
        } else {
            barra.style.height = alturaPct + '%';
        }
        barra.style.width = barWidth + 'px';
        // Color por día laborable/no laborable si hay configuración cargada (solo periodo 'dia')
        let color = '#4C8BF5';
        try {
            if (data.period === 'dia') {
                const dow = new Date(lbl + 'T00:00:00').getDay(); // 0=Domingo..6=Sabado
                const dia17 = (dow === 0 ? 7 : dow); // 1=Lunes..7=Domingo
                if (window.__graficoDiasLaborales && Array.isArray(window.__graficoDiasLaborales)) {
                    if (!window.__graficoDiasLaborales.includes(dia17)) {
                        color = '#9aa5b1'; // gris para no laborables
                    }
                }
            }
        } catch(_) {}
        barra.style.background = color;
        barra.style.borderRadius = '4px 4px 0 0';
        barra.style.display = 'inline-block';
        barra.style.minHeight = '2px';
        // Tooltip amigable por periodo (breve descripción)
        let tooltip = `${lbl}: ${val}`;
        const period = data.period || null;
        try {
            const unidades = (val === 1) ? 'unidad' : 'unidades';
            if (period === 'dia') {
                const esHoy = (lbl === hoyStr);
                tooltip = (val > 0)
                    ? `Día ${lbl} — Ventas: ${val} ${unidades}`
                    : `Día ${lbl} — Sin ventas`;
                if (esHoy) tooltip += ' — Hoy';
            } else if (period === 'semana') {
                const str = String(lbl);
                const isoYear = str.slice(0,4);
                const isoWeek = str.slice(4);
                tooltip = (val > 0)
                    ? `Semana ${isoWeek} de ${isoYear} — Ventas: ${val} ${unidades}`
                    : `Semana ${isoWeek} de ${isoYear} — Sin ventas`;
                const esSemanaActual = (lbl === semanaActualLbl);
                if (esSemanaActual) tooltip += ' — Semana actual';
            } else if (period === 'mes') {
                const [y,m] = String(lbl).split('-');
                const nombres = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                const nombreMes = nombres[(parseInt(m,10)-1) || 0] || m;
                tooltip = (val > 0)
                    ? `Mes ${nombreMes} ${y} — Ventas: ${val} ${unidades}`
                    : `Mes ${nombreMes} ${y} — Sin ventas`;
                const esMesActual = (lbl === mesActualLbl);
                if (esMesActual) tooltip += ' — Mes actual';
            } else if (period === 'anio') {
                tooltip = (val > 0)
                    ? `Año ${lbl} — Ventas: ${val} ${unidades}`
                    : `Año ${lbl} — Sin ventas`;
            }
        } catch(_) {}
        barra.title = tooltip;

        // Resaltar periodo actual con un marcador en forma de triángulo sobre la barra
        try {
            const isDiaActual = (data.period === 'dia' && lbl === hoyStr);
            const isSemanaActual = (data.period === 'semana' && lbl === semanaActualLbl);
            const isMesActual = (data.period === 'mes' && lbl === mesActualLbl);
            if (isDiaActual || isSemanaActual || isMesActual) {
                barra.style.position = 'relative';
                const tri = document.createElement('div');
                tri.style.position = 'absolute';
                const halfBase = Math.max(4, Math.floor(barWidth / 2));
                const height = Math.max(8, Math.floor(barWidth * 0.7));
                tri.style.bottom = `-${height}px`;
                tri.style.left = '50%';
                tri.style.transform = 'translateX(-50%)';
                tri.style.width = '0';
                tri.style.height = '0';
                tri.style.borderLeft = `${halfBase}px solid transparent`;
                tri.style.borderRight = `${halfBase}px solid transparent`;
                tri.style.borderBottom = `${height}px solid #FFB300`;
                tri.style.pointerEvents = 'none';
                barra.appendChild(tri);
            }
        } catch(_) {}

        contBarras.appendChild(barra);
    });
}

function verDetalleDeuda(idCliente, fechaFactura) {
    fetch(`../logica/obtener_detalle_deuda.php?id_cliente=${idCliente}&fecha_factura=${fechaFactura}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            
            mostrarModalDetalle(data);
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Función para mostrar modal con detalle
function mostrarModalDetalle(data) {
    const modal = document.getElementById('modal-detalle-deuda');
    const modalBody = document.querySelector('#modal-detalle-deuda .modal-body');
    
    // Crear HTML para productos
    let productosHtml = '';
    if (data.productos && data.productos.length > 0) {
        productosHtml = `
            <div class="deuda-section">
                <h3>Productos</h3>
                <table class="productos-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Fecha/Hora</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        data.productos.forEach(producto => {
            productosHtml += `
                <tr>
                    <td>${producto.producto || 'N/A'}</td>
                    <td>${producto.cantidad || 0}</td>
                    <td>${monedaActual === 'USD' ? `$${parseFloat(producto.subtotal || 0).toFixed(2)}` : `Bs ${(parseFloat(producto.subtotal || 0)*tasaCambio).toFixed(2)}`}</td>
                    <td>${producto.fecha_compra || ''} ${producto.hora_compra || ''}</td>
                </tr>
            `;
        });
        
        productosHtml += '</tbody></table></div>';
    }
    
    // Crear HTML para abonos
    let abonosHtml = '';
    if (data.abonos && data.abonos.length > 0) {
        abonosHtml = `
            <div class="deuda-section">
                <h3>Historial de Abonos</h3>
                <table class="abonos-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        data.abonos.forEach(abono => {
            abonosHtml += `
                <tr>
                    <td>${new Date(abono.fecha_abono).toLocaleDateString()}</td>
                    <td>${monedaActual === 'USD' ? `$${parseFloat(abono.monto || 0).toFixed(2)}` : `Bs ${(parseFloat(abono.monto || 0)*tasaCambio).toFixed(2)}`}</td>
                    <td>${abono.metodo_pago || 'N/A'}</td>
                    <td>${abono.observaciones || '-'}</td>
                </tr>
            `;
        });
        
        abonosHtml += '</tbody></table></div>';
    } else {
        abonosHtml = `
            <div class="deuda-section">
                <h3>Historial de Abonos</h3>
                <p style="text-align: center; color: #666; font-style: italic;">No hay abonos registrados</p>
            </div>
        `;
    }
    
    // Obtener totales desde data.resumen o calcular si no existen
    const total = data.resumen ? data.resumen.total_factura : (data.total || 0);
    const abonado = data.resumen ? data.resumen.total_abonado : (data.abonado || 0);
    const saldo = data.resumen ? data.resumen.saldo_pendiente : (data.saldo || 0);
    const totalBs = parseFloat(total) * tasaCambio;
    const abonadoBs = parseFloat(abonado) * tasaCambio;
    const saldoBs = parseFloat(saldo) * tasaCambio;
    
    modalBody.innerHTML = `
        <div class="deuda-section">
            <h3>Información del Cliente</h3>
            <div class="deuda-info-grid">
                <div class="deuda-info-item">
                    <span class="deuda-info-label">Cliente</span>
                    <span class="deuda-info-value">${data.cliente || 'N/A'}</span>
                </div>
                <div class="deuda-info-item">
                    <span class="deuda-info-label">Fecha de Factura</span>
                    <span class="deuda-info-value">${data.fecha_factura || 'N/A'}</span>
                </div>
                <div class="deuda-info-item">
                    <span class="deuda-info-label">ID Cliente</span>
                    <span class="deuda-info-value">${data.id_cliente || 'N/A'}</span>
                </div>
            </div>
        </div>
        
        ${productosHtml}
        
        ${abonosHtml}
        
        <div class="totales-resumen">
            <h3 style="margin-bottom: 15px; text-align: center; color: #333;">Resumen de Totales</h3>
            <div class="totales-grid">
                <div class="total-item">
                    <div class="total-label">Total</div>
                    <div class="total-value total">${monedaActual === 'USD' ? `$${parseFloat(total).toFixed(2)}` : `Bs ${totalBs.toFixed(2)}`}</div>
                </div>
                <div class="total-item">
                    <div class="total-label">Abonado</div>
                    <div class="total-value abonado">${monedaActual === 'USD' ? `$${parseFloat(abonado).toFixed(2)}` : `Bs ${abonadoBs.toFixed(2)}`}</div>
                </div>
                <div class="total-item">
                    <div class="total-label">Saldo Pendiente</div>
                    <div class="total-value saldo">${monedaActual === 'USD' ? `$${parseFloat(saldo).toFixed(2)}` : `Bs ${saldoBs.toFixed(2)}`}</div>
                </div>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
    // Guardar último payload para re-render tras toggle
    window.ultimoDetalleCaja = data;
}

function toggleMoneda() {
    monedaActual = (monedaActual === 'USD') ? 'VES' : 'USD';
    localStorage.setItem('monedaActual', monedaActual);
    aplicarMonedaEnUI();
    const activeTab = document.querySelector('.tab-button.active').dataset.tab;
    if (activeTab === 'ventas') {
        cargarVentas();
    } else if (activeTab === 'deudas') {
        cargarDeudas();
    } else if (activeTab === 'reportes') {
        cargarReportes();
    } else if (activeTab === 'graficos') {
        actualizarGrafico();
    }
}

function aplicarMonedaEnUI() {
    const btn = document.getElementById('btn-toggle-moneda');
    if (btn) {
        btn.textContent = monedaActual === 'USD' ? 'USD' : 'Bs';
    }
}

function cerrarModal() {
    document.getElementById('modal-detalle-deuda').style.display = 'none';
}

function cerrarModalDetalle() {
    document.getElementById('modal-detalle-deuda').style.display = 'none';
}

// Detalle de Venta (modal)
async function verDetalleVenta(idVenta) {
    try {
        const resp = await fetch(`../logica/obtener_detalle_venta.php?id_venta=${idVenta}`);
        const data = await resp.json();
        if (data.error) { console.error(data.error); return; }
        mostrarModalDetalleVenta(data);
    } catch (e) {
        console.error('Error al cargar detalle de venta:', e);
    }
}

function mostrarModalDetalleVenta(data) {
    const modal = document.getElementById('modal-detalle-venta');
    const body = document.getElementById('contenido-detalle-venta');
    const fechaStr = data.fecha_venta ? new Date(data.fecha_venta).toLocaleString('es-ES') : '';
    const cajeroFull = (data.cajero && data.cajero.trim().length)
        ? data.cajero
        : `${data.cajero_nombre || ''}${data.cajero_apellido ? ' ' + data.cajero_apellido : ''}`.trim();

    let rows = '';
    let totalUSD = 0;
    (data.items || []).forEach(item => {
        const subtotalUsd = parseFloat(item.subtotal || 0);
        const subtotalBs = subtotalUsd * tasaCambio;
        totalUSD += subtotalUsd;
        rows += `
            <tr>
                <td>${item.producto || 'N/A'}</td>
                <td>${item.cantidad || 0}</td>
                <td>$${subtotalUsd.toFixed(2)}</td>
                <td>Bs ${subtotalBs.toFixed(2)}</td>
            </tr>
        `;
    });
    const totalBs = totalUSD * tasaCambio;

    body.innerHTML = `
        <div class="venta-section">
            <h3>Información de la Venta</h3>
            <p><strong>ID Venta:</strong> ${data.id_venta}</p>
            <p><strong>Cliente:</strong> ${data.cliente || ''}</p>
            <p><strong>Cajero:</strong> ${cajeroFull || ''}</p>
            <p><strong>Fecha/Hora:</strong> ${fechaStr}</p>
            <h4>Productos</h4>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Subtotal (USD)</th>
                        <th>Equivalente (Bs)</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows}
                </tbody>
            </table>
            <div class="venta-totales" style="margin-top:8px; display:flex; gap:16px;">
                <div><strong>Total (USD):</strong> $${totalUSD.toFixed(2)}</div>
                <div><strong>Equivalente (Bs):</strong> Bs ${totalBs.toFixed(2)}</div>
            </div>
            <p style="font-size:0.9em;color:#555;">Equivalencia en bolívares calculada con la tasa actual configurada.</p>
        </div>
    `;

    modal.style.display = 'block';
}

function cerrarModalVenta() {
    document.getElementById('modal-detalle-venta').style.display = 'none';
}

// Detalle por Producto (modal)
async function verDetalleProducto(nombreProducto) {
    try {
        const fechaInicio = document.getElementById('fecha_inicio')?.value || '';
        const fechaFin = document.getElementById('fecha_fin')?.value || '';
        let url = '../logica/obtener_ventas.php';
        if (fechaInicio && fechaFin) {
            url += `?start_date=${fechaInicio}&end_date=${fechaFin}`;
        }
        const resp = await fetch(url);
        const data = await resp.json();
        if (data.error) {
            mostrarModalDetalleProducto({ nombreProducto, grupos: [], totalUSD: 0, totalUnidades: 0, error: data.error });
            return;
        }
        // Filtrar por producto y agrupar por cliente
        const registros = data.filter(v => (v.producto_nombre || '') === nombreProducto);
        const byCliente = new Map();
        registros.forEach(v => {
            const cliente = `${v.cliente_nombre || ''}${v.cliente_apellido ? ' ' + v.cliente_apellido : ''}`.trim() || 'N/A';
            const fecha = new Date(v.fecha_venta);
            const entry = byCliente.get(cliente) || { cliente, cantidadTotal: 0, totalUSD: 0, ultimaFecha: fecha };
            entry.cantidadTotal += parseInt(v.cantidad || 0, 10);
            entry.totalUSD += parseFloat(v.total || 0);
            if (fecha > entry.ultimaFecha) entry.ultimaFecha = fecha;
            byCliente.set(cliente, entry);
        });
        const grupos = Array.from(byCliente.values()).sort((a,b)=> b.ultimaFecha - a.ultimaFecha);
        const totalUSD = grupos.reduce((acc,g)=> acc + g.totalUSD, 0);
        const totalUnidades = grupos.reduce((acc,g)=> acc + g.cantidadTotal, 0);
        mostrarModalDetalleProducto({ nombreProducto, grupos, totalUSD, totalUnidades });
    } catch (e) {
        console.error('Error al cargar detalle por producto:', e);
        mostrarModalDetalleProducto({ nombreProducto, grupos: [], totalUSD: 0, totalUnidades: 0, error: 'Error al cargar detalle' });
    }
}

function mostrarModalDetalleProducto(payload) {
    const modal = document.getElementById('modal-detalle-producto');
    const body = document.getElementById('contenido-detalle-producto');
    const totalBs = (payload.totalUSD || 0) * tasaCambio;
    const header = `
        <div class="venta-section">
            <h3>Producto: ${payload.nombreProducto || 'N/A'}</h3>
            <div style="display:flex; gap:16px; margin:8px 0;">
                <div><strong>Total unidades:</strong> ${payload.totalUnidades || 0}</div>
                <div><strong>Total USD:</strong> $${(payload.totalUSD || 0).toFixed(2)}</div>
                <div><strong>Total Bs:</strong> Bs ${totalBs.toFixed(2)}</div>
            </div>
        </div>
    `;
    let rows = '';
    (payload.grupos || []).forEach(g => {
        const totalBsFila = g.totalUSD * tasaCambio;
        rows += `
            <tr>
                <td>${g.cliente}</td>
                <td>${g.cantidadTotal}</td>
                <td>$${g.totalUSD.toFixed(2)}</td>
                <td>Bs ${totalBsFila.toFixed(2)}</td>
                <td>${g.ultimaFecha.toLocaleString('es-ES')}</td>
            </tr>
        `;
    });
    const table = `
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Cantidad total</th>
                    <th>Total (USD)</th>
                    <th>Equivalente (Bs)</th>
                    <th>Última venta</th>
                </tr>
            </thead>
            <tbody>
                ${rows || '<tr><td colspan="5" style="text-align:center; color:#666;">Sin resultados</td></tr>'}
            </tbody>
        </table>
    `;
    const errorMsg = payload.error ? `<div style="color:#b00; margin-top:8px;">${payload.error}</div>` : '';
    body.innerHTML = header + table + errorMsg;
    modal.style.display = 'block';
}

function cerrarModalProducto() {
    document.getElementById('modal-detalle-producto').style.display = 'none';
}

// Cerrar modales al hacer clic fuera de ellos
window.onclick = function(event) {
    const modalDeuda = document.getElementById('modal-detalle-deuda');
    const modalVenta = document.getElementById('modal-detalle-venta');
    const modalProducto = document.getElementById('modal-detalle-producto');
    if (event.target === modalDeuda) {
        modalDeuda.style.display = 'none';
    }
    if (event.target === modalVenta) {
        modalVenta.style.display = 'none';
    }
    if (event.target === modalProducto) {
        modalProducto.style.display = 'none';
    }
}

// Inicialización de controles de Reportes
document.addEventListener('DOMContentLoaded', function() {
    const btnActualizar = document.getElementById('reporte_actualizar');
    if (btnActualizar) {
        btnActualizar.addEventListener('click', cargarReportes);
    }
    const initialTab = document.querySelector('.tab-button.active')?.dataset.tab;
    if (initialTab === 'reportes') {
        cargarReportes();
    } else if (initialTab === 'graficos') {
        if (!window.graficosInicializado) {
            initGraficosControls();
            window.graficosInicializado = true;
        }
        actualizarGrafico();
    }
});
