<?php
require_once '../../../acces/auth_check.php';
initSessionIfNeeded();
require_once '../../../acces/security_headers.php';
require_once '../../../acces/csrf.php';
require_once '../../../BBDD/BBDD.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /cafetin/login/recuperacion/vista/solicitar.php?error=' . urlencode('Método no permitido'));
  exit;
}

// Verificar CSRF
if (!csrfVerifyFromPost('csrf_token')) {
  header('Location: /cafetin/login/recuperacion/vista/solicitar.php?error=' . urlencode('Token CSRF inválido'));
  exit;
}

$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
$respuesta = isset($_POST['respuesta']) ? trim($_POST['respuesta']) : '';
$idPregunta = isset($_POST['id_pregunta']) ? (int)$_POST['id_pregunta'] : 0;

if ($usuario === '' || $cedula === '' || !preg_match('/^[0-9]{5,}$/', $cedula)) {
  header('Location: /cafetin/login/recuperacion/vista/solicitar.php?error=' . urlencode('Datos inválidos. Verifique usuario y cédula.'));
  exit;
}

try {
  // Usar PDO del sistema
  $conexion = (new Conexion())->conectar();

  // Asegurar tabla de resets
  $conexion->exec("CREATE TABLE IF NOT EXISTS password_reset (
    id_reset INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expiracion DATETIME NOT NULL,
    usado TINYINT(1) DEFAULT 0,
    creado TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_reset),
    KEY id_usuario (id_usuario)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4");

  // Buscar usuario y validar cédula
  $stmt = $conexion->prepare("SELECT u.id_usuario
                              FROM usuario u
                              JOIN persona p ON u.id_persona = p.id_persona
                              WHERE u.usuario = :usuario AND p.cedula = :cedula
                              LIMIT 1");
  $stmt->execute([':usuario' => $usuario, ':cedula' => $cedula]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    header('Location: /cafetin/login/recuperacion/vista/solicitar.php?error=' . urlencode('No se encontró coincidencia de usuario y cédula.'));
    exit;
  }

  $id_usuario = (int)$row['id_usuario'];

  // Verificar respuesta de seguridad si el usuario tiene preguntas asociadas
  $stmtCount = $conexion->prepare("SELECT COUNT(*) AS c FROM respuestas WHERE id_usuario = :id");
  $stmtCount->execute([':id' => $id_usuario]);
  $cRow = $stmtCount->fetch(PDO::FETCH_ASSOC);
  $tienePreguntas = $cRow && (int)$cRow['c'] > 0;
  if ($tienePreguntas) {
    // Debe venir id_pregunta y respuesta
    if ($idPregunta <= 0 || $respuesta === '') {
      header('Location: /cafetin/login/recuperacion/vista/solicitar.php?error=' . urlencode('Debe responder su pregunta de seguridad.'));
      exit;
    }
    // Validar respuesta contra la pregunta seleccionada
    $stmtResp = $conexion->prepare("SELECT respuesta FROM respuestas WHERE id_usuario = :id AND id_pregunta = :pid LIMIT 1");
    $stmtResp->execute([':id' => $id_usuario, ':pid' => $idPregunta]);
    $respRow = $stmtResp->fetch(PDO::FETCH_ASSOC);
    if (!$respRow || !password_verify($respuesta, $respRow['respuesta'])) {
      header('Location: /cafetin/login/recuperacion/vista/solicitar.php?error=' . urlencode('Respuesta de seguridad incorrecta.'));
      exit;
    }
  }
  $token = bin2hex(random_bytes(32));

  // Limpiar resets anteriores no usados del mismo usuario (opcional)
  $conexion->prepare("DELETE FROM password_reset WHERE id_usuario = :id AND usado = 0 AND expiracion < NOW()")->execute([':id' => $id_usuario]);

  // Insertar nuevo token con expiración a 30 minutos
  $stmtIns = $conexion->prepare("INSERT INTO password_reset (id_usuario, token, expiracion, usado) VALUES (:id, :token, DATE_ADD(NOW(), INTERVAL 30 MINUTE), 0)");
  $stmtIns->execute([':id' => $id_usuario, ':token' => $token]);

  // Redirigir directamente al formulario de restablecimiento con el token
  header('Location: /cafetin/login/recuperacion/vista/restablecer.php?token=' . urlencode($token));
  exit;

} catch (Exception $e) {
  $msg = 'Error del servidor: ' . $e->getMessage();
  header('Location: /cafetin/login/recuperacion/vista/solicitar.php?error=' . urlencode($msg));
  exit;
}