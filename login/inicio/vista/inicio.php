<?php
require_once '../../../acces/security_headers.php';
require_once '../../../acces/csrf.php';
$csrf = csrfEnsureToken();
?>
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
      <span class="toggle-password" onclick="togglePassword()" aria-label="Mostrar u ocultar contraseña" title="Mostrar/Ocultar">
        <!-- Ojo abierto -->
        <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="12" cy="12" r="3" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <!-- Ojo tachado -->
        <svg class="icon-eye-off" style="display:none" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M3 3l18 18" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M10.584 10.59a2 2 0 102.828 2.828" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M9.88 5.09A10.943 10.943 0 0112 5c7 0 11 7 11 7a20.02 20.02 0 01-4.522 4.9M6.61 6.61C3.78 8.2 1.999 12 1.999 12a20.016 20.016 0 005.936 5.27" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </span>
    </div>
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="links">
      <a href="../../recuperacion/vista/solicitar.php">¿Olvidaste tu contrase&ntilde;a?</a>
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