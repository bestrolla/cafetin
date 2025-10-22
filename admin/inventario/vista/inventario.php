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
    <link rel="stylesheet" href="inventario.css?v=1.3">
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
                <input type="text" id="search-input" placeholder="Buscar productos..." onkeyup="filterTable()">
                <table id="tabla-inventario">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Cajas</th>
                            <th>Total Unidades</th>
                            <th>Precio/Caja</th>
                            <th>Precio/Unidad</th>
                            <th>Precio Venta</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filas de productos se insertarán aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal para Editar Producto -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Editar Producto</h2>
            <form id="form-edit-producto" class="form-grid">
                <input type="hidden" id="edit_id_producto" name="id_producto">
                <div>
                    <label for="edit_nombre_produc">Nombre:</label>
                    <input type="text" id="edit_nombre_produc" name="nombre_produc" required>
                </div>
                <div>
                    <label for="edit_caja_produc">Cajas:</label>
                    <input type="number" id="edit_caja_produc" name="caja_produc" min="0">
                </div>
                <div>
                    <label for="edit_cantidad_caja">Unidades por Caja:</label>
                    <input type="number" id="edit_cantidad_caja" name="cantidad_caja" min="0">
                </div>
                <div>
                    <label for="edit_precio_caja">Precio por Caja:</label>
                    <input type="number" id="edit_precio_caja" name="precio_caja" min="0" step="0.01">
                </div>
                <div>
                    <label for="edit_precio_produc">Precio por Unidad (Calculado):</label>
                    <input type="number" id="edit_precio_produc" name="precio_produc" min="0" step="0.01" required readonly>
                </div>
                <div>
                    <label for="edit_precio_venta">Precio de Venta:</label>
                    <input type="number" id="edit_precio_venta" name="precio_venta" min="0" step="0.01" required>
                </div>
                <button type="submit" class="submit-btn">Actualizar Producto</button>
            </form>
        </div>
    </div>

    <?php require_once '../../../acces/footer/footer.php'; ?>
    <script src="script.js?v=1.2"></script>
</body>
</html>