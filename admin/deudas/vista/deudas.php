<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Deudas</title>
    <link rel="stylesheet" href="../../../acces/css/main.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require_once '../../../acces/nav_admin/nav_admin.php'; ?>
    <div class="container">
        <div class="debts-container">
            <h1>Gestión de Deudas</h1>

            <div class="table-container">
                <table id="tabla-deudas">
                    <thead>
                        <tr>
                            <th>ID Crédito</th>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                            <th>Fecha Creación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filas de deudas se insertarán aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php require_once '../../../acces/footer/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>
