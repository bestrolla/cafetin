<?php
// Incluir sistema de control de acceso
require_once '../../../acces/auth_check.php';

// Proteger página - solo cajeros
protegerPagina(['cajero']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Cajero</title>
    <link rel="stylesheet" href="../../../acces/css/main.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include '../../../acces/nav_cajero/nav_cajero.php'; ?>
    
    <div class="container">
        <div class="content-wrapper">
            <h1>Configuración de Cajero</h1>
            
            <!-- Información de tasa actual -->
            <div class="config-section">
                <h2>Tasa de Cambio Actual</h2>
                <div class="tasa-info">
                    <div class="rate-display">
                        <span class="currency">$1 USD =</span>
                        <span id="current-rate" class="rate-value">36.00</span>
                        <span class="currency">Bs</span>
                    </div>
                    <p class="last-update">Última actualización: <span id="last-update">--</span></p>
                    <button class="btn btn-secondary" onclick="actualizarTasa()">
                        <i class="icon-refresh"></i> Actualizar
                    </button>
                </div>
            </div>

            <!-- Configuración de perfil -->
            <div class="config-section">
                <h2>Mi Perfil</h2>
                <form id="form-perfil">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre-usuario">Nombre de Usuario:</label>
                            <input type="text" id="nombre-usuario" readonly>
                        </div>
                        <div class="form-group">
                            <label for="rol-usuario">Rol:</label>
                            <input type="text" id="rol-usuario" value="Cajero" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email-usuario">Email:</label>
                        <input type="email" id="email-usuario" placeholder="Ingrese su email">
                    </div>
                    <div class="form-group">
                        <label for="telefono-usuario">Teléfono:</label>
                        <input type="tel" id="telefono-usuario" placeholder="Ingrese su teléfono">
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar Perfil</button>
                </form>
            </div>

            <!-- Cambio de contraseña -->
            <div class="config-section">
                <h2>Cambiar Contraseña</h2>
                <form id="form-password">
                    <div class="form-group">
                        <label for="password-actual">Contraseña Actual:</label>
                        <input type="password" id="password-actual" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password-nueva">Nueva Contraseña:</label>
                            <input type="password" id="password-nueva" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="password-confirmar">Confirmar Contraseña:</label>
                            <input type="password" id="password-confirmar" required minlength="6">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                </form>
            </div>

            <!-- Seguridad: Preguntas de seguridad (hasta 3) -->
            <div class="config-section">
                <h2>Seguridad</h2>
                <div class="alert alert-warning">Estas preguntas son predeterminadas del sistema y no pueden editarse. Seleccione hasta 3 y guarde sus respuestas; se validarán en la recuperación de contraseña.</div>
                <p>Seleccione 3 preguntas predeterminadas y defina sus respuestas.</p>
                <form id="form-seguridad-cajero">
                    <div class="form-group">
                        <label for="pregunta-seguridad-1">Pregunta 1:</label>
                        <select id="pregunta-seguridad-1" required>
                            <option value="">Cargando preguntas...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="respuesta-seguridad-1">Respuesta 1:</label>
                        <input type="text" id="respuesta-seguridad-1" placeholder="Escribe tu respuesta" required>
                        <small>Las respuestas se almacenan cifradas.</small>
                    </div>

                    <div class="form-group">
                        <label for="pregunta-seguridad-2">Pregunta 2:</label>
                        <select id="pregunta-seguridad-2" required>
                            <option value="">Cargando preguntas...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="respuesta-seguridad-2">Respuesta 2:</label>
                        <input type="text" id="respuesta-seguridad-2" placeholder="Escribe tu respuesta" required>
                    </div>

                    <div class="form-group">
                        <label for="pregunta-seguridad-3">Pregunta 3:</label>
                        <select id="pregunta-seguridad-3" required>
                            <option value="">Cargando preguntas...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="respuesta-seguridad-3">Respuesta 3:</label>
                        <input type="text" id="respuesta-seguridad-3" placeholder="Escribe tu respuesta" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar Seguridad</button>
                </form>
            </div>

            <!-- Preferencias -->
            <div class="config-section">
                <h2>Preferencias</h2>
                <form id="form-preferencias">
                    <div class="form-group">
                        <label for="moneda-preferida">Moneda Preferida para Mostrar:</label>
                        <select id="moneda-preferida">
                            <option value="BS">Bolívares (Bs)</option>
                            <option value="USD">Dólares (USD)</option>
                            <option value="AMBAS">Mostrar Ambas</option>
                        </select>
                    </div>
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="sonidos-notificacion">
                            Activar sonidos de notificación
                        </label>
                    </div>
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="confirmacion-ventas">
                            Solicitar confirmación antes de procesar ventas
                        </label>
                    </div>
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="auto-imprimir">
                            Imprimir automáticamente facturas
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Preferencias</button>
                </form>
            </div>

            <!-- Información del sistema -->
            <div class="config-section">
                <h2>Información del Sistema</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Empresa:</label>
                        <span id="nombre-empresa">--</span>
                    </div>
                    <div class="info-item">
                        <label>Versión del Sistema:</label>
                        <span>1.0.0</span>
                    </div>
                    <div class="info-item">
                        <label>Última Sesión:</label>
                        <span id="ultima-sesion">--</span>
                    </div>
                    <div class="info-item">
                        <label>Estado del Sistema:</label>
                        <span class="status-online">En línea</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>