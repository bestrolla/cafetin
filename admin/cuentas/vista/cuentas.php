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
    <title>Gestión de Pedidos</title>
    <link rel="stylesheet" href="../../../acces/css/main.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require_once '../../../acces/nav_admin/nav_admin.php'; ?>
    <div class="container">
        <div class="accounts-container">
            <div class="accounts-header">
                <h1>Gestión de Pedidos</h1>
                <button id="btnToggleMonedaAdminCuentas" type="button" class="btn-toggle-moneda">Moneda: USD</button>
            </div>

            <!-- Filtros y búsqueda -->
            <div class="filters-container">
                <div class="filter-group">
                    <label for="filtroCliente">Buscar Cliente:</label>
                    <input type="text" id="filtroCliente" placeholder="Nombre del cliente...">
                </div>
                <div class="filter-group">
                    <label for="filtroEstado">Estado:</label>
                    <select id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="parcial">Parcial</option>
                        <option value="pagado">Pagado</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filtroFecha">Fecha:</label>
                    <input type="date" id="filtroFecha">
                </div>
                <button id="btn-limpiar-filtros" class="btn btn-secondary">Limpiar Filtros</button>
            </div>

            <!-- Resumen de pedidos -->
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Total Pedidos</h3>
                    <div class="amount" id="totalCuentas">0</div>
                    <div class="label">Registros</div>
                </div>
                <div class="summary-card">
                    <h3>Pedidos Pendientes</h3>
                    <div class="amount" id="cuentasPendientes">0</div>
                    <div class="label">Activas</div>
                </div>
                <div class="summary-card">
                    <h3>Total Adeudado</h3>
                    <div class="amount" id="totalAdeudado">$0.00</div>
                    <div class="label">Saldo Pendiente</div>
                </div>
                <div class="summary-card">
                    <h3>Total Abonado</h3>
                    <div class="amount" id="totalAbonado">$0.00</div>
                    <div class="label">Pagos Realizados</div>
                </div>
            </div>

            <div class="table-container">
                <table id="tablaCuentas">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Cantidad de pedidos</th>
                            <th>Total a deber</th>
                            <th>Abonado</th>
                            <th>Debiendo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filas de pedidos se insertarán aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalle de pedido -->
    <div id="modalDetalle" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detalle de Pedido</h2>
                <button id="btnToggleMonedaAdminDetalle" type="button" class="btn-toggle-moneda">Moneda: USD</button>
                <span class="close" onclick="cerrarModalDetalle()">&times;</span>
            </div>
            <div class="modal-body">
                <!-- El contenido se cargará dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Modal para abonar -->
    <div id="modalAbono" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Abonar por Total</h2>
                <button id="btnToggleMonedaAdminAbono" type="button" class="btn-toggle-moneda">Moneda: USD</button>
                <span class="close" onclick="cerrarModalAbono()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="formAbono">
                    <div class="form-group" style="display:none;">
                        <label for="fechaFacturaAbono">Pedido / Fecha:</label>
                        <select id="fechaFacturaAbono">
                            <option value="">Seleccione un pedido...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="clienteAbono">Cliente:</label>
                        <input type="text" id="clienteAbono" readonly>
                    </div>
                    <div class="form-group">
                        <label for="saldoAbono">Saldo Actual:</label>
                        <input type="text" id="saldoAbono" readonly>
                    </div>
                    <div class="form-group">
                        <label>Montos a Abonar</label>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <div style="flex:1; min-width:200px;">
                                <label for="montoAbonoUsd">USD</label>
                                <input type="number" id="montoAbonoUsd" step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div style="flex:1; min-width:200px;">
                                <label for="montoAbonoBs">Bs</label>
                                <input type="number" id="montoAbonoBs" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        <small>Puede abonar una parte en USD y otra en Bs.</small>
                        <div id="equivalenteAbono" style="margin-top:6px; font-size: 0.95em; color:#333;">
                            Total equivalente: USD $0.00 | Bs 0.00
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="metodoPago">Método de Pago:</label>
                        <select id="metodoPago" required>
                            <option value="">Seleccionar...</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="observacionesAbono">Observaciones:</label>
                        <textarea id="observacionesAbono" rows="3"></textarea>
                    </div>
                    <input type="hidden" id="idCreditoAbono">
                </form>
                <div class="form-buttons">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalAbono()">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="procesarAbono()">Procesar Abono</button>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../../../acces/footer/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>