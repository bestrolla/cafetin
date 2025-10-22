document.addEventListener('DOMContentLoaded', function() {
    // --- ELEMENTOS DEL DOM ---
    const addForm = document.getElementById('form-producto');
    const tableBody = document.querySelector('#tabla-inventario tbody');
    const searchInput = document.getElementById('search-input');
    const editModal = document.getElementById('edit-modal');
    const editForm = document.getElementById('form-edit-producto');
    const closeModalButton = editModal.querySelector('.close-button');

    // --- ESTADO DE LA APLICACIÓN ---
    let inventarioActual = [];

    // --- INICIALIZACIÓN ---
    cargarInventario();

    // --- EVENT LISTENERS ---
    addForm.addEventListener('submit', e => { e.preventDefault(); agregarProducto(); });
    editForm.addEventListener('submit', e => { e.preventDefault(); actualizarProducto(); });
    searchInput.addEventListener('keyup', filterTable);
    closeModalButton.addEventListener('click', cerrarModal);
    window.addEventListener('click', event => { if (event.target == editModal) cerrarModal(); });

    // --- LÓGICA DE CÁLCULO AUTOMÁTICO ---
    const addPrecioCaja = document.getElementById('precio_caja');
    const addCantidadCaja = document.getElementById('cantidad_caja');
    const addPrecioUnidad = document.getElementById('precio_produc');
    const editPrecioCaja = document.getElementById('edit_precio_caja');
    const editCantidadCaja = document.getElementById('edit_cantidad_caja');
    const editPrecioUnidad = document.getElementById('edit_precio_produc');

    function calcularYActualizarPrecioUnidad(precioCajaInput, cantidadCajaInput, precioUnidadOutput) {
        console.log("Calculando precio..."); // DEBUG
        const precioCaja = parseFloat(precioCajaInput.value);
        const cantidadCaja = parseInt(cantidadCajaInput.value);
        if (!isNaN(precioCaja) && !isNaN(cantidadCaja) && cantidadCaja > 0) {
            const precioUnidad = precioCaja / cantidadCaja;
            precioUnidadOutput.value = precioUnidad.toFixed(2);
            console.log(`Resultado: ${precioUnidad.toFixed(2)}`); // DEBUG
        } else {
            console.log("Datos inválidos para calcular."); // DEBUG
        }
    }

    addPrecioCaja.addEventListener('input', () => calcularYActualizarPrecioUnidad(addPrecioCaja, addCantidadCaja, addPrecioUnidad));
    addCantidadCaja.addEventListener('input', () => calcularYActualizarPrecioUnidad(addPrecioCaja, addCantidadCaja, addPrecioUnidad));
    editPrecioCaja.addEventListener('input', () => calcularYActualizarPrecioUnidad(editPrecioCaja, editCantidadCaja, editPrecioUnidad));
    editCantidadCaja.addEventListener('input', () => calcularYActualizarPrecioUnidad(editPrecioCaja, editCantidadCaja, editPrecioUnidad));

    // --- FUNCIONES PRINCIPALES ---
    function cargarInventario() {
        fetch('../logica/obtener_inventario.php')
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error("Error al cargar inventario:", data.message);
                    tableBody.innerHTML = `<tr><td colspan="8">Error al cargar datos: ${data.message}</td></tr>`;
                    return;
                }
                tableBody.innerHTML = '';
                inventarioActual = data.inventario;

                inventarioActual.forEach(producto => {
                    const row = document.createElement('tr');
                    const totalUnidades = parseInt(producto.caja_produc) * parseInt(producto.cantidad_caja);
                    row.innerHTML = `
                        <td>${producto.nombre_produc}</td>
                        <td>${producto.caja_produc}</td>
                        <td>${totalUnidades}</td>
                        <td>${producto.precio_caja}</td>
                        <td>${producto.precio_produc}</td>
                        <td>${producto.precio_venta}</td>
                        <td>${producto.activo ? 'Activo' : 'Inactivo'}</td>
                        <td class="actions">
                            <button class="btn btn-edit">Editar</button>
                            <button class="btn btn-delete">Eliminar</button>
                        </td>
                    `;
                    row.querySelector('.btn-edit').addEventListener('click', () => abrirModalDeEdicion(producto));
                    row.querySelector('.btn-delete').addEventListener('click', () => eliminarProducto(producto.id_producto));
                    tableBody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error en fetch:', error);
                tableBody.innerHTML = `<tr><td colspan="8">Error de conexión. No se pudo cargar el inventario.</td></tr>`;
            });
    }

    function agregarProducto() {
        const formData = new FormData(addForm);
        const nombreNuevo = formData.get('nombre_produc').trim().toLowerCase();
        if (inventarioActual.some(p => p.nombre_produc.toLowerCase() === nombreNuevo)) {
            alert('Error: Este producto ya está registrado.');
            return;
        }
        fetch('../logica/agregar_producto.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    addForm.reset();
                    cargarInventario();
                }
            });
    }

    function actualizarProducto() {
        const formData = new FormData(editForm);
        fetch('../logica/actualizar_producto.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    cerrarModal();
                    cargarInventario();
                }
            });
    }

    function eliminarProducto(id) {
        if (confirm('¿Está seguro de que desea desactivar este producto?')) {
            const formData = new FormData();
            formData.append('id_producto', id);
            fetch('../logica/eliminar_producto.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) cargarInventario();
                });
        }
    }

    // --- FUNCIONES DEL MODAL ---
    function abrirModalDeEdicion(producto) {
        editForm.querySelector('#edit_id_producto').value = producto.id_producto;
        editForm.querySelector('#edit_nombre_produc').value = producto.nombre_produc;
        editForm.querySelector('#edit_caja_produc').value = producto.caja_produc;
        editForm.querySelector('#edit_cantidad_caja').value = producto.cantidad_caja;
        editForm.querySelector('#edit_precio_caja').value = producto.precio_caja;
        editForm.querySelector('#edit_precio_produc').value = producto.precio_produc;
        editForm.querySelector('#edit_precio_venta').value = producto.precio_venta;
        editModal.style.display = 'block';
    }

    function cerrarModal() {
        editModal.style.display = 'none';
    }

    // --- FUNCIONES AUXILIARES ---
    function filterTable() {
        const filter = searchInput.value.toLowerCase();
        const rows = tableBody.getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            for (let j = 0; j < cells.length - 1; j++) {
                if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
            rows[i].style.display = found ? '' : 'none';
        }
    }
});