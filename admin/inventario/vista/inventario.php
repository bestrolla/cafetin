<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario</title>
    <link rel="stylesheet" href="inventario.css">
</head>
<body>
    <div class="container">
        <div class="inventory-container">
            <h1>Gestión de Inventario</h1>

            <div class="form-container">
                <h2>Agregar o Editar Producto</h2>
                <form id="form-producto" class="form-grid">
                    <input type="hidden" id="id_producto" name="id_producto">
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
                        <label for="precio_produc">Precio por Unidad:</label>
                        <input type="number" id="precio_produc" name="precio_produc" min="0" step="0.01" required>
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
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Cajas</th>
                            <th>Unidades/Caja</th>
                            <th>Precio/Caja</th>
                            <th>Precio/Unidad</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filas de productos se insertarán aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
