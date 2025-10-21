document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-agregar-cajero');
    const responseMessage = document.getElementById('response-message');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);

        fetch('../logica/procesar_agregar_cajero.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            responseMessage.textContent = data.message;
            responseMessage.classList.remove('hidden', 'success', 'error');

            if (data.success) {
                responseMessage.classList.add('success');
                form.reset();
            } else {
                responseMessage.classList.add('error');
            }
        })
        .catch(error => {
            responseMessage.textContent = 'Error de conexión. Inténtelo de nuevo.';
            responseMessage.classList.remove('hidden', 'success');
            responseMessage.classList.add('error');
            console.error('Error:', error);
        });
    });
});