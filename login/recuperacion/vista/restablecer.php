<?php
require_once '../../../acces/security_headers.php';
require_once '../../../acces/csrf.php';
$csrf = csrfEnsureToken();

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restablecer contraseña</title>
  <link rel="stylesheet" href="../../../acces/css/main.css">
  <link rel="stylesheet" href="../inicio/vista/style.css">
</head>
<body>
  <section class="container">
    <div class="login-form">
      <form action="../logica/restablecer_password.php" method="POST">
        <h2>Restablecer contraseña</h2>
        <hr>
        <div class="inputBox">
          <input type="password" name="nueva_contrasena" id="nueva_contrasena" required minlength="6" maxlength="64">
          <span>Nueva contraseña</span>
          <span class="toggle-password" onclick="toggleVisibility('nueva_contrasena')" title="Mostrar/Ocultar">MO</span>
        </div>
        <div class="inputBox">
          <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" required minlength="6" maxlength="64">
          <span>Confirmar contraseña</span>
          <span class="toggle-password" onclick="toggleVisibility('confirmar_contrasena')" title="Mostrar/Ocultar">MO</span>
        </div>
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
        <input class="button" type="submit" value="Guardar nueva contraseña">
        <div class="links" style="margin-top: 10px;">
          <a href="../../inicio/vista/inicio.php">Volver al inicio</a>
          <a href="./solicitar.php">Solicitar otro enlace</a>
        </div>
        <?php if (isset($_GET['error'])): ?>
          <div class="error-message" style="color: #ff4444; text-align: center; margin: 10px 0; padding: 10px; background: rgba(255,68,68,0.1); border-radius: 5px;"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['mensaje'])): ?>
          <div class="success-message" style="color: #44ff44; text-align: center; margin: 10px 0; padding: 10px; background: rgba(68,255,68,0.1); border-radius: 5px;"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
        <?php endif; ?>
        <div class="info" style="font-size: 12px; color: #555; margin-top: 8px;">Por seguridad, este enlace expira en 30 minutos.</div>
      </form>
    </div>
  </section>
  <?php require_once '../../../acces/footer/footer.php'; ?>
  <script>
    function toggleVisibility(id) {
      const el = document.getElementById(id);
      el.type = (el.type === 'password') ? 'text' : 'password';
    }
  </script>
</body>
</html>