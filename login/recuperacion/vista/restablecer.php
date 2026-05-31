<?php
require_once __DIR__ . '/../../../acces/security_headers.php';
require_once __DIR__ . '/../../../acces/csrf.php';
$csrf = csrfEnsureToken();

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restablecer contraseña</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(appUrl('/acces/css/main.css'), ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="<?php echo htmlspecialchars(appUrl('/login/recuperacion/vista/style.css'), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
  <section class="container">
    <div class="login-form recuperacion">
      <form action="<?php echo htmlspecialchars(appUrl('/login/recuperacion/logica/restablecer_password.php'), ENT_QUOTES, 'UTF-8'); ?>" method="POST">
        <h2>Restablecer contraseña</h2>
        <hr>
        <div class="inputBox">
          <input type="password" name="nueva_contrasena" id="nueva_contrasena" required minlength="6" maxlength="12" placeholder=" ">
          <span>Nueva contraseña</span>
          <span class="toggle-password" onclick="togglePasswordFor('nueva_contrasena', this)" aria-label="Mostrar u ocultar contraseña" title="Mostrar/Ocultar">
            <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="12" cy="12" r="3" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <svg class="icon-eye-off" style="display:none" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M3 3l18 18" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M10.584 10.59a2 2 0 102.828 2.828" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M9.88 5.09A10.943 10.943 0 0112 5c7 0 11 7 11 7a20.02 20.02 0 01-4.522 4.9M6.61 6.61C3.78 8.2 1.999 12 1.999 12a20.016 20.016 0 005.936 5.27" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
        </div>
        <div class="inputBox">
          <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" required minlength="6" maxlength="12" placeholder=" ">
          <span>Confirmar contraseña</span>
          <span class="toggle-password" onclick="togglePasswordFor('confirmar_contrasena', this)" aria-label="Mostrar u ocultar contraseña" title="Mostrar/Ocultar">
            <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="12" cy="12" r="3" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <svg class="icon-eye-off" style="display:none" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M3 3l18 18" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M10.584 10.59a2 2 0 102.828 2.828" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M9.88 5.09A10.943 10.943 0 0112 5c7 0 11 7 11 7a20.02 20.02 0 01-4.522 4.9M6.61 6.61C3.78 8.2 1.999 12 1.999 12a20.016 20.016 0 005.936 5.27" stroke="#4B5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
        </div>
        <div class="password-legend" role="note" aria-live="polite">
          <strong>Indicaciones de la contraseña</strong>
          <ul>
            <li>Debe tener entre 6 y 12 caracteres.</li>
            <li>Para mayor seguridad, combina letras, números y símbolos.</li>
            <li>Ambos campos deben coincidir exactamente.</li>
          </ul>
        </div>
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
        <input class="button" type="submit" value="Guardar nueva contraseña">
        <div class="links">
          <a href="<?php echo htmlspecialchars(appUrl('/login/inicio/vista/inicio.php'), ENT_QUOTES, 'UTF-8'); ?>">Volver al inicio</a>
          <!-- <a href="./solicitar.php">Solicitar otro enlace</a> -->
        </div>
        <?php if (isset($_GET['error'])): ?>
          <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['mensaje'])): ?>
          <div class="success-message"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
        <?php endif; ?>
        <div class="info">Por seguridad, este enlace expira en 30 minutos.</div>
      </form>
    </div>
  </section>
  <?php require_once __DIR__ . '/../../../acces/footer/footer.php'; ?>
  <script>
    function togglePasswordFor(id, trigger) {
      const input = document.getElementById(id);
      if (!input) return;
      const eyeOpen = trigger.querySelector('.icon-eye');
      const eyeOff = trigger.querySelector('.icon-eye-off');
      if (input.type === 'password') {
        input.type = 'text';
        if (eyeOpen && eyeOff) { eyeOpen.style.display = 'none'; eyeOff.style.display = 'block'; }
      } else {
        input.type = 'password';
        if (eyeOpen && eyeOff) { eyeOpen.style.display = 'block'; eyeOff.style.display = 'none'; }
      }
    }
  </script>
</body>
</html>