<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja - Reporte de Ventas y Deudas</title>
    <link rel="stylesheet" href="../../../acces/css/main.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require_once '../../../acces/nav_admin/nav_admin.php'; ?>

    <main class="container">
        <div class="cash-container">
            <h1>Reporte de Caja</h1>

            <!-- Pestañas de navegación -->
            <div class="tabs-container">
                <div class="tabs">
                    <button class="tab-button active" data-tab="ventas">
                        <span class="tab-icon">💰</span>
                        Ventas
                    </button>
                    <button class="tab-button" data-tab="deudas">
                        <span class="tab-icon">📋</span>
                        Deudas
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filters">
                <div class="date-group">
                    <label for="fecha_inicio">Desde:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio">
                    <label for="fecha_fin">Hasta:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin">
                </div>
                <button id="filtrar" class="btn">Filtrar</button>
            </div>

            <!-- Contenido de Ventas -->
            <div id="ventas" class="tab-content active">
                <div class="table-container">
                    <table id="tabla-ventas">
                        <thead>
                            <tr>
                                <th>ID Venta</th>
                                <th>Cliente</th>
                                <th>Cajero</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Filas de ventas se insertarán aquí -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Contenido de Deudas -->
            <div id="deudas" class="tab-content">
                <div class="table-container">
                    <table id="tabla-deudas">
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
                            <!-- Las deudas se cargarán aquí dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Detalles de Deuda -->
    <div id="modal-detalle-deuda" class="modal">
        <div class="modal-content modal-detalle">
            <div class="modal-header">
                <h2>Detalles de la Deuda</h2>
                <span class="close" onclick="cerrarModalDetalle()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="contenido-detalle-deuda">
                    <!-- El contenido se cargará dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../../../acces/footer/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>