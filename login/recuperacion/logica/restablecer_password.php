<?php
require_once '../../../acces/auth_check.php';
initSessionIfNeeded();
require_once '../../../acces/security_headers.php';
require_once '../../../acces/csrf.php';
require_once '../../../BBDD/BBDD.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . appUrl('/login/recuperacion/vista/solicitar.php?error=' . urlencode('Método no permitido')));
  exit;
}

// Verificar CSRF
if (!csrfVerifyFromPost('csrf_token')) {
  header('Location: ' . appUrl('/login/recuperacion/vista/restablecer.php?error=' . urlencode('Token CSRF inválido')));
  exit;
}

$token = isset($_POST['token']) ? trim($_POST['token']) : '';
$nueva = isset($_POST['nueva_contrasena']) ? (string)$_POST['nueva_contrasena'] : '';
$confirmar = isset($_POST['confirmar_contrasena']) ? (string)$_POST['confirmar_contrasena'] : '';

if ($token === '' || $nueva === '' || $confirmar === '') {
  header('Location: ' . appUrl('/login/recuperacion/vista/restablecer.php?error=' . urlencode('Complete todos los campos del formulario')));
  exit;
}

if ($nueva !== $confirmar) {
  header('Location: ' . appUrl('/login/recuperacion/vista/restablecer.php?error=' . urlencode('Las contraseñas no coinciden')));
  exit;
}

if (strlen($nueva) < 6 || strlen($nueva) > 12) {
  header('Location: ' . appUrl('/login/recuperacion/vista/restablecer.php?error=' . urlencode('La contraseña debe tener entre 6 y 12 caracteres')));
  exit;
}

try {
  // Verificar token válido y no vencido
  $stmt = $conexion->prepare("SELECT id_usuario, expiracion, usado FROM password_reset WHERE token = :token LIMIT 1");
  $stmt->execute([':token' => $token]);
  $reset = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$reset) {
    header('Location: ' . appUrl('/login/recuperacion/vista/restablecer.php?error=' . urlencode('Token inválido')));
    exit;
  }

  if ((int)$reset['usado'] === 1) {
    header('Location: ' . appUrl('/login/recuperacion/vista/restablecer.php?error=' . urlencode('Este enlace ya fue utilizado')));
    exit;
  }

  // Verificar expiración en DB
  $stmtExp = $conexion->prepare("SELECT CASE WHEN expiracion >= NOW() THEN 1 ELSE 0 END AS vigente FROM password_reset WHERE token = :token");
  $stmtExp->execute([':token' => $token]);
  $vigenteRow = $stmtExp->fetch(PDO::FETCH_ASSOC);
  if (!$vigenteRow || (int)$vigenteRow['vigente'] !== 1) {
    header('Location: ' . appUrl('/login/recuperacion/vista/restablecer.php?error=' . urlencode('El enlace ha expirado')));
    exit;
  }

  $id_usuario = (int)$reset['id_usuario'];
  $hash = password_hash($nueva, PASSWORD_DEFAULT);

  // Iniciar transacción para actualizar usuario y marcar token usado
  $conexion->beginTransaction();

  $stmtUpd = $conexion->prepare("UPDATE usuario SET contrasena = :hash WHERE id_usuario = :id");
  $stmtUpd->execute([':hash' => $hash, ':id' => $id_usuario]);

  $stmtTok = $conexion->prepare("UPDATE password_reset SET usado = 1 WHERE token = :token");
  $stmtTok->execute([':token' => $token]);

  $conexion->commit();

  // Redirigir al login con mensaje de éxito
  header('Location: ' . appUrl('/login/inicio/vista/inicio.php?mensaje=' . urlencode('Contraseña actualizada. Inicie sesión con sus nuevas credenciales.')));
  exit;

} catch (Exception $e) {
  if ($conexion->inTransaction()) {
    $conexion->rollBack();
  }
  $msg = 'Error del servidor: ' . $e->getMessage();
  header('Location: ' . appUrl('/login/recuperacion/vista/restablecer.php?error=' . urlencode($msg)));
  exit;
}