<?php
require_once __DIR__ . '/../../../acces/security_headers.php';
require_once __DIR__ . '/../../../acces/csrf.php';
$csrf = csrfEnsureToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CDC | login</title>
  <!-- ✅ Desde cualquier página funciona -->
<link rel="stylesheet" href="<?php echo htmlspecialchars(appUrl('/acces/css/main.css'), ENT_QUOTES, 'UTF-8'); ?>">
<link rel="stylesheet" href="<?php echo htmlspecialchars(appUrl('/login/inicio/vista/style.css'), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
  <section class="container">

  <div class="login-form">
  <form action="<?php echo htmlspecialchars(appUrl('/login/inicio/logica/procesar_login.php'), ENT_QUOTES, 'UTF-8'); ?>" method="POST">
    <h2>Iniciar Sesión</h2>
     <hr>
    <div class="inputBox">
      <input type="text" name="usuario" required>
      <span>Usuario</span>
      <i></i>
    </div>
    <div class="inputBox">
      <input type="password" name="contrasena" id="contrasena" required>
      <span>Contraseña</span>
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
    <div id="alert-container" class="alert-container">
      <?php if (isset($_GET['error'])): ?>
        <div id="error-message" class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
      <?php elseif (isset($_GET['mensaje'])): ?>
        <div id="success-message" class="alert alert-success"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
      <?php endif; ?>
    </div>

    <div class="links">
      <a href="<?php echo htmlspecialchars(appUrl('/login/recuperacion/vista/solicitar.php'), ENT_QUOTES, 'UTF-8'); ?>">¿Olvidaste tu contraseña?</a>
      <!-- <a href="#">Registrate</a> -->
    </div>
    
    <input class="button" type="submit" value="Iniciar Sesión">
  </form>

  </div>
  </section>

  <?php
 require __DIR__ . '/../../../acces/footer/footer.php';
  
  
  
  ?>
 
  <script src="<?php echo htmlspecialchars(appUrl('/login/inicio/vista/script.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
  <script>window.APP_BASE = <?php echo json_encode(appBasePath(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;</script>
</body>
</html>