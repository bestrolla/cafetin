<?php
require_once __DIR__ . '/../../../BBDD/BBDD.php';
$conexion = Conexion::getConnection();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $id_cliente = $data['id_cliente'] ?? null;
    $cedula = trim($data['cedula'] ?? '');
    $nombre = normalizarTextoNombre($data['nombre'] ?? '');
    $apellido = normalizarTextoNombre($data['apellido'] ?? '');
    $telefono = trim($data['telefono'] ?? '');
    $alias = normalizarTextoNombre($data['alias'] ?? '');

    try {
        // Asegurar rol cliente
        $sqlCheckRol = "SELECT id_rol FROM rol WHERE nombre_rol = 'cliente'";
        $stmtCheckRol = $conexion->prepare($sqlCheckRol);
        $stmtCheckRol->execute();
        $rolExistente = $stmtCheckRol->fetch(PDO::FETCH_ASSOC);
        if (!$rolExistente) {
            $sqlCrearRol = "INSERT INTO rol (nombre_rol) VALUES ('cliente')";
            $stmtCrearRol = $conexion->prepare($sqlCrearRol);
            $stmtCrearRol->execute();
            $id_rol = (int)$conexion->lastInsertId();
        } else {
            $id_rol = (int)$rolExistente['id_rol'];
        }

        // Descripción cliente
        $descripcion = "Cliente: $nombre $apellido - Tel: $telefono";

        // Si viene id_cliente, actualizar registro existente
        if ($id_cliente) {
            // Obtener persona asociada al cliente
            $sqlGetPersona = "
                SELECT p.id_persona, p.cedula
                FROM cliente c
                JOIN usuario u ON c.id_usuario = u.id_usuario
                JOIN persona p ON u.id_persona = p.id_persona
                WHERE c.id_cliente = ?
                LIMIT 1";
            $stmtGetPersona = $conexion->prepare($sqlGetPersona);
            $stmtGetPersona->execute([$id_cliente]);
            $rowPersona = $stmtGetPersona->fetch(PDO::FETCH_ASSOC);
            if (!$rowPersona) {
                echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
                exit;
            }
            $id_persona = (int)$rowPersona['id_persona'];
            $cedulaActual = (int)$rowPersona['cedula'];

            // Regla: cédula solo se puede actualizar una vez (de 0 a valor real)
            $nuevaCedula = strlen($cedula) ? (int)$cedula : null;
            if ($nuevaCedula !== null) {
                if ($cedulaActual !== 0 && $cedulaActual !== $nuevaCedula) {
                    echo json_encode(['success' => false, 'message' => 'La cédula ya está asignada y no puede cambiarse']);
                    exit;
                }
                if ($cedulaActual === 0) {
                    // Verificar que la nueva cédula no existe en otra persona
                    $sqlCheckCed = "SELECT id_persona FROM persona WHERE cedula = ? LIMIT 1";
                    $stmtCheckCed = $conexion->prepare($sqlCheckCed);
                    $stmtCheckCed->execute([$nuevaCedula]);
                    if ($stmtCheckCed->fetch(PDO::FETCH_ASSOC)) {
                        echo json_encode(['success' => false, 'message' => 'Ya existe otra persona con esa cédula']);
                        exit;
                    }
                    // Actualizar cédula
                    $conexion->prepare("UPDATE persona SET cedula = ? WHERE id_persona = ?")
                             ->execute([$nuevaCedula, $id_persona]);
                }
            }

            // Actualizar datos de persona
            $conexion->prepare("UPDATE persona SET nombre = ?, apellido = ?, telefono = ? WHERE id_persona = ?")
                     ->execute([$nombre, $apellido, $telefono, $id_persona]);

            // Actualizar alias y descripción del cliente
            $conexion->prepare("UPDATE cliente SET alias = ?, descripcion = ? WHERE id_cliente = ?")
                     ->execute([$alias, $descripcion, $id_cliente]);

            echo json_encode(['success' => true, 'message' => 'Cliente actualizado exitosamente', 'id_cliente' => (int)$id_cliente]);
            exit;
        }

        // Crear nuevo cliente
        $cedulaInt = strlen($cedula) ? (int)$cedula : 0; // permitir cédula 0

        // Verificar duplicado solo si cédula > 0
        if ($cedulaInt > 0) {
            $stmtCheck = $conexion->prepare("SELECT id_persona FROM persona WHERE cedula = ? LIMIT 1");
            $stmtCheck->execute([$cedulaInt]);
            if ($stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un cliente con esta cédula']);
                exit;
            }
        }

        // Insertar persona
        $stmtPersona = $conexion->prepare("INSERT INTO persona (cedula, nombre, apellido, telefono) VALUES (?, ?, ?, ?)");
        $stmtPersona->execute([$cedulaInt, $nombre, $apellido, $telefono]);
        $id_persona = (int)$conexion->lastInsertId();

        // Insertar usuario
        $stmtUsuario = $conexion->prepare("INSERT INTO usuario (id_persona, usuario, contrasena, id_rol) VALUES (?, ?, ?, ?)");
        $usuario = strtolower(preg_replace('/\s+/', '', $nombre . $apellido));
        $contrasena = password_hash($cedulaInt, PASSWORD_DEFAULT);
        $stmtUsuario->execute([$id_persona, $usuario, $contrasena, $id_rol]);
        $id_usuario = (int)$conexion->lastInsertId();

        // Insertar cliente
        $stmtCliente = $conexion->prepare("INSERT INTO cliente (alias, descripcion, id_usuario) VALUES (?, ?, ?)");
        $stmtCliente->execute([$alias, $descripcion, $id_usuario]);
        $id_cliente = (int)$conexion->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Cliente registrado exitosamente',
            'id_cliente' => $id_cliente,
            'cliente' => [
                'id_cliente' => $id_cliente,
                'cedula' => $cedulaInt,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'telefono' => $telefono,
                'alias' => $alias
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al registrar/actualizar cliente: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>