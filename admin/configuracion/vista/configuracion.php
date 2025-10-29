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
    <title>Configuración - Sistema Cafetín</title>
    <link rel="stylesheet" href="../../../acces/css/main.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include '../../../acces/nav_admin/nav_admin.php'; ?>
    
    <div class="container">
        <div class="content-wrapper">
            <h1>Configuración del Sistema</h1>
            
            <!-- Tabs de configuración -->
            <div class="tabs">
                <button class="tab-button active" onclick="showTab('tasa')">Tasa del Día</button>
                <button class="tab-button" onclick="showTab('empresa')">Datos Empresa</button>
                <button class="tab-button" onclick="showTab('sistema')">Sistema</button>
                <button class="tab-button" onclick="showTab('historial')">Historial</button>
            </div>

            <!-- Tab Tasa del Día -->
            <div id="tasa" class="tab-content active">
                <div class="config-section">
                    <h2>Configuración de Tasa del Dólar</h2>
                    <div class="tasa-container">
                        <div class="current-rate">
                            <h3>Tasa Actual</h3>
                            <div class="rate-display">
                                <span class="currency">$1 USD =</span>
                                <span id="current-rate" class="rate-value">36.00</span>
                                <span class="currency">Bs</span>
                            </div>
                            <p class="last-update">Última actualización: <span id="last-update">--</span></p>
                        </div>
                        
                        <div class="update-rate">
                            <h3>Actualizar Tasa</h3>
                            <form id="form-tasa">
                                <div class="form-group">
                                    <label for="nueva-tasa">Nueva Tasa (Bs por USD):</label>
                                    <input type="number" id="nueva-tasa" step="0.01" min="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="motivo">Motivo del cambio:</label>
                                    <textarea id="motivo" rows="3" placeholder="Opcional: Razón del cambio de tasa"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Actualizar Tasa</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Datos Empresa -->
            <div id="empresa" class="tab-content">
                <div class="config-section">
                    <h2>Información de la Empresa</h2>
                    <form id="form-empresa">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre-empresa">Nombre de la Empresa:</label>
                                <input type="text" id="nombre-empresa" required>
                            </div>
                            <div class="form-group">
                                <label for="telefono-empresa">Teléfono:</label>
                                <input type="tel" id="telefono-empresa">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="direccion-empresa">Dirección:</label>
                            <textarea id="direccion-empresa" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="email-empresa">Email:</label>
                            <input type="email" id="email-empresa">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </form>
                </div>
            </div>

            <!-- Tab Sistema -->
            <div id="sistema" class="tab-content">
                <div class="config-section">
                    <h2>Configuración del Sistema</h2>
                    <form id="form-sistema">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="moneda-principal">Moneda Principal:</label>
                                <select id="moneda-principal">
                                    <option value="BS">Bolívares (Bs)</option>
                                    <option value="USD">Dólares (USD)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="iva-porcentaje">IVA (%):</label>
                                <input type="number" id="iva-porcentaje" step="0.01" min="0" max="100">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descuento-maximo">Descuento Máximo (%):</label>
                            <input type="number" id="descuento-maximo" step="0.01" min="0" max="100">
                        </div>
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" id="backup-automatico">
                                Realizar backup automático
                            </label>
                        </div>
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" id="notificaciones-email">
                                Enviar notificaciones por email
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                    </form>
                </div>
            </div>

            <!-- Tab Historial -->
            <div id="historial" class="tab-content">
                <div class="config-section">
                    <h2>Historial de Cambios de Tasa</h2>
                    <div class="table-container">
                        <table id="tabla-historial">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tasa Anterior</th>
                                    <th>Tasa Nueva</th>
                                    <th>Usuario</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llenará dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div id="modal-confirmacion" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmar Cambio de Tasa</h3>
                <span class="close" onclick="cerrarModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de cambiar la tasa de <span id="tasa-actual-modal"></span> Bs a <span id="tasa-nueva-modal"></span> Bs por dólar?</p>
                <p><strong>Este cambio afectará todas las transacciones futuras.</strong></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                <button class="btn btn-primary" onclick="confirmarCambioTasa()">Confirmar</button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>