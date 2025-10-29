<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lobby | Cajero</title>
  <link rel="stylesheet" href="../../../acces/css/main.css">
  <link rel="stylesheet" href="lobby.css">
</head>
<body>
<?php
require_once '../../../acces/nav_cajero/nav_cajero.php';
?>
<section class="main">
  <div class="container">
    
    <!-- 🧍 CLIENTE -->
    <div class="container_cliente" id="container-cliente">
      <h2>Cliente</h2>
      <hr>
      <label>Cédula <input id="cliente-cedula" type="text" placeholder="Cédula"></label>
      <label>Nombre <input id="cliente-nombre" type="text" placeholder="Nombre"></label>
      <label>Apellido <input id="cliente-apellido" type="text" placeholder="Apellido"></label>
      <label>Teléfono <input id="cliente-telefono" type="text" placeholder="Teléfono"></label>
      <label>Alias <input id="cliente-alias" type="text" placeholder="Alias"></label>

      <div class="cliente-actions">
        <button id="btn-registrar" class="button registrar" type="button" aria-label="Registrar nuevo cliente">Registrar</button>
        <button id="btn-siguiente" class="button siguiente" type="button" aria-label="Continuar con cliente seleccionado">Siguiente</button>
      </div>
    </div>

    <!-- 💰 FACTURA -->
    <div id="container-factura" class="container_factura hidden">
      <div class="factura-header">
        <button id="btn-eliminar-factura" class="btn-eliminar-factura" onclick="eliminarFacturaCompleta()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 6h18"></path>
            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
            <path d="M8 6V4c0-1 1-2 2-2h4c0 1 1 2 2 2v2"></path>
            <line x1="10" y1="11" x2="10" y2="17"></line>
            <line x1="14" y1="11" x2="14" y2="17"></line>
          </svg>
          Eliminar Factura
        </button>
        <h2>Factura</h2>
      </div>
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
            <button id="btn-ver-cuenta" class="button cuenta" type="button" aria-label="Agregar productos a cuenta del cliente">Agregar a cuenta</button>
            <button id="btn-pagar" class="button procesar" type="button" aria-label="Procesar pago de la factura">Pagar</button>
          </div>
        </div>

        <!-- 🛒 Agregar producto -->
        <div class="agregar-panel">
          <h3>Agregar producto</h3>
          <input id="busqueda-producto" type="text" placeholder="Buscar producto..." aria-label="Buscar productos por nombre">

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
                $sql = "SELECT id_producto, nombre_produc, precio_venta FROM inventario WHERE activo = TRUE ORDER BY nombre_produc ASC";
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Iterar sobre los productos y mostrarlos en la tabla
                if ($productos) {
                    foreach ($productos as $producto) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($producto['id_producto']) . "</td>";
                        echo "<td>" . htmlspecialchars($producto['nombre_produc']) . "</td>";
                        echo "<td>$" . htmlspecialchars(number_format($producto['precio_venta'], 2)) . "</td>";
                        echo "<td><button class=\"btn-agregar-producto modern-btn\" data-id=\"" . htmlspecialchars($producto['id_producto']) . "\" data-nombre=\"" . htmlspecialchars($producto['nombre_produc']) . "\" data-precio=\"" . htmlspecialchars($producto['precio_venta']) . "\" aria-label=\"Agregar " . htmlspecialchars($producto['nombre_produc']) . " a la factura\" onclick=\"agregarProductoAFactura(this)\">Agregar</button></td>";
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
