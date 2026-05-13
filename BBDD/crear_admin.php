<?php
require_once 'BBDD.php';

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    // Datos del administrador
    $usuario = 'admin';
    $contrasena = 'Admin123$';
    $nombre = 'Administrador';
    $apellido = 'Sistema';
    $cedula = '00000000';
    $telefono = '0000000000';
    
    // Verificar si el usuario ya existe
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE usuario = ? LIMIT 1");
    $stmt->execute([$usuario]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row && isset($row['id_usuario'])) {
        $id_usuario = (int)$row['id_usuario'];
        $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);
        $pdo->beginTransaction();
        $upd = $pdo->prepare("UPDATE usuario SET contrasena = ? WHERE id_usuario = ?");
        $upd->execute([$contrasenaHash, $id_usuario]);
        $checkAdmin = $pdo->prepare("SELECT 1 FROM admin WHERE id_usuario = ?");
        $checkAdmin->execute([$id_usuario]);
        if (!$checkAdmin->fetch()) {
            $insAdmin = $pdo->prepare("INSERT INTO admin (id_usuario) VALUES (?)");
            $insAdmin->execute([$id_usuario]);
        }
        $pdo->commit();
        echo "Contraseña del usuario administrador actualizada.\n";
        echo "Usuario: $usuario\n";
        echo "Nueva contraseña: $contrasena\n";
        exit();
    }
    
    // Obtener el ID del rol admin (ya existe con ID 3)
    $stmt = $pdo->prepare("SELECT id_rol FROM rol WHERE nombre_rol = 'admin'");
    $stmt->execute();
    $rolAdmin = $stmt->fetch();
    
    if (!$rolAdmin) {
        echo "❌ Error: No se encontró el rol 'admin' en la base de datos.\n";
        exit();
    }
    
    $id_rol = $rolAdmin['id_rol'];
    echo "✅ Rol 'admin' encontrado con ID: $id_rol\n";
    
    // Encriptar la contraseña
    $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // 1. Insertar en la tabla persona
    $stmt = $pdo->prepare("
        INSERT INTO persona (nombre, apellido, cedula, telefono) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$nombre, $apellido, $cedula, $telefono]);
    $id_persona = $pdo->lastInsertId();
    
    // 2. Insertar en la tabla usuario
    $stmt = $pdo->prepare("
        INSERT INTO usuario (id_persona, usuario, contrasena, id_rol) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$id_persona, $usuario, $contrasenaHash, $id_rol]);
    $id_usuario = $pdo->lastInsertId();
    
    // 3. Insertar en la tabla admin
    $stmt = $pdo->prepare("INSERT INTO admin (id_usuario) VALUES (?)");
    $stmt->execute([$id_usuario]);
    
    // Confirmar transacción
    $pdo->commit();
    
    echo "✅ Usuario administrador creado exitosamente.\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📋 CREDENCIALES DE ACCESO:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "👤 Usuario: $usuario\n";
    echo "🔐 Contraseña: $contrasena\n";
    echo "🎭 Rol: Administrador\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>