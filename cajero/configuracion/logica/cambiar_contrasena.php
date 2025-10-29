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

// Obtener datos JSON
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input)) {
    echo json_encode([
        'success' => false,
        'message' => 'No se recibieron datos'
    ]);
    exit();
}

$usuario = $_SESSION['usuario'];
$passwordActual = $input['password_actual'] ?? '';
$passwordNueva = $input['password_nueva'] ?? '';

// Validar que se proporcionaron las contraseñas
if (empty($passwordActual) || empty($passwordNueva)) {
    echo json_encode([
        'success' => false,
        'message' => 'Debe proporcionar la contraseña actual y la nueva'
    ]);
    exit();
}

// Validar longitud mínima
if (strlen($passwordNueva) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'La nueva contraseña debe tener al menos 6 caracteres'
    ]);
    exit();
}

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    // Verificar contraseña actual
    $stmt = $pdo->prepare("
        SELECT contrasena 
        FROM usuarios 
        WHERE nombre = ? AND rol = 'cajero'
    ");
    $stmt->execute([$usuario]);
    $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuarioData) {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ]);
        exit();
    }
    
    // Verificar contraseña actual
    if (!password_verify($passwordActual, $usuarioData['contrasena'])) {
        echo json_encode([
            'success' => false,
            'message' => 'La contraseña actual es incorrecta'
        ]);
        exit();
    }
    
    // Encriptar nueva contraseña
    $passwordNuevaHash = password_hash($passwordNueva, PASSWORD_DEFAULT);
    
    // Actualizar contraseña
    $stmt = $pdo->prepare("
        UPDATE usuarios 
        SET contrasena = ?, fecha_actualizacion = NOW()
        WHERE nombre = ? AND rol = 'cajero'
    ");
    $stmt->execute([$passwordNuevaHash, $usuario]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Contraseña cambiada correctamente'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cambiar contraseña: ' . $e->getMessage()
    ]);
}
?>