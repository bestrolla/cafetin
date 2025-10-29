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
    
    let url = '../logica/obtener_deudas.php';
    if (fechaInicio && fechaFin) {
        url += `?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
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
                row.innerHTML = `
                    <td>${deuda.id_credito}</td>
                    <td>${deuda.cliente}</td>
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
     const modalBody = modal.querySelector('.modal-body');
    
    // Crear HTML para productos
    let productosHtml = '';
    if (data.productos && data.productos.length > 0) {
        productosHtml = `
            <h4>Productos:</h4>
            <table class="tabla-detalle">
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
                    <td>${producto.producto}</td>
                    <td>${producto.cantidad}</td>
                    <td>$${parseFloat(producto.subtotal).toFixed(2)}</td>
                    <td>${producto.fecha_compra} ${producto.hora_compra}</td>
                </tr>
            `;
        });
        
        productosHtml += '</tbody></table>';
    }
    
    // Crear HTML para abonos
    let abonosHtml = '';
    if (data.abonos && data.abonos.length > 0) {
        abonosHtml = `
            <h4>Historial de Abonos:</h4>
            <table class="tabla-detalle">
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
                    <td>$${parseFloat(abono.monto).toFixed(2)}</td>
                    <td>${abono.metodo_pago}</td>
                    <td>${abono.observaciones || '-'}</td>
                </tr>
            `;
        });
        
        abonosHtml += '</tbody></table>';
    } else {
        abonosHtml = '<h4>Historial de Abonos:</h4><p>No hay abonos registrados</p>';
    }
    
    modalBody.innerHTML = `
        <div class="detalle-cliente">
            <h3>Cliente: ${data.cliente}</h3>
            <p><strong>Fecha de Factura:</strong> ${data.fecha_factura}</p>
        </div>
        
        ${productosHtml}
        
        ${abonosHtml}
        
        <div class="resumen-totales">
            <div class="total-item">
                <span>Total Factura:</span>
                <span>$${parseFloat(data.resumen.total_factura).toFixed(2)}</span>
            </div>
            <div class="total-item">
                <span>Total Abonado:</span>
                <span>$${parseFloat(data.resumen.total_abonado).toFixed(2)}</span>
            </div>
            <div class="total-item saldo-pendiente">
                <span>Saldo Pendiente:</span>
                <span>$${parseFloat(data.resumen.saldo_pendiente).toFixed(2)}</span>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modal-detalle-deuda').style.display = 'none';
}

// Cerrar modal al hacer clic fuera de él
window.onclick = function(event) {
    const modal = document.getElementById('modal-detalle-deuda');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
