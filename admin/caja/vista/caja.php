<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja - Reporte de Ventas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="cash-container">
            <h1>Reporte de Ventas</h1>

            <div class="filters">
                <label for="start-date">Desde:</label>
                <input type="date" id="start-date" name="start-date">
                <label for="end-date">Hasta:</label>
                <input type="date" id="end-date" name="end-date">
                <button id="filter-btn">Filtrar</button>
            </div>

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
    </div>

    <script src="script.js"></script>
</body>
</html>
