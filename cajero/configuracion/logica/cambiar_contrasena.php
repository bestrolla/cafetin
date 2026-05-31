<?php
require_once __DIR__ . '/../../../acces/auth_check.php';
require_once __DIR__ . '/../../../BBDD/BBDD.php';

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
        'message' => 'No se recibieron datos',
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

$passwordActual = $input['password_actual'] ?? '';
$passwordNueva = $input['password_nueva'] ?? '';

if ($passwordActual === '' || $passwordNueva === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Debe proporcionar la contraseña actual y la nueva',
    ]);
    exit();
}

if (strlen($passwordNueva) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'La nueva contraseña debe tener al menos 6 caracteres',
    ]);
    exit();
}

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();

    $stmt = $pdo->prepare(
        'SELECT u.contrasena
         FROM usuario u
         INNER JOIN rol r ON u.id_rol = r.id_rol
         WHERE u.id_usuario = ? AND LOWER(r.nombre_rol) = \'cajero\''
    );
    $stmt->execute([$idUsuario]);
    $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuarioData) {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado',
        ]);
        exit();
    }

    if (!password_verify($passwordActual, $usuarioData['contrasena'])) {
        echo json_encode([
            'success' => false,
            'message' => 'La contraseña actual es incorrecta',
        ]);
        exit();
    }

    $passwordNuevaHash = password_hash($passwordNueva, PASSWORD_DEFAULT);

    $upd = $pdo->prepare('UPDATE usuario SET contrasena = ? WHERE id_usuario = ?');
    $upd->execute([$passwordNuevaHash, $idUsuario]);

    echo json_encode([
        'success' => true,
        'message' => 'Contraseña cambiada correctamente',
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cambiar contraseña: ' . $e->getMessage(),
    ]);
}
