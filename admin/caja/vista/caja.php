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
    <title>Caja - Reporte de Pedidos y Deudas</title>
    <link rel="stylesheet" href="../../../acces/css/main.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require_once '../../../acces/nav_admin/nav_admin.php'; ?>

    <main class="container">
        <div class="cash-container">
            <div class="cash-header" style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                <h1 style="margin:0;">Reporte de Caja</h1>
                <button id="btn-toggle-moneda" class="btn btn-toggle-moneda" title="Cambiar moneda">USD</button>
            </div>

            <!-- Pestañas de navegación -->
            <div class="tabs-container">
                <div class="tabs">
                    <button class="tab-button active" data-tab="ventas">
                        <span class="tab-icon">💰</span>
                        Pedidos
                    </button>
                    <button class="tab-button" data-tab="deudas">
                        <span class="tab-icon">📋</span>
                        Deudas
                    </button>
                    <button class="tab-button" data-tab="reportes">
                        <span class="tab-icon">📊</span>
                        Reportes
                    </button>
                    <button class="tab-button" data-tab="graficos">
                        <span class="tab-icon">📈</span>
                        Gráficos
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
                <div class="search-group" style="display:flex; gap:8px; align-items:center; margin-top:8px;">
                    <input type="text" id="buscar_general" placeholder="Buscar por nombre, apellido, cédula u hora">
                </div>
                <div class="mode-group" style="display:flex; gap:8px; align-items:center; margin-top:8px;">
                    <label for="ventas_modo">Modo:</label>
                    <select id="ventas_modo">
                        <option value="producto">Por producto</option>
                        <option value="venta">Por pedido</option>
                    </select>
                    <label for="ventas_ventana">Ventana:</label>
                    <select id="ventas_ventana">
                        <option value="dia">Día completo</option>
                        <option value="60">60 min</option>
                        <option value="30">30 min</option>
                        <option value="10">10 min</option>
                    </select>
                </div>
                <div style="display:flex; gap:8px; margin-top:8px;">
                    <button id="filtrar" class="btn">Filtrar</button>
                    <button id="limpiar_filtros" class="btn btn-secondary">Borrar filtros</button>
                </div>
            </div>

            <!-- Contenido de Ventas -->
            <div id="ventas" class="tab-content active">
                <div class="table-container">
                    <table id="tabla-ventas">
                        <thead>
                            <tr>
                                <!-- Cabecera dinámica según modo -->
                                <th>Cliente</th>
                                <th>Cajero</th>
                                <th>Total</th>
                                <th>Fecha</th>
                                <th>Ver</th>
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
                <!-- Filtros específicos de Deudas -->
                <div class="filters" id="deudas-filtros" style="margin-bottom:12px;">
                    <div class="search-group" style="display:flex; gap:8px; align-items:center;">
                        <input type="text" id="deudas_buscar" placeholder="Buscar por nombre, apellido o cédula">
                        <label for="deudas_estado" style="margin-left:8px;">Estado:</label>
                        <select id="deudas_estado">
                            <option value="todos">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="parcial">Parcial</option>
                        </select>
                        <label for="deudas_orden" style="margin-left:8px;">Orden:</label>
                        <select id="deudas_orden">
                            <option value="mas">Quién debe más</option>
                            <option value="menos">Quién debe menos</option>
                        </select>
                    </div>
                    <div style="display:flex; gap:8px; margin-top:8px;">
                        <button id="deudas_filtrar" class="btn">Filtrar</button>
                        <button id="deudas_limpiar" class="btn btn-secondary">Borrar filtros</button>
                    </div>
                </div>
                <div class="table-container">
                    <table id="tabla-deudas">
                        <thead>
                            <tr>
                                <th>ID Pedido</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
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

            <!-- Contenido de Reportes -->
            <div id="reportes" class="tab-content">
                <!-- Controles de Reportes (alineados con estilos de Caja) -->
                <div class="filters" style="margin-top:12px;">
                    <div class="date-group" style="gap:12px; align-items:center;">
                        <label for="reporte_periodo">Periodo:</label>
                        <select id="reporte_periodo" name="reporte_periodo">
                            <option value="dia">Diario</option>
                            <option value="semana">Semanal</option>
                            <option value="mes">Mensual</option>
                            <option value="anio">Anual</option>
                        </select>
                        <label for="reporte_fecha">Fecha base:</label>
                        <input type="date" id="reporte_fecha" name="reporte_fecha" />

                    </div>
                    <div style="display:flex; gap:8px; margin-top:8px;">
                        <button id="reporte_actualizar" class="btn">Filtrar</button>
                        <button id="reporte_limpiar" class="btn btn-secondary">Borrar filtros</button>
                    </div>
                </div>
                <div class="reportes-resumen" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
                    <div class="card">
                        <h3>Total Pedidos</h3>
                        <div><strong>USD:</strong> <span id="reporte-total-usd">$0.00</span></div>
                        <div><strong>Bs:</strong> <span id="reporte-total-bs">Bs 0.00</span></div>
                    </div>
                    <div class="card">
                        <h3>Más Pedido</h3>
                        <div id="reporte-top">—</div>
                    </div>
                    <div class="card">
                        <h3>Menos Pedido</h3>
                        <div id="reporte-bottom">—</div>
                    </div>
                </div>
                <div class="table-container" style="margin-top:16px;">
                    <table id="tabla-reporte-productos">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th id="reporte-col-total-label">Total (USD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Top productos por periodo -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Contenido de Gráficos -->
            <div id="graficos" class="tab-content">
                <!-- Controles de Gráficos -->
                <div class="filters" style="margin-top:12px;">
                    <div class="date-group" style="gap:12px; align-items:center;">
                        <label for="grafico_periodo">Periodo:</label>
                        <select id="grafico_periodo" name="grafico_periodo">
                            <option value="dia">Diario</option>
                            <option value="semana">Semanal</option>
                            <option value="mes">Mensual</option>
                            <option value="anio">Anual</option>
                        </select>
                        <label for="grafico_fecha">Fecha base:</label>
                        <input type="date" id="grafico_fecha" name="grafico_fecha" />
                        <label for="grafico_producto">Producto:</label>
                        <select id="grafico_producto" name="grafico_producto">
                            <option value="">Todos los productos</option>
                        </select>
                    </div>
                    <div style="display:flex; gap:8px; margin-top:8px;">
                        <button id="grafico_actualizar" class="btn">Actualizar gráfico</button>
                        <button id="grafico_limpiar" class="btn btn-secondary">Borrar filtros</button>
                    </div>
                </div>
                <!-- Área del gráfico -->
                <div id="grafico_contenedor" style="margin-top:16px;">
                    <div id="grafico_resumen" class="card" style="margin-bottom:12px;">
                        <h3>Resumen</h3>
                        <div><strong>Total unidades pedidas:</strong> <span id="grafico_total_unidades">0</span></div>
                    </div>
                    <div id="grafico_barras" style="display:flex; align-items:flex-end; gap:8px; height:260px; padding:12px; border:1px solid #ddd; border-radius:8px; overflow-x:auto;">
                        <!-- Barras del gráfico se renderizan aquí -->
                    </div>
                    <div id="grafico_etiquetas" style="display:flex; gap:8px; justify-content:flex-start; align-items:center; margin-top:8px; font-size:12px; color:#555; overflow-x:auto;">
                        <!-- Etiquetas del eje X -->
                    </div>
                    <div id="grafico_leyenda" class="grafico-leyenda">
                        <p><strong>Leyenda del gráfico</strong></p>
                        <div class="leyenda-items">
                            <span class="leyenda-item">
                                <span class="leyenda-marca leyenda-marca-laboral"></span>
                                Día laborable con pedidos
                            </span>
                            <span class="leyenda-item">
                                <span class="leyenda-marca leyenda-marca-no-laboral"></span>
                                Día no laborable (vista diaria)
                            </span>
                            <span class="leyenda-item">
                                <span class="leyenda-marca leyenda-marca-actual"></span>
                                Periodo actual resaltado
                            </span>
                        </div>
                        <small>Cada barra representa unidades pedidas en el periodo seleccionado.</small>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Detalles de Deuda -->
    <div id="modal-detalle-deuda" class="modal">
        <div class="modal-content modal-detalle">
            <div class="modal-header">
                <h2>Detalles de la Deuda</h2>
                <button id="btnToggleMonedaAdminCajaDetalle" type="button" class="btn-toggle-moneda">Moneda: USD</button>
                <span class="close" onclick="cerrarModalDetalle()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="contenido-detalle-deuda">
                    <!-- El contenido se cargará dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Detalle de Venta -->
    <div id="modal-detalle-venta" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detalle de Pedido</h2>
                <span class="close" onclick="cerrarModalVenta()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="contenido-detalle-venta"></div>
            </div>
        </div>
    </div>

    <!-- Modal de Detalle por Producto -->
    <div id="modal-detalle-producto" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detalle por Producto</h2>
                <span class="close" onclick="cerrarModalProducto()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="contenido-detalle-producto"></div>
            </div>
        </div>
    </div>

    <?php require_once '../../../acces/footer/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>