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

$idUsuario = (int) ($_SESSION['id_usuario'] ?? 0);
if ($idUsuario < 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Sesión inválida',
    ]);
    exit();
}

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    cafetin_persona_ensure_email_column($pdo);

    $stmt = $pdo->prepare(
        'SELECT u.usuario AS nombre,
                COALESCE(p.email, \'\') AS email,
                COALESCE(p.telefono, \'\') AS telefono
         FROM usuario u
         INNER JOIN persona p ON u.id_persona = p.id_persona
         INNER JOIN rol r ON u.id_rol = r.id_rol
         WHERE u.id_usuario = ? AND LOWER(r.nombre_rol) = \'cajero\''
    );
    $stmt->execute([$idUsuario]);
    $datosUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($datosUsuario) {
        $ultima = null;
        if (!empty($_SESSION['ultimo_acceso'])) {
            $ultima = date('c', (int) $_SESSION['ultimo_acceso']);
        }
        $datosUsuario['fecha_creacion'] = null;
        $datosUsuario['ultima_sesion'] = $ultima;

        echo json_encode([
            'success' => true,
            'usuario' => $datosUsuario,
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado',
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener datos del usuario: ' . $e->getMessage(),
    ]);
}
