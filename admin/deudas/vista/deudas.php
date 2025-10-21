<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Deudas - Administrador</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php 
    require_once '../../../acces/nav_admin/nav_cajero.php'; 
    ?>

    <div class="container">
        <h1>Gestión de Deudas</h1>
        <p>Administra las deudas de los clientes.</p>

        <div class="deudas-container">
            <div class="deudas-list">
                <h2>Clientes con Deudas</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID Cliente</th>
                            <th>Nombre</th>
                            <th>Deuda Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>CLI-001</td>
                            <td>Juan Pérez</td>
                            <td class="deuda">$ 55.00</td>
                            <td><button class="btn-ver">Ver Detalles</button></td>
                        </tr>
                        <tr>
                            <td>CLI-008</td>
                            <td>Ana Gómez</td>
                            <td class="deuda">$ 120.00</td>
                            <td><button class="btn-ver">Ver Detalles</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="deudas-form">
                <h2>Registrar Nueva Deuda</h2>
                <form id="form-nueva-deuda">
                    <label for="cliente">Cliente:</label>
                    <input type="text" id="cliente" name="cliente" placeholder="Buscar cliente por nombre o ID">

                    <label for="monto">Monto:</label>
                    <input type="number" id="monto" name="monto" step="0.01" required>

                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="3"></textarea>

                    <button type="submit" class="submit-btn">Agregar Deuda</button>
                </form>
            </div>
        </div>
    </div>

    <?php 
    require_once '../../../acces/footer/footer.php'; 
    ?>
    <script src="script.js"></script>
</body>
</html>