document.addEventListener('DOMContentLoaded', function() {
    // Inicializar pestañas
    initializeTabs();
    
    // Cargar datos iniciales
    cargarVentas();
    
    // Event listeners para filtros
    document.getElementById('filtrar').addEventListener('click', function() {
        const activeTab = document.querySelector('.tab-button.active').dataset.tab;
        if (activeTab === 'ventas') {
            cargarVentas();
        } else if (activeTab === 'deudas') {
            cargarDeudas();
        }
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
            
            // Cargar datos según la pestaña seleccionada
            if (targetTab === 'ventas') {
                cargarVentas();
            } else if (targetTab === 'deudas') {
                cargarDeudas();
            }
        });
    });
}

function cargarVentas() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    
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
                tbody.innerHTML = '<tr><td colspan="7">Error: ' + data.error + '</td></tr>';
                return;
            }
            
            data.forEach(venta => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${venta.id_venta}</td>
                    <td>${venta.cliente_nombre} ${venta.cliente_apellido}</td>
                    <td>${venta.cajero_nombre}</td>
                    <td>${venta.producto_nombre}</td>
                    <td>${venta.cantidad}</td>
                    <td>$${parseFloat(venta.total).toFixed(2)}</td>
                    <td>${new Date(venta.fecha_venta).toLocaleDateString()}</td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            const tbody = document.querySelector('#tabla-ventas tbody');
            tbody.innerHTML = '<tr><td colspan="7">Error al cargar los datos</td></tr>';
        });
}

function cargarDeudas() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const buscarNombre = document.getElementById('buscar_nombre')?.value || '';
    const buscarApellido = document.getElementById('buscar_apellido')?.value || '';
    const buscarCedula = document.getElementById('buscar_cedula')?.value || '';
    
    let url = '../logica/obtener_deudas.php';
    if (fechaInicio && fechaFin) {
        url += `?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    }
    // Agregar filtros de texto
    const params = new URLSearchParams();
    if (fechaInicio && fechaFin) {
        // ya agregados arriba en la URL, no repetir
    } else {
        // si no se incluyó fecha en la URL base, usaremos params
        if (fechaInicio) params.set('fecha_inicio', fechaInicio);
        if (fechaFin) params.set('fecha_fin', fechaFin);
    }
    if (buscarNombre) params.set('buscar_nombre', buscarNombre);
    if (buscarApellido) params.set('buscar_apellido', buscarApellido);
    if (buscarCedula) params.set('buscar_cedula', buscarCedula);
    const queryString = params.toString();
    if (queryString) {
        url += (url.includes('?') ? '&' : '?') + queryString;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            
            const tbody = document.querySelector('#tabla-deudas tbody');
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No hay deudas registradas</td></tr>';
                return;
            }
            
            data.forEach(deuda => {
                const saldo = parseFloat(deuda.saldo_pendiente);
                const estado = deuda.estado;
                let estadoClass = '';
                let estadoTexto = '';
                
                switch(estado) {
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
                row.innerHTML = `
                    <td>${deuda.id_credito}</td>
                    <td>${deuda.cliente}</td>
                    <td>${fechaMostrar}</td>
                    <td>${deuda.total_productos} producto(s)</td>
                    <td>$${parseFloat(deuda.total_factura).toFixed(2)}</td>
                    <td>$${parseFloat(deuda.total_abonado).toFixed(2)}</td>
                    <td>$${saldo.toFixed(2)}</td>
                    <td><span class="estado ${estadoClass}">${estadoTexto}</span></td>
                    <td>
                        <button class="btn-detalle" onclick="verDetalleDeuda(${deuda.id_cliente}, '${deuda.fecha_factura}')">
                            Ver Detalle
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error:', error);
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
                    <td>$${parseFloat(producto.subtotal || 0).toFixed(2)}</td>
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
                    <td>$${parseFloat(abono.monto || 0).toFixed(2)}</td>
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
                    <div class="total-value total">$${parseFloat(total).toFixed(2)}</div>
                </div>
                <div class="total-item">
                    <div class="total-label">Abonado</div>
                    <div class="total-value abonado">$${parseFloat(abonado).toFixed(2)}</div>
                </div>
                <div class="total-item">
                    <div class="total-label">Saldo Pendiente</div>
                    <div class="total-value saldo">$${parseFloat(saldo).toFixed(2)}</div>
                </div>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modal-detalle-deuda').style.display = 'none';
}

function cerrarModalDetalle() {
    document.getElementById('modal-detalle-deuda').style.display = 'none';
}

// Cerrar modal al hacer clic fuera de él
window.onclick = function(event) {
    const modal = document.getElementById('modal-detalle-deuda');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
