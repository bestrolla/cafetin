<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CDC | login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <section class="container">

  <div class="login-form">
  <form action="login.php" method="POST">
    <h2>Iniciar Sesion</h2>
     <hr>
    <div class="inputBox">
      <input type="text" name="usuario" required>
      <span>Usuario</span>
      <i></i>
    </div>
    <div class="inputBox">
      <input type="password" name="contrasena" required>
      <span>Contrase&ntilde;a</span>
      <i></i>
    </div>
    <div class="links">
      <a href="#">¿Olvidaste tu contrase&ntilde;a?</a>
      <!-- <a href="#">Registrate</a> -->
    </div>
    <input class="button" type="submit" value="Iniciar Sesion">
  </form>

  </div>
  </section>

  <?php
  require_once '../../../acces/footer/footer.php';
  
  
  
  ?>
 
  <script src="script.js"></script>
</body>
</html>