<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>inventario | admin</title>
  <link rel="stylesheet" href="inventario.css">
</head>
<body>
<?php include '../../../acces/nav_admin/nav_cajero.php'; ?>
<section class="main">
  <div class="container">
    <div class="container_crud">
    <h2>Gestión de Productos</h2>

<form action="producto_crud.php" method="post">
  <input type="hidden" name="id" value="">
  <label for="nombre">Nombre:</label>
  <input type="text" id="nombre" name="nombre" required><br>

  <label for="descripcion">Descripción:</label>
  <input type="text" id="descripcion" name="descripcion"><br>

  <label for="precio">Precio:</label>
  <input type="number" id="precio" name="precio" step="0.01" required><br>

  <label for="cantidad">Cantidad:</label>
  <input type="number" id="cantidad" name="cantidad" required><br>

  <button type="submit" name="accion" value="crear">Crear</button>
  <button type="submit" name="accion" value="actualizar">Actualizar</button>
  <button type="submit" name="accion" value="eliminar">Eliminar</button>
</form>
</div>

<!-- Aquí puedes mostrar la lista de productos -->
<?php
// ...aquí iría el código para mostrar la tabla de productos...
?>
</div>
</section>
<?php include '../../../acces/footer/footer.php'; ?>
<script src="script.js"></script>
</body>
</html>