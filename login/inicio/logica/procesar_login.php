<?php
session_start();

require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_post = $_POST['usuario'] ?? null;
    $contrasena_post = $_POST['contrasena'] ?? null;

    if (!$usuario_post || !$contrasena_post) {
        $response['message'] = 'Usuario y contraseña son obligatorios.';
        echo json_encode($response);
        exit;
    }

    try {
        $sql = "SELECT u.id_usuario, u.contrasena, p.nombre, p.apellido, r.nombre_rol 
                FROM usuario u
                JOIN persona p ON u.id_persona = p.id_persona
                JOIN rol r ON u.id_rol = r.id_rol
                WHERE u.usuario = :usuario LIMIT 1";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([':usuario' => $usuario_post]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($contrasena_post, $user['contrasena'])) {
            // Contraseña correcta, iniciar sesión
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['usuario'] = $usuario_post;
            $_SESSION['nombre_completo'] = $user['nombre'] . ' ' . $user['apellido'];
            $_SESSION['rol'] = $user['nombre_rol'];

            $response['success'] = true;
            $response['message'] = 'Inicio de sesión exitoso.';
            $response['role'] = $user['nombre_rol'];

        } else {
            // Usuario no encontrado o contraseña incorrecta
            $response['message'] = 'Usuario o contraseña incorrectos.';
        }

    } catch (PDOException $e) {
        $response['message'] = 'Error de base de datos: ' . $e->getMessage();
    }

} else {
    $response['message'] = 'Método de solicitud no válido.';
}

echo json_encode($response);
?>