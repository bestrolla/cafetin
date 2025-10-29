<?php
require_once '../../../acces/auth_check.php';
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

// Verificar que el usuario sea cajero
if (!esCajero()) {
    echo json_encode([
        'success' => false,
        'message' => 'Acceso denegado'
    ]);
    exit();
}

$usuario = $_SESSION['usuario'];

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    // Obtener datos del usuario
    $stmt = $pdo->prepare("
        SELECT 
            nombre,
            email,
            telefono,
            fecha_creacion,
            ultima_sesion
        FROM usuarios 
        WHERE nombre = ? AND rol = 'cajero'
    ");
    $stmt->execute([$usuario]);
    $datosUsuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($datosUsuario) {
        echo json_encode([
            'success' => true,
            'usuario' => $datosUsuario
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener datos del usuario: ' . $e->getMessage()
    ]);
}
?>