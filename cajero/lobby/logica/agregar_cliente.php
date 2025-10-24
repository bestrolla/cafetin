<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $cedula = $data['cedula'] ?? '';
    $nombre = $data['nombre'] ?? '';
    $apellido = $data['apellido'] ?? '';
    $telefono = $data['telefono'] ?? '';
    $alias = $data['alias'] ?? '';
    
    try {
        // Verificar si el cliente ya existe por cédula
        $sqlCheck = "SELECT id_persona FROM persona WHERE cedula = ?";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->execute([$cedula]);
        $personaExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($personaExistente) {
            echo json_encode([
                'success' => false,
                'message' => 'Ya existe un cliente con esta cédula'
            ]);
            exit;
        }
        
        // Insertar en tabla persona
        $sqlPersona = "INSERT INTO persona (cedula, nombre, apellido, telefono) VALUES (?, ?, ?, ?)";
        $stmtPersona = $conexion->prepare($sqlPersona);
        $stmtPersona->execute([$cedula, $nombre, $apellido, $telefono]);
        $id_persona = $conexion->lastInsertId();
        
        // Insertar en tabla usuario (cliente básico)
        $sqlUsuario = "INSERT INTO usuario (id_persona, usuario, contrasena, id_rol) VALUES (?, ?, ?, ?)";
        $stmtUsuario = $conexion->prepare($sqlUsuario);
        $usuario = strtolower($nombre . $apellido);
        $contrasena = password_hash($cedula, PASSWORD_DEFAULT);
        
        // Verificar si existe el rol de cliente, si no, crearlo
        $sqlCheckRol = "SELECT id_rol FROM rol WHERE nombre_rol = 'cliente'";
        $stmtCheckRol = $conexion->prepare($sqlCheckRol);
        $stmtCheckRol->execute();
        $rolExistente = $stmtCheckRol->fetch(PDO::FETCH_ASSOC);
        
        if (!$rolExistente) {
            // Crear rol de cliente si no existe
            $sqlCrearRol = "INSERT INTO rol (nombre_rol) VALUES ('cliente')";
            $stmtCrearRol = $conexion->prepare($sqlCrearRol);
            $stmtCrearRol->execute();
            $id_rol = $conexion->lastInsertId();
        } else {
            $id_rol = $rolExistente['id_rol'];
        }
        
        $stmtUsuario->execute([$id_persona, $usuario, $contrasena, $id_rol]);
        $id_usuario = $conexion->lastInsertId();
        
        // Insertar en tabla cliente
        $sqlCliente = "INSERT INTO cliente (alias, descripcion, id_usuario) VALUES (?, ?, ?)";
        $stmtCliente = $conexion->prepare($sqlCliente);
        $descripcion = "Cliente: $nombre $apellido - Tel: $telefono";
        $stmtCliente->execute([$alias, $descripcion, $id_usuario]);
        $id_cliente = $conexion->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cliente registrado exitosamente',
            'id_cliente' => $id_cliente,
            'cliente' => [
                'id_cliente' => $id_cliente,
                'cedula' => $cedula,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'telefono' => $telefono,
                'alias' => $alias
            ]
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al registrar cliente: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>