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

            <div class="tabs">
                <button class="tab-button active" onclick="showTab('tab-tasa', this)">Tasa</button>
                <button class="tab-button" onclick="showTab('tab-perfil', this)">Perfil</button>
                <button class="tab-button" onclick="showTab('tab-password', this)">Contraseña</button>
                <button class="tab-button" onclick="showTab('tab-seguridad', this)">Seguridad</button>
                <!-- <button class="tab-button" onclick="showTab('tab-preferencias', this)">Preferencias</button> -->
                <button class="tab-button" onclick="showTab('tab-info', this)">Sistema</button>
            </div>

            <div id="tab-tasa" class="tab-content active">
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
            </div>

            <div id="tab-perfil" class="tab-content">
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
            </div>

            <div id="tab-password" class="tab-content">
                <div class="config-section">
                    <h2>Cambiar Contraseña</h2>
                    <form id="form-password">
                        <div class="form-group">
                            <label for="password-actual">Contraseña Actual:</label>
                            <div class="input-wrapper">
                              <input type="password" id="password-actual" required>
                              <span class="toggle-password" onclick="toggleConfigPassword('password-actual', this)" aria-label="Mostrar u ocultar contraseña" title="Mostrar/Ocultar">
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
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password-nueva">Nueva Contraseña:</label>
                                <div class="input-wrapper">
                                  <input type="password" id="password-nueva" required minlength="6">
                                  <span class="toggle-password" onclick="toggleConfigPassword('password-nueva', this)" aria-label="Mostrar u ocultar contraseña" title="Mostrar/Ocultar">
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
                            </div>
                            <div class="form-group">
                                <label for="password-confirmar">Confirmar Contraseña:</label>
                                <div class="input-wrapper">
                                  <input type="password" id="password-confirmar" required minlength="6">
                                  <span class="toggle-password" onclick="toggleConfigPassword('password-confirmar', this)" aria-label="Mostrar u ocultar contraseña" title="Mostrar/Ocultar">
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
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                    </form>
                </div>
            </div>

            <div id="tab-seguridad" class="tab-content">
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
            </div>

            <!-- <div id="tab-preferencias" class="tab-content">
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
                                Solicitar confirmación antes de procesar pedidos
                            </label>
                        </div>
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" id="auto-imprimir">
                                Imprimir automáticamente pedidos
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Preferencias</button>
                    </form>
                </div>
            </div> -->

            <div id="tab-info" class="tab-content">
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
    </div>

    <script src="script.js"></script>
</body>
</html>