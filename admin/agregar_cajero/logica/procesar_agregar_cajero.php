<?php
require_once __DIR__ . '/../../../BBDD/BBDD.php';
$conexion = Conexion::getConnection();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recibir y validar datos
    $nombre = isset($_POST['nombre']) ? normalizarTextoNombre($_POST['nombre']) : null;
    $apellido = isset($_POST['apellido']) ? normalizarTextoNombre($_POST['apellido']) : null;
    $telefono = $_POST['telefono'] ?? null;
    $usuario = $_POST['usuario'] ?? null;
    $contrasena = $_POST['contrasena'] ?? null;
    $contrasena_confirmar = $_POST['contrasena_confirmar'] ?? null;

    if (!$nombre || !$apellido || !$usuario || !$contrasena) {
        $response['message'] = 'Nombre, apellido, usuario y contraseña son obligatorios.';
        echo json_encode($response);
        exit;
    }

    if (!$contrasena_confirmar) {
        $response['message'] = 'Confirmar contraseña es obligatoria.';
        echo json_encode($response);
        exit;
    }

    if ($contrasena !== $contrasena_confirmar) {
        $response['message'] = 'Las contraseñas no coinciden.';
        echo json_encode($response);
        exit;
    }

    try {
        // Iniciar transacción
        $conexion->beginTransaction();

        // 2. Obtener el id_rol para 'cajero'
        $sqlRol = "SELECT id_rol FROM rol WHERE nombre_rol = 'cajero' LIMIT 1";
        $stmtRol = $conexion->prepare($sqlRol);
        $stmtRol->execute();
        $rol = $stmtRol->fetch(PDO::FETCH_ASSOC);

        if (!$rol) {
            // Si no existe el rol, podemos crearlo o lanzar un error.
            // Por ahora, lanzamos un error.
            throw new Exception('El rol \'cajero\' no existe en la base de datos.');
        }
        $id_rol_cajero = $rol['id_rol'];

        // 3. Insertar en la tabla `persona`
        $sqlPersona = "INSERT INTO persona (nombre, apellido, telefono) VALUES (:nombre, :apellido, :telefono)";
        $stmtPersona = $conexion->prepare($sqlPersona);
        $stmtPersona->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':telefono' => $telefono
        ]);
        $id_persona = $conexion->lastInsertId();

        // 4. Cifrar contraseña e insertar en `usuario`
        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $sqlUsuario = "INSERT INTO usuario (id_persona, usuario, contrasena, id_rol) VALUES (:id_persona, :usuario, :contrasena, :id_rol)";
        $stmtUsuario = $conexion->prepare($sqlUsuario);
        $stmtUsuario->execute([
            ':id_persona' => $id_persona,
            ':usuario' => $usuario,
            ':contrasena' => $contrasena_hash,
            ':id_rol' => $id_rol_cajero
        ]);
        $id_usuario = $conexion->lastInsertId();

        // 5. Insertar en la tabla `cajero`
        $sqlCajero = "INSERT INTO cajero (id_usuario, fecha_ini) VALUES (:id_usuario, CURDATE())";
        $stmtCajero = $conexion->prepare($sqlCajero);
        $stmtCajero->execute([':id_usuario' => $id_usuario]);

        // Si todo fue bien, confirmar la transacción
        $conexion->commit();

        $response['success'] = true;
        $response['message'] = 'Cajero registrado con éxito.';

    } catch (PDOException $e) {
        // Si algo falla, revertir la transacción
        $conexion->rollBack();
        if ($e->errorInfo[1] == 1062) { // Error de entrada duplicada (ej. usuario ya existe)
            $response['message'] = 'El nombre de usuario ya existe. Por favor, elija otro.';
        } else {
            $response['message'] = 'Error de base de datos: ' . $e->getMessage();
        }
    } catch (Exception $e) {
        $conexion->rollBack();
        $response['message'] = 'Error en la aplicación: ' . $e->getMessage();
    }

} else {
    $response['message'] = 'Método de solicitud no válido.';
}

echo json_encode($response);
?>
