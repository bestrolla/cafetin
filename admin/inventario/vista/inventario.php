<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/cafetin/acces/css/main.css">
    <link rel="stylesheet" href="inventario.css?v=1.4">
</head>
<body>
    <?php require_once '../../../acces/nav_admin/nav_admin.php'; ?>

    <main class="container">
        <div class="inventory-container">
            <h1>Gestión de Inventario</h1>

            <div class="form-container">
                <h2>Agregar Nuevo Producto</h2>
                <form id="form-producto" class="form-grid">
                    <div>
                        <label for="nombre_produc">Nombre:</label>
                        <input type="text" id="nombre_produc" name="nombre_produc" required>
                    </div>
                    <div>
                        <label for="caja_produc">Cajas:</label>
                        <input type="number" id="caja_produc" name="caja_produc" min="0">
                    </div>
                    <div>
                        <label for="cantidad_caja">Unidades por Caja:</label>
                        <input type="number" id="cantidad_caja" name="cantidad_caja" min="0">
                    </div>
                    <div>
                        <label for="precio_caja">Precio por Caja:</label>
                        <input type="number" id="precio_caja" name="precio_caja" min="0" step="0.01">
                    </div>
                    <div>
                        <label for="precio_venta">Precio de Venta:</label>
                        <input type="number" id="precio_venta" name="precio_venta" min="0" step="0.01" required>
                    </div>
                    <div>
                        <label for="precio_produc">Precio por Unidad (Calculado):</label>
                        <input type="number" id="precio_produc" name="precio_produc" min="0" step="0.01" required readonly>
                    </div>
                    <button type="submit" class="submit-btn">Guardar Producto</button>
                </form>
            </div>

            <div class="table-container">
                <h2>Lista de Productos</h2>
                <div class="search-container">
                    <div class="search-row">
                        <div class="search-input-wrap">
                            <span class="icon-search" aria-hidden="true">
                                <!-- SVG lupa -->
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                  <path d="M21 21l-4.35-4.35" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                  <circle cx="11" cy="11" r="6" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <input type="text" id="search-input" placeholder="Buscar productos por nombre." aria-label="Buscar productos">
                        </div>

                        <div class="select-wrap">
                            <select id="status-filter" class="styled-select" aria-label="Filtrar por estado del producto">
                                <option value="todo">Todos</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <table id="tabla-inventario" class="tabla-factura">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Unidades Totales</th>
                            <th>Cajas</th>
                            <th>Precio Producto</th>
                            <th>Precio de Venta</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargarán dinámicamente via JavaScript desde la API -->
                    </tbody>
                </table>

                <div id="pagination" class="pagination" aria-label="Paginación productos"></div>
            </div>
        </div>
    </main>

    <!-- Modal para editar producto -->
    <div id="modal-editar" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Editar Producto</h3>
                <span class="close" onclick="cerrarModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="form-editar" onsubmit="guardarProducto(event)">
                    <input type="hidden" id="edit-id" name="id_producto">
                    
                    <div class="form-group">
                        <label for="edit-nombre">Nombre del Producto</label>
                        <input type="text" id="edit-nombre" name="nombre_produc" required placeholder="Ingrese el nombre del producto">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-cajas">Cantidad de Cajas</label>
                        <input type="number" id="edit-cajas" name="caja_produc" min="0" required placeholder="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-unidades">Unidades por Caja</label>
                        <input type="number" id="edit-unidades" name="cantidad_caja" min="1" required placeholder="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-precio-caja">Precio por Caja</label>
                        <input type="number" id="edit-precio-caja" name="precio_caja" step="0.01" min="0" placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-precio-unidad">Precio por Unidad</label>
                        <input type="number" id="edit-precio-unidad" name="precio_produc" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-precio-venta">Precio de Venta</label>
                        <input type="number" id="edit-precio-venta" name="precio_venta" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    
                    <div class="form-group toggle-group">
                        <label for="edit-activo" class="toggle-label">Producto Activo</label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="edit-activo" name="activo" class="toggle-input">
                            <span class="toggle-slider">
                                <span class="toggle-button"></span>
                            </span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="form-actions">
                <button type="button" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" form="form-editar">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <?php require_once '../../../acces/footer/footer.php'; ?>
    <script src="script.js?v=1.3"></script>
</body>
</html>