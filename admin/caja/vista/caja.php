<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja - Administrador</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php 
    // Incluir el menú de navegación para el administrador
    require_once '../../../acces/nav_admin/nav_cajero.php'; 
    ?>

    <div class="container">
        <h1>Gestión de Caja</h1>
        <p>Supervisa el estado de la caja, revisa transacciones y realiza arqueos.</p>

        <div class="caja-summary">
            <div class="summary-card">
                <h2>Saldo Inicial</h2>
                <p class="amount">$ 100.00</p>
            </div>
            <div class="summary-card">
                <h2>Ventas Totales</h2>
                <p class="amount">$ 1,250.50</p>
            </div>
            <div class="summary-card">
                <h2>Total en Caja</h2>
                <p class="amount">$ 1,350.50</p>
            </div>
            <div class="summary-card">
                <h2>Estado</h2>
                <p class="status open">Abierta</p>
            </div>
        </div>

        <div class="caja-actions">
            <button class="action-btn">Abrir Caja</button>
            <button class="action-btn">Cerrar Caja (Arqueo)</button>
            <button class="action-btn">Registrar Gasto</button>
        </div>

        <div class="caja-transactions">
            <h2>Últimas Transacciones</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Fecha y Hora</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>101</td>
                        <td>Venta</td>
                        <td class="positive">+ $ 15.00</td>
                        <td>2023-10-27 10:30</td>
                        <td>Café Americano, Croissant</td>
                    </tr>
                    <tr>
                        <td>102</td>
                        <td>Gasto</td>
                        <td class="negative">- $ 25.00</td>
                        <td>2023-10-27 11:00</td>
                        <td>Compra de servilletas</td>
                    </tr>
                    <tr>
                        <td>103</td>
                        <td>Venta</td>
                        <td class="positive">+ $ 8.50</td>
                        <td>2023-10-27 11:15</td>
                        <td>Jugo de Naranja</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <?php 
    // Incluir el pie de página
    require_once '../../../acces/footer/footer.php'; 
    ?>
    <script src="script.js"></script>
</body>
</html>