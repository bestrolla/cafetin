document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('#tabla-ventas tbody');
    const filterBtn = document.getElementById('filter-btn');
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');

    // Cargar todas las ventas al iniciar
    cargarVentas();

    // Manejar clic en el botón de filtrar
    filterBtn.addEventListener('click', function() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        cargarVentas(startDate, endDate);
    });

    // Cargar datos de ventas
    function cargarVentas(startDate = null, endDate = null) {
        let url = '../logica/obtener_ventas.php';
        if (startDate && endDate) {
            url += `?start_date=${startDate}&end_date=${endDate}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = '';
                if (data.error) {
                    alert(data.error);
                    return;
                }
                data.forEach(venta => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${venta.id_venta}</td>
                        <td>${venta.cliente_nombre}</td>
                        <td>${venta.cajero_nombre}</td>
                        <td>${venta.producto_nombre}</td>
                        <td>${venta.cantidad}</td>
                        <td>${venta.total}</td>
                        <td>${venta.fecha_venta}</td>
                    `;
                    tableBody.appendChild(row);
                });
            });
    }
});
