// Variables globales
let facturas = [];
let facturasFiltradas = [];

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

// Función para mostrar las facturas en la tabla
function mostrarCuentas() {
    const tbody = document.getElementById('tablaCuentas');
    tbody.innerHTML = '';

    if (facturasFiltradas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay facturas registradas</td></tr>';
        return;
    }

    facturasFiltradas.forEach(factura => {
        const saldo = parseFloat(factura.saldo_pendiente);
        const estado = factura.estado_factura;
        const estadoTexto = estado === 'pagado' ? 'Pagado' : (estado === 'parcial' ? 'Parcial' : 'Pendiente');
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${factura.id_factura}</td>
            <td>${factura.cliente}</td>
            <td>${factura.total_productos} producto(s)</td>
            <td>$${parseFloat(factura.total_factura).toFixed(2)}</td>
            <td>$${parseFloat(factura.total_abonado).toFixed(2)}</td>
            <td>$${saldo.toFixed(2)}</td>
            <td>
                <span class="badge ${estado === 'pagado' ? 'bg-success' : estado === 'parcial' ? 'bg-warning' : 'bg-danger'}">
                    ${estadoTexto}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="verDetalleFactura(${factura.id_cliente}, '${factura.fecha_factura}')">
                    Ver Detalle
                </button>
                ${saldo > 0 ? `<button class="btn btn-sm btn-success ms-1" onclick="abrirModalAbono(${factura.id_factura})">Abonar</button>` : ''}
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
        const cumpleFecha = !filtroFecha || factura.fecha_factura === filtroFecha;
        
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
    document.getElementById('totalAdeudado').textContent = '$' + totalAdeudado.toFixed(2);
    document.getElementById('totalAbonado').textContent = '$' + totalAbonado.toFixed(2);
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
                        <span class="float-right">Total: $${parseFloat(grupoFecha.total_fecha).toFixed(2)}</span>
                    </h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-secondary">
                             <tr>
                                 <th>Fecha y Hora</th>
                                 <th>Producto</th>
                                 <th>Cantidad</th>
                                 <th>Subtotal</th>
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
                         <td>$${parseFloat(producto.subtotal).toFixed(2)}</td>
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
                    <td>$${parseFloat(abono.monto).toFixed(2)}</td>
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
                        <h6>Total Factura</h6>
                        <h4>$${parseFloat(data.resumen.total_factura).toFixed(2)}</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card text-center" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                        <h6>Total Abonado</h6>
                        <h4>$${parseFloat(data.resumen.total_abonado).toFixed(2)}</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card text-center" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <h6>Saldo Pendiente</h6>
                        <h4>$${parseFloat(data.resumen.saldo_pendiente).toFixed(2)}</h4>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
    
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
    cargarCuentas();
    
    // Filtros
    document.getElementById('filtroCliente').addEventListener('input', filtrarCuentas);
    document.getElementById('filtroEstado').addEventListener('change', filtrarCuentas);
    document.getElementById('filtroFecha').addEventListener('change', filtrarCuentas);
    
    // Botón limpiar filtros
    document.getElementById('btn-limpiar-filtros').addEventListener('click', limpiarFiltros);
});