document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.querySelector('.login-form form');
    const linksContainer = document.querySelector('.links');

    // Crear un div para mensajes de error si no existe
    let errorDiv = document.getElementById('error-message');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'error-message';
        errorDiv.style.color = '#ff4d4d';
        errorDiv.style.marginTop = '10px';
        errorDiv.style.textAlign = 'center';
        errorDiv.style.fontWeight = 'bold';
        // Insertar el div de error antes de los links
        loginForm.insertBefore(errorDiv, linksContainer);
    }

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            errorDiv.textContent = ''; // Limpiar errores previos

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
                        window.location.href = '../../../admin/agregar_cajero/vista/agregar_cajero.php';
                    } else if (data.role === 'cajero') {
                        window.location.href = '../../../cajero/lobby/vista/lobby.php';
                    } else {
                        // Rol no reconocido, redirigir a una página por defecto o mostrar error
                        errorDiv.textContent = 'Rol de usuario no reconocido.';
                    }
                } else {
                    errorDiv.textContent = data.message;
                }
            })
            .catch(error => {
                errorDiv.textContent = 'Error de conexión. Intente de nuevo.';
                console.error('Error:', error);
            });
        });
    }
});

// Función para mostrar/ocultar contraseña
function togglePassword() {
    const passwordInput = document.getElementById('contrasena');
    const toggleIcon = document.querySelector('.toggle-password');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.textContent = '🙈'; // Cambiar a ojo cerrado
    } else {
        passwordInput.type = 'password';
        toggleIcon.textContent = '👁️'; // Cambiar a ojo abierto
    }
}
