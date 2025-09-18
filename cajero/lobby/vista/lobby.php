<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lobby | Cajero</title>
  <link rel="stylesheet" href="lobby.css">
</head>
<body>
<?php
require_once '../../../acces/nav_cajero/nav_cajero.php';
?>
<section class="main">
  <div class="container">
    
    <!-- 🧍 CLIENTE -->
    <div class="container_cliente">
      <h2>Cliente</h2>
      <hr>
      <label>Cédula <input id="cliente-cedula" type="text" placeholder="Cédula"></label>
      <label>Nombre <input id="cliente-nombre" type="text" placeholder="Nombre"></label>
      <label>Apellido <input id="cliente-apellido" type="text" placeholder="Apellido"></label>
      <label>Teléfono <input id="cliente-telefono" type="text" placeholder="Teléfono"></label>
      <label>Alias <input id="cliente-alias" type="text" placeholder="Alias"></label>

      <div class="cliente-actions">
        <button id="btn-registrar" class="button registrar" type="button">Registrar</button>
        <button id="btn-siguiente" class="button siguiente" type="button">Siguiente</button>
      </div>
    </div>

    <!-- 💰 FACTURA -->
    <div id="container-factura" class="container_factura hidden">
      <h2>Factura</h2>
      <hr>
      <div class="cliente-resumen">
        <h3>Cliente seleccionado</h3>
        <p id="resumen-cliente">Ninguno</p>
      </div>

      <div class="factura-content">
        <!-- 🧾 Factura actual -->
        <div class="factura-panel">
          <h3>Productos agregados</h3>
          <table class="tabla-factura">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio ($)</th>
                <th>Precio (Bs)</th>
                <th>Total</th>
                <th>Eliminar</th>
              </tr>
            </thead>
            <tbody id="tabla-factura-body">
              <!-- Filas dinámicas -->
            </tbody>
          </table>

          <div class="total">
            <h3 id="total-text">Total: $0.00 | Bs. 0.00</h3>
          </div>

          <div class="factura-actions">
            <button id="btn-ver-cuenta" class="button cuenta" type="button">Agregar a cuenta</button>
            <button id="btn-pagar" class="button procesar" type="button">Pagar</button>
          </div>
        </div>

        <!-- 🛒 Agregar producto -->
        <div class="agregar-panel">
          <h3>Agregar producto</h3>
          <input id="busqueda-producto" type="text" placeholder="Buscar producto...">

          <div class="lista-productos">
            <table class="tabla-productos">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nombre</th>
                  <th>Precio ($)</th>
                  <th>Agregar</th>
                </tr>
              </thead>
              <tbody id="productos-body">
                <?php
                // Incluir el archivo de conexión
                require_once '../../../BBDD/BBDD.php';

                // Consulta para obtener los productos activos del inventario
                $sql = "SELECT id_producto, nombre_produc, precio_produc FROM inventario WHERE activo = TRUE ORDER BY nombre_produc ASC";
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Iterar sobre los productos y mostrarlos en la tabla
                if ($productos) {
                    foreach ($productos as $producto) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($producto['id_producto']) . "</td>";
                        echo "<td>" . htmlspecialchars($producto['nombre_produc']) . "</td>";
                        echo "<td>$" . htmlspecialchars(number_format($producto['precio_produc'], 2)) . "</td>";
                        echo "<td><button class=\"btn-agregar-producto\" data-id=\"" . htmlspecialchars($producto['id_producto']) . "\" data-nombre=\"" . htmlspecialchars($producto['nombre_produc']) . "\" data-precio=\"" . htmlspecialchars($producto['precio_produc']) . "\">Agregar</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan=\"4\">No hay productos disponibles.</td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<?php
require_once '../../../acces/footer/footer.php';
?>
<script src="script.js"></script>
</body>
</html>
