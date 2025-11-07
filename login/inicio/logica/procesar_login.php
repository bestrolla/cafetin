<?php
require_once '../../../acces/auth_check.php';
// Asegurar inicialización segura de sesión
initSessionIfNeeded();

require_once '../../../acces/security_headers.php';
require_once '../../../acces/csrf.php';
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF token
    if (!csrfVerifyFromPost('csrf_token')) {
        $response['message'] = 'Token CSRF inválido o ausente.';
        echo json_encode($response);
        exit;
    }
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
            // Regenerar ID de sesión al autenticarse para mitigar fijación de sesión
            session_regenerate_id(true);
            // Contraseña correcta, iniciar sesión
            // Variables de sesión para compatibilidad con el sistema existente
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['usuario'] = $usuario_post;
            $_SESSION['nombre_completo'] = $user['nombre'] . ' ' . $user['apellido'];
            $_SESSION['rol'] = $user['nombre_rol'];
            
            // Variables de sesión para el nuevo sistema de autenticación
            $_SESSION['usuario_id'] = $user['id_usuario'];
            $_SESSION['usuario_nombre'] = $usuario_post;
            $_SESSION['usuario_rol'] = $user['nombre_rol'];
            $_SESSION['ultimo_acceso'] = time();

            // Actualizar la cantidad_total en el inventario
            $sqlUpdateInventario = "UPDATE inventario SET cantidad_total = (caja_produc * cantidad_caja) + unidades_sueltas";
            $stmtUpdate = $conexion->prepare($sqlUpdateInventario);
            $stmtUpdate->execute();

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