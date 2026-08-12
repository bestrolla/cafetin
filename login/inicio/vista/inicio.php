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
  
  <!-- Fuente manuscrita opcional para dar efecto realista de nota -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600&display=swap" rel="stylesheet">

  <style>
    /* Estructura para alinear la nota y el contenedor de login lado a lado */
    .page-wrapper {
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      max-width: 1100px;
      margin: 0 auto;
    }

    /* 📌 Estilo de Nota Adhesiva (Sticky Note) */
    .sticky-note {
      position: absolute;
      left: calc(50% + 240px); /* Posiciona la nota a la derecha del formulario */
      top: 10px;
      width: 230px;
      padding: 20px 18px;
      background: #fef08a; /* Amarillo Post-it */
      color: #713f12;
      border-radius: 2px 2px 25px 2px; /* Esquina inferior doblada */
      box-shadow: 4px 6px 12px rgba(0, 0, 0, 0.15);
      transform: rotate(3deg); /* Efecto inclinado */
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      z-index: 10;
      font-family: 'Caveat', cursive, sans-serif; /* Tipografía tipo borrador */
    }

    .sticky-note:hover {
      transform: rotate(0deg) scale(1.03);
      box-shadow: 6px 10px 18px rgba(0, 0, 0, 0.2);
    }

    /* Alfiler / Pin rojo en la parte superior */
    .sticky-note::before {
      content: '';
      position: absolute;
      top: -8px;
      left: 50%;
      transform: translateX(-50%);
      width: 14px;
      height: 14px;
      background: #ef4444;
      border-radius: 50%;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .sticky-note h3 {
      margin: 0 0 8px 0;
      font-size: 1.3rem;
      text-align: center;
      border-bottom: 1px dashed #ca8a04;
      padding-bottom: 4px;
    }

    .sticky-note ul {
      margin: 0;
      padding-left: 15px;
      font-size: 1.1rem;
    }

    .sticky-note li {
      margin-bottom: 4px;
    }

    .sticky-note code {
      font-family: monospace;
      background-color: rgba(255, 255, 255, 0.6);
      padding: 1px 4px;
      border-radius: 3px;
      font-weight: bold;
    }

    /* Adaptación para pantallas pequeñas (móviles) */
    @media (max-width: 900px) {
      .page-wrapper {
        flex-direction: column-reverse;
      }
      .sticky-note {
        position: relative;
        left: auto;
        top: auto;
        margin-bottom: 20px;
        transform: rotate(-1deg);
        width: 90%;
        max-width: 350px;
      }
    }
  </style>
</head>
<body>

  <div class="page-wrapper">
    
    <!-- 📌 NOTA PEGADA (Fuera del container) -->
    <aside class="sticky-note">
      <h3>📌 Credenciales de prueba</h3>
      <ul>
        <li><strong>Usuario:</strong> <code>admin</code></li>
        <li><strong>Password:</strong> <code>123456</code></li>
      </ul>
    </aside>

    <!-- FORMULARIO DE LOGIN PRINCIPAL -->
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

  </div>

  <?php
  require __DIR__ . '/../../../acces/footer/footer.php';
  ?>
 
  <script src="<?php echo htmlspecialchars(appUrl('/login/inicio/vista/script.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
  <script>window.APP_BASE = <?php echo json_encode(appBasePath(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;</script>
</body>
</html>