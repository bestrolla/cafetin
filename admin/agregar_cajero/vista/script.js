document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-agregar-cajero');
    const responseMessage = document.getElementById('response-message');
    const inputNombre = document.getElementById('nombre');
    const inputApellido = document.getElementById('apellido');
    const inputTelefono = document.getElementById('telefono');

    // Utilidades de validación/sanitización
    const toLettersOnly = (str) => (str || '').replace(/[^a-zA-ZÁÉÍÓÚÜÑáéíóúüñ\s]/g, '').replace(/\s{2,}/g, ' ');
    const capitalizeFirst = (str) => {
        const s = (str || '').trim();
        if (!s) return '';
        return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();
    };
    const toIntOnly = (str) => (str || '').replace(/[^0-9]/g, '');

    // Reglas: nombres/apellidos solo letras; teléfono solo números
    if (inputNombre) {
        inputNombre.addEventListener('input', (e) => {
            const v = toLettersOnly(e.target.value);
            if (v !== e.target.value) e.target.value = v;
        });
        inputNombre.addEventListener('blur', (e) => {
            e.target.value = capitalizeFirst(e.target.value);
        });
    }
    if (inputApellido) {
        inputApellido.addEventListener('input', (e) => {
            const v = toLettersOnly(e.target.value);
            if (v !== e.target.value) e.target.value = v;
        });
        inputApellido.addEventListener('blur', (e) => {
            e.target.value = capitalizeFirst(e.target.value);
        });
    }
    if (inputTelefono) {
        inputTelefono.addEventListener('input', (e) => {
            const v = toIntOnly(e.target.value);
            if (v !== e.target.value) e.target.value = v;
        });
        inputTelefono.addEventListener('blur', (e) => {
            const n = toIntOnly(e.target.value);
            e.target.value = n;
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Normalizar antes de enviar
        if (inputNombre) inputNombre.value = capitalizeFirst(toLettersOnly(inputNombre.value));
        if (inputApellido) inputApellido.value = capitalizeFirst(toLettersOnly(inputApellido.value));
        if (inputTelefono) inputTelefono.value = toIntOnly(inputTelefono.value);

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