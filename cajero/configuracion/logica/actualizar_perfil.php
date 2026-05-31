<?php
require_once __DIR__ . '/../../../acces/auth_check.php';
require_once __DIR__ . '/../../../BBDD/BBDD.php';
require_once __DIR__ . '/persona_perfil_helper.php';

header('Content-Type: application/json');

if (!esCajero()) {
    echo json_encode([
        'success' => false,
        'message' => 'Acceso denegado',
    ]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input)) {
    echo json_encode([
        'success' => false,
        'message' => 'No se recibieron datos para actualizar',
    ]);
    exit();
}

$idUsuario = (int) ($_SESSION['id_usuario'] ?? 0);
if ($idUsuario < 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Sesión inválida',
    ]);
    exit();
}

$email = $input['email'] ?? '';
$telefono = $input['telefono'] ?? '';

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'El formato del email no es válido',
    ]);
    exit();
}

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    cafetin_persona_ensure_email_column($pdo);

    $stmt = $pdo->prepare(
        'SELECT p.id_persona
         FROM usuario u
         INNER JOIN persona p ON u.id_persona = p.id_persona
         INNER JOIN rol r ON u.id_rol = r.id_rol
         WHERE u.id_usuario = ? AND LOWER(r.nombre_rol) = \'cajero\''
    );
    $stmt->execute([$idUsuario]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || empty($row['id_persona'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado',
        ]);
        exit();
    }

    $idPersona = (int) $row['id_persona'];
    $emailVal = $email === '' ? null : $email;

    $upd = $pdo->prepare('UPDATE persona SET email = ?, telefono = ? WHERE id_persona = ?');
    $upd->execute([$emailVal, $telefono, $idPersona]);

    echo json_encode([
        'success' => true,
        'message' => 'Perfil actualizado correctamente',
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar perfil: ' . $e->getMessage(),
    ]);
}
