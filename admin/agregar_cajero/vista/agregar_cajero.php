<?php
// Incluir sistema de control de acceso
require_once '../../../acces/auth_check.php';

// Proteger página - solo administradores
protegerPagina(['admin']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Cajero</title>
    <link rel="stylesheet" href="style.css?v=1.1">
    <link rel="stylesheet" href="../../../acces/css/main.css">
</head>
<body>
    <?php require_once '../../../acces/nav_admin/nav_admin.php'; ?>

    <main class="container">
        <form id="form-agregar-cajero" method="POST">
            <h1>Agregar Nuevo Cajero</h1>
            <p>Complete el formulario para registrar un nuevo cajero en el sistema.</p>
            <div class="form-grid">
                <!-- Columna 1: Datos Personales -->
                <div class="form-column">
                    <h2>Datos Personales</h2>
                    <label for="nombre">Nombre: <span style="color:#e53935;">*</span></label>
                    <input type="text" id="nombre" name="nombre" required>

                    <label for="apellido">Apellido: <span style="color:#e53935;">*</span></label>
                    <input type="text" id="apellido" name="apellido" required>

                    <label for="telefono">Teléfono: <span style="color:#e53935;">*</span></label>
                    <input type="text" id="telefono" name="telefono" required>
                </div>

                <!-- Columna 2: Datos de Usuario -->
                <div class="form-column">
                    <h2>Datos de Acceso</h2>
                    <label for="usuario">Nombre de Usuario: <span style="color:#e53935;">*</span></label>
                    <input type="text" id="usuario" name="usuario" required>

                    <label for="contrasena">Contraseña: <span style="color:#e53935;">*</span></label>
                    <div class="input-wrapper">
                        <input type="password" id="contrasena" name="contrasena" required aria-describedby="password-hint">
                        <span class="toggle-password" onclick="toggleAgregarCajeroPassword(this)" aria-label="Mostrar u ocultar contraseña" title="Mostrar/Ocultar">
                          <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="3" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                          <svg class="icon-eye-off" style="display:none" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M3 3l18 18" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10.584 10.59a2 2 0 102.828 2.828" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9.88 5.09A10.943 10.943 0 0112 5c7 0 11 7 11 7a20.02 20.02 0 01-4.522 4.9M6.61 6.61C3.78 8.2 1.999 12 1.999 12a20.016 20.016 0 005.936 5.27" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                        </span>
                    </div>
                    <label for="contrasena_confirmar">Confirmar Contraseña: <span style="color:#e53935;">*</span></label>
                    <div class="input-wrapper">
                        <input type="password" id="contrasena_confirmar" name="contrasena_confirmar" required aria-describedby="password-hint">
                        <span class="toggle-password" onclick="toggleAgregarCajeroPasswordConfirm(this)" aria-label="Mostrar u ocultar contraseña" title="Mostrar/Ocultar">
                          <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="3" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                          <svg class="icon-eye-off" style="display:none" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M3 3l18 18" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10.584 10.59a2 2 0 102.828 2.828" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9.88 5.09A10.943 10.943 0 0112 5c7 0 11 7 11 7a20.02 20.02 0 01-4.522 4.9M6.61 6.61C3.78 8.2 1.999 12 1.999 12a20.016 20.016 0 005.936 5.27" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                        </span>
                    </div>
                    <small id="password-hint" class="password-hint">La contraseña debe tener entre 6 y 12 caracteres, usando mayúsculas, minúsculas, números y un solo signo especial permitido($ % & , . ¿ ? ¡ !).</small>
                </div>
            </div>

            <div id="response-message" class="hidden"></div>

            <button type="submit" class="submit-btn">Registrar Cajero</button>
        </form>
    </main>

    <?php require_once '../../../acces/footer/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>