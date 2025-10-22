<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Cajero</title>
    <link rel="stylesheet" href="style.css?v=1.1">
    <link rel="stylesheet" href="/cafetin/acces/css/main.css">
</head>
<body>
    <?php require_once '../../../acces/nav_admin/nav_admin.php'; ?>

    <main class="container">
        <form id="form-agregar-cajero" method="POST">
            <h1>Agregar Nuevo Cajero</h1>
            <p>Complete el formulario para registrar un nuevo cajero en el sistema.</p>
            <div class="form-grid">
                <!-- Columna 1: Datos Personales -->
                <div class="form-column">
                    <h2>Datos Personales</h2>
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>

                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" required>

                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono">
                </div>

                <!-- Columna 2: Datos de Usuario -->
                <div class="form-column">
                    <h2>Datos de Acceso</h2>
                    <label for="usuario">Nombre de Usuario:</label>
                    <input type="text" id="usuario" name="usuario" required>

                    <label for="contrasena">Contraseña:</label>
                    <input type="password" id="contrasena" name="contrasena" required>
                </div>
            </div>

            <div id="response-message" class="hidden"></div>

            <button type="submit" class="submit-btn">Registrar Cajero</button>
        </form>
    </main>

    <?php require_once '../../../acces/footer/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>