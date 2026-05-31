<?php
require_once __DIR__ . '/../../../acces/auth_check.php';
initSessionIfNeeded();
require_once __DIR__ . '/../../../BBDD/BBDD.php';

header('Content-Type: application/json');

// Verificar que el usuario sea admin
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Acceso denegado'
    ]);
    exit();
}

// Obtener datos JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['tasa_nueva']) || !is_numeric($input['tasa_nueva'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Tasa nueva requerida y debe ser numérica'
    ]);
    exit();
}

$tasa_nueva = floatval($input['tasa_nueva']);
$motivo = isset($input['motivo']) ? trim($input['motivo']) : '';
$usuario = $_SESSION['usuario'];

if ($tasa_nueva <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'La tasa debe ser mayor a 0'
    ]);
    exit();
}

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Obtener tasa actual
    $stmt = $pdo->prepare("SELECT valor FROM configuraciones WHERE clave = 'tasa_dolar' AND activo = 1");
    $stmt->execute();
    $tasa_actual = $stmt->fetchColumn();
    
    // Actualizar la tasa en configuraciones
    $stmt = $pdo->prepare("
        UPDATE configuraciones 
        SET valor = ?, fecha_actualizacion = NOW(), usuario_actualizacion = ? 
        WHERE clave = 'tasa_dolar'
    ");
    $stmt->execute([$tasa_nueva, $usuario]);
    
    // Registrar en historial
    $stmt = $pdo->prepare("
        INSERT INTO historial_tasa (tasa_anterior, tasa_nueva, usuario, motivo) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$tasa_actual, $tasa_nueva, $usuario, $motivo]);
    
    // Confirmar transacción
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Tasa actualizada correctamente',
        'tasa_anterior' => $tasa_actual,
        'tasa_nueva' => $tasa_nueva
    ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar tasa: ' . $e->getMessage()
    ]);
}
?>