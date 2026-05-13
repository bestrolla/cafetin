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
  <link rel="stylesheet" href="style.css">
  
</head>
<body>
  <section class="container">
    <div class="login-form recuperacion">
      <form id="form-solicitar" action="../logica/solicitar_reset.php" method="POST">
        <h2>Recuperación de contraseña</h2>
        <hr>
        <p class="subtitle">Ingresa tu usuario, elige una de tus preguntas de seguridad guardadas y respóndela.</p>
        <div class="inputBox">
          <input type="text" name="usuario" id="usuario" required placeholder=" ">
          <span>Usuario</span>
        </div>
        <div class="inputBox" id="pregunta-wrapper">
          <select name="id_pregunta" id="id_pregunta" required>
            <option value="" disabled selected>Primero escribe tu usuario…</option>
          </select>
          <span>Elige tu pregunta de seguridad</span>
          <div class="hint">Selecciona cualquiera de las preguntas que tienes configuradas.</div>
        </div>
        <div class="inputBox">
          <input type="text" name="respuesta" placeholder=" " required>
          <span>Respuesta de seguridad</span>
        </div>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
        <input class="button" type="submit" value="Siguiente">
        <div class="links">
          <a href="../../inicio/vista/inicio.php">Volver al inicio</a>
          <!-- <a href="../../inicio/vista/inicio.php">Iniciar sesión</a> -->
        </div>
        <?php if (isset($_GET['error'])): ?>
          <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['mensaje'])): ?>
          <div class="success-message"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
        <?php endif; ?>
        <div class="info">Por seguridad, el enlace expira en 30 minutos.</div>
      </form>
    </div>
  </section>
  <?php require_once '../../../acces/footer/footer.php'; ?>
  <script>
    (function(){
      const usuarioInput = document.getElementById('usuario');
      const preguntaWrapper = document.getElementById('pregunta-wrapper');
      const selectPreguntas = document.getElementById('id_pregunta');
      const csrf = '<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>';

      let timeout;
      usuarioInput.addEventListener('input', function() {
        const usuario = usuarioInput.value.trim();
        clearTimeout(timeout);
        if (usuario.length < 2) {
          selectPreguntas.innerHTML = '<option value="" disabled selected>Primero escribe tu usuario…</option>';
          selectPreguntas.disabled = true;
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
              // Popular el select con las preguntas del usuario
              selectPreguntas.disabled = false;
              selectPreguntas.innerHTML = '';
              const placeholder = document.createElement('option');
              placeholder.value = '';
              placeholder.textContent = 'Selecciona una pregunta…';
              placeholder.disabled = true;
              placeholder.selected = true;
              selectPreguntas.appendChild(placeholder);
              data.preguntas.slice(0, 3).forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id_pregunta;
                opt.textContent = p.pregunta;
                selectPreguntas.appendChild(opt);
              });
            } else {
              selectPreguntas.disabled = true;
              selectPreguntas.innerHTML = '<option value="" disabled selected>No tienes preguntas configuradas</option>';
            }
          } catch (e) {
            selectPreguntas.disabled = true;
            selectPreguntas.innerHTML = '<option value="" disabled selected>Error al cargar preguntas</option>';
          }
        }, 300);
      });
    })();
  </script>
</body>
</html>