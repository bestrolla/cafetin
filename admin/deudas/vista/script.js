document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('#tabla-deudas tbody');

    // Cargar deudas al iniciar
    cargarDeudas();

    // Cargar datos de deudas
    function cargarDeudas() {
        fetch('../logica/obtener_deudas.php')
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = '';
                if (data.error) {
                    alert(data.error);
                    return;
                }
                data.forEach(deuda => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${deuda.id_credito}</td>
                        <td>${deuda.cliente_nombre}</td>
                        <td>${deuda.producto_nombre}</td>
                        <td>${deuda.cantidad}</td>
                        <td>${deuda.total}</td>
                        <td>${deuda.fecha_cre}</td>
                        <td>${deuda.estado}</td>
                        <td>
                            <button class="pay-btn" onclick="pagarDeuda(${deuda.id_credito})" ${deuda.estado === 'pagado' ? 'disabled' : ''}>
                                Pagar
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            });
    }

    // Marcar deuda como pagada
    window.pagarDeuda = function(id_credito) {
        if (confirm('¿Está seguro de que desea marcar esta deuda como pagada?')) {
            const formData = new FormData();
            formData.append('id_credito', id_credito);

            fetch('../logica/pagar_deuda.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    cargarDeudas();
                }
            });
        }
    }
});
