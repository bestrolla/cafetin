<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CDC | login</title>
  <link rel="stylesheet" href="../../../acces/css/main.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <section class="container">

  <div class="login-form">
  <form action="../logica/procesar_login.php" method="POST">
    <h2>Iniciar Sesion</h2>
     <hr>
    <div class="inputBox">
      <input type="text" name="usuario" required>
      <span>Usuario</span>
      <i></i>
    </div>
    <div class="inputBox">
      <input type="password" name="contrasena" id="contrasena" required>
      <span>Contrase&ntilde;a</span>
      <i></i>
      <!-- <span class="toggle-password" onclick="togglePassword()">👁️</span> -->
    </div>
    <div class="links">
      <a href="#">¿Olvidaste tu contrase&ntilde;a?</a>
      <!-- <a href="#">Registrate</a> -->
    </div>
    
    <?php
    // Mostrar mensajes de error o información
    if (isset($_GET['error'])) {
        echo '<div class="error-message" style="color: #ff4444; text-align: center; margin: 10px 0; padding: 10px; background: rgba(255,68,68,0.1); border-radius: 5px;">' . htmlspecialchars($_GET['error']) . '</div>';
    }
    if (isset($_GET['mensaje'])) {
        echo '<div class="success-message" style="color: #44ff44; text-align: center; margin: 10px 0; padding: 10px; background: rgba(68,255,68,0.1); border-radius: 5px;">' . htmlspecialchars($_GET['mensaje']) . '</div>';
    }
    ?>
    
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