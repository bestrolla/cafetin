document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.querySelector('.login-form form');
    const linksContainer = document.querySelector('.links');

    // Contenedor único de alertas
    let alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alert-container';
        alertContainer.className = 'alert-container';
        // Insertar el contenedor antes de los links
        loginForm.insertBefore(alertContainer, linksContainer);
    }

    // Utilidades de alerta: auto-ocultar
    function autoDismiss(el, delay = 2000, remove = true) {
        setTimeout(() => {
            el.classList.add('alert-hide');
            // Esperar transición antes de retirar
            setTimeout(() => {
                if (remove) {
                    el.remove();
                } else {
                    el.style.display = 'none';
                }
            }, 400);
        }, delay);
    }

    function getDismissDelay(el) {
        if (el.classList.contains('alert-success')) return 1500; // éxito
        if (el.classList.contains('alert-error')) return 3000;   // error
        return 2000; // por defecto
    }

    // Mostrar error: crea alert efímero del mismo ancho que los inputs
    function mostrarError(msg) {
        const el = document.createElement('div');
        el.className = 'alert alert-error';
        el.textContent = msg;
        alertContainer.appendChild(el);
        autoDismiss(el, 3000, true);
    }

    // Si hay alertas pre-renderizadas por PHP, auto-ocultarlas con delay según tipo
    alertContainer.querySelectorAll('.alert').forEach(el => {
        autoDismiss(el, getDismissDelay(el), true);
    });

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alertContainer.innerHTML = ''; // Limpiar alertas previas

            const formData = new FormData(this);

            fetch('../logica/procesar_login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirección basada en el rol
                    if (data.role === 'admin') {
                        window.location.href = '../../../admin/inventario/vista/inventario.php';
                    } else if (data.role === 'cajero') {
                        window.location.href = '../../../cajero/lobby/vista/lobby.php';
                    } else {
                        // Rol no reconocido, redirigir a una página por defecto o mostrar error
                        mostrarError('Rol de usuario no reconocido.');
                }
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                mostrarError('Error de conexión. Intente de nuevo.');
                console.error('Error:', error);
            });
        });
    }
});

// Función para mostrar/ocultar contraseña
function togglePassword() {
    const passwordInput = document.getElementById('contrasena');
    const eyeOpen = document.querySelector('.toggle-password .icon-eye');
    const eyeOff = document.querySelector('.toggle-password .icon-eye-off');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        if (eyeOpen && eyeOff) { eyeOpen.style.display = 'none'; eyeOff.style.display = 'block'; }
    } else {
        passwordInput.type = 'password';
        if (eyeOpen && eyeOff) { eyeOpen.style.display = 'block'; eyeOff.style.display = 'none'; }
    }
}
