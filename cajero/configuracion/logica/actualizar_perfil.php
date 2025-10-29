<?php
session_start();
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

// Verificar que el usuario sea cajero
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cajero') {
    echo json_encode([
        'success' => false,
        'message' => 'Acceso denegado'
    ]);
    exit();
}

// Obtener datos JSON
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input)) {
    echo json_encode([
        'success' => false,
        'message' => 'No se recibieron datos para actualizar'
    ]);
    exit();
}

$usuario = $_SESSION['usuario'];
$email = $input['email'] ?? '';
$telefono = $input['telefono'] ?? '';

// Validar email si se proporciona
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'El formato del email no es válido'
    ]);
    exit();
}

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    // Actualizar datos del usuario
    $stmt = $pdo->prepare("
        UPDATE usuarios 
        SET email = ?, telefono = ?, fecha_actualizacion = NOW()
        WHERE nombre = ? AND rol = 'cajero'
    ");
    $stmt->execute([$email, $telefono, $usuario]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Perfil actualizado correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se realizaron cambios en el perfil'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar perfil: ' . $e->getMessage()
    ]);
}
?>