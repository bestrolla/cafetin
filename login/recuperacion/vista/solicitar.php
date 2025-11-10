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
  <title>Recuperación de contraseña</title>
  <link rel="stylesheet" href="../../../acces/css/main.css">
  <link rel="stylesheet" href="../inicio/vista/style.css">
</head>
<body>
  <section class="container">
    <div class="login-form">
      <form id="form-solicitar" action="../logica/solicitar_reset.php" method="POST">
        <h2>Recuperación de contraseña</h2>
        <hr>
        <div class="inputBox">
          <input type="text" name="usuario" id="usuario" required>
          <span>Usuario</span>
        </div>
        <div class="inputBox">
          <input type="text" name="cedula" required pattern="^[0-9]{5,}$" title="Ingrese su cédula (solo números)">
          <span>Cédula</span>
        </div>
        <div class="inputBox" id="pregunta-wrapper" style="display:none;">
          <input type="text" id="pregunta" disabled>
          <span>Tu pregunta de seguridad</span>
        </div>
        <input type="hidden" name="id_pregunta" id="id_pregunta">
        <div class="inputBox">
          <input type="text" name="respuesta" placeholder="Respuesta a tu pregunta de seguridad">
          <span>Respuesta de seguridad (si aplica)</span>
        </div>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
        <input class="button" type="submit" value="Generar enlace">
        <div class="links" style="margin-top: 10px;">
          <a href="../../inicio/vista/inicio.php">Volver al inicio</a>
          <a href="../../inicio/vista/inicio.php">Iniciar sesión</a>
        </div>
        <?php if (isset($_GET['error'])): ?>
          <div class="error-message" style="color: #ff4444; text-align: center; margin: 10px 0; padding: 10px; background: rgba(255,68,68,0.1); border-radius: 5px;"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['mensaje'])): ?>
          <div class="success-message" style="color: #44ff44; text-align: center; margin: 10px 0; padding: 10px; background: rgba(68,255,68,0.1); border-radius: 5px;"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
        <?php endif; ?>
        <div class="info" style="font-size: 12px; color: #555; margin-top: 8px;">Por seguridad, el enlace expira en 30 minutos.</div>
      </form>
    </div>
  </section>
  <?php require_once '../../../acces/footer/footer.php'; ?>
  <script>
    (function(){
      const usuarioInput = document.getElementById('usuario');
      const preguntaWrapper = document.getElementById('pregunta-wrapper');
      const preguntaInput = document.getElementById('pregunta');
      const csrf = '<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>';

      let timeout;
      usuarioInput.addEventListener('input', function() {
        const usuario = usuarioInput.value.trim();
        clearTimeout(timeout);
        if (usuario.length < 2) {
          preguntaWrapper.style.display = 'none';
          preguntaInput.value = '';
          return;
        }
        timeout = setTimeout(async () => {
          try {
            const res = await fetch('../logica/obtener_pregunta_usuario.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrf
              },
              body: JSON.stringify({ usuario })
            });
            const data = await res.json();
            if (data.success && data.has_question && Array.isArray(data.preguntas) && data.preguntas.length > 0) {
              // Elegir una pregunta aleatoria de las disponibles
              const idx = Math.floor(Math.random() * Math.min(3, data.preguntas.length));
              const chosen = data.preguntas[idx];
              preguntaWrapper.style.display = 'block';
              preguntaInput.value = chosen.pregunta;
              document.getElementById('id_pregunta').value = chosen.id_pregunta;
            } else {
              preguntaWrapper.style.display = 'none';
              preguntaInput.value = '';
              document.getElementById('id_pregunta').value = '';
            }
          } catch (e) {
            preguntaWrapper.style.display = 'none';
            preguntaInput.value = '';
            document.getElementById('id_pregunta').value = '';
          }
        }, 300);
      });
    })();
  </script>
</body>
</html>