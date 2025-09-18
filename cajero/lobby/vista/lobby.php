<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lobby | cajero</title>
  <link rel="stylesheet" href="lobby.css">
</head>
<body>
<?php
require_once '../../../acces/nav_cajero/nav_cajero.php';
?>
  <section class="main">
    <div class="container">
      <div class="container_cliente">
        <h2>Cliente</h2>
        <hr>
        <label> Cédula <input type="text" placeholder="Cédula"></label>
        <label> Nombre <input type="text" placeholder="Nombre"></label>
        <label> Apellido <input type="text" placeholder="Apellido"></label>
        <label> Teléfono <input type="text" placeholder="Teléfono"></label>
        <label> Alias <input type="text" placeholder="Alias"></label>

        <input class="button" type="submit" value="Registrar">
      </div>
      <div class="container_factura">
        <h2>Factura</h2>
        <hr>
        <div class="producto-row">
          <label>producto
            <select name="factura" id="factura">
              <option value="" disabled selected>producto</option>
              <option value="001">001</option>
              <option value="002">002</option>
              <option value="003">003</option>
            </select>
          </label>
          <label>Cantidad
            <input type="text" placeholder="Cantidad">
          </label>
          <input class="button" type="submit" value="Agregar">
        </div>


        <div class="tabla">
          <table class="tabla-factura">
            <thead>
              <tr>
                <th>Factura</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
                <th>Eliminar</th> <!-- Nueva columna -->
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>001</td>
                <td>2</td>
                <td>$50.00</td>
                <td>$100.00</td>
                <td><button class="eliminar-btn" type="button">Eliminar</button></td>
              </tr>
              <tr>
                <td>002</td>
                <td>1</td>
                <td>$30.00</td>
                <td>$30.00</td>
                <td><button class="eliminar-btn" type="button">Eliminar</button></td>
              </tr>
            </tbody>
          </table>
          <div class="total">
            <h3>Total: $130.00</h3>
            </div>
        </div>
          <div class="factura-actions">
            <input class="button eliminar" type="submit" value="Eliminar">
            <input class="button cuenta" type="submit" value="cuenta">
            <input class="button procesar" type="submit" value=" Pagar">
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