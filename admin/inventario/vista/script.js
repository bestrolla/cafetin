document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-producto');
    const tableBody = document.querySelector('#tabla-inventario tbody');
    const searchInput = document.getElementById('search-input');

    // Cargar inventario al iniciar
    cargarInventario();

    // Manejar envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        guardarProducto();
    });

    // Filtrar tabla al escribir en el buscador
    searchInput.addEventListener('keyup', filterTable);

    // Cargar datos del inventario
    function cargarInventario() {
        fetch('../logica/obtener_inventario.php')
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = '';
                data.forEach(producto => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${producto.id_producto}</td>
                        <td>${producto.nombre_produc}</td>
                        <td>${producto.caja_produc}</td>
                        <td>${producto.cantidad_caja}</td>
                        <td>${producto.precio_caja}</td>
                        <td>${producto.precio_produc}</td>
                        <td>${producto.activo ? 'Sí' : 'No'}</td>
                        <td>
                            <button onclick="editarProducto(${producto.id_producto})">Editar</button>
                            <button onclick="eliminarProducto(${producto.id_producto})">Eliminar</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            });
    }

    // Guardar (agregar o actualizar) producto
    function guardarProducto() {
        const formData = new FormData(form);
        const id_producto = formData.get('id_producto');
        const url = id_producto ? '../logica/actualizar_producto.php' : '../logica/agregar_producto.php';

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                form.reset();
                cargarInventario();
            }
        });
    }

    // Filtrar tabla
    window.filterTable = function() {
        const filter = searchInput.value.toLowerCase();
        const rows = tableBody.getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            for (let j = 0; j < cells.length; j++) {
                if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
            rows[i].style.display = found ? '' : 'none';
        }
    }

    // Editar producto
    window.editarProducto = function(id) {
        fetch('../logica/obtener_inventario.php') // Re-usamos el obtener para buscar el producto
            .then(response => response.json())
            .then(data => {
                const producto = data.find(p => p.id_producto == id);
                if (producto) {
                    document.getElementById('id_producto').value = producto.id_producto;
                    document.getElementById('nombre_produc').value = producto.nombre_produc;
                    document.getElementById('caja_produc').value = producto.caja_produc;
                    document.getElementById('cantidad_caja').value = producto.cantidad_caja;
                    document.getElementById('precio_caja').value = producto.precio_caja;
                    document.getElementById('precio_produc').value = producto.precio_produc;
                }
            });
    }

    // Eliminar producto
    window.eliminarProducto = function(id) {
        if (confirm('¿Está seguro de que desea desactivar este producto?')) {
            const formData = new FormData();
            formData.append('id_producto', id);

            fetch('../logica/eliminar_producto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    cargarInventario();
                }
            });
        }
    }
});
