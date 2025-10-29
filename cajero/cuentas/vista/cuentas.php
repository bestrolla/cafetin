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
    <title>Gestión de Facturas</title>
    <link rel="stylesheet" href="../../../acces/css/main.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require_once '../../../acces/nav_cajero/nav_cajero.php'; ?>
    <div class="container">
        <div class="accounts-container">
            <h1>Gestión de Facturas</h1>

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

            <!-- Resumen de facturas -->
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Total Facturas</h3>
                    <div class="amount" id="totalCuentas">0</div>
                    <div class="label">Registros</div>
                </div>
                <div class="summary-card">
                    <h3>Facturas Pendientes</h3>
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
                            <th>ID Factura</th>
                            <th>Cliente</th>
                            <th>Productos</th>
                            <th>Total</th>
                            <th>Abonado</th>
                            <th>Saldo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filas de facturas se insertarán aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalle de factura -->
    <div id="modalDetalle" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detalle de Factura</h2>
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
                <h2>Abonar a Factura</h2>
                <span class="close" onclick="cerrarModalAbono()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="formAbono">
                    <div class="form-group">
                        <label for="clienteAbono">Cliente:</label>
                        <input type="text" id="clienteAbono" readonly>
                    </div>
                    <div class="form-group">
                        <label for="saldoAbono">Saldo Actual:</label>
                        <input type="text" id="saldoAbono" readonly>
                    </div>
                    <div class="form-group">
                        <label for="montoAbono">Monto a Abonar:</label>
                        <input type="number" id="montoAbono" step="0.01" min="0" required>
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