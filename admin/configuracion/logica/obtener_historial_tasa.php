<?php
require_once __DIR__ . '/../../../acces/auth_check.php';
require_once __DIR__ . '/../../../BBDD/BBDD.php';

header('Content-Type: application/json');

// Verificar que el usuario sea admin
if (!esAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Acceso denegado'
    ]);
    exit();
}

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    // Obtener historial de cambios de tasa (últimos 50 registros)
    $stmt = $pdo->prepare("
        SELECT 
            tasa_anterior,
            tasa_nueva,
            usuario,
            fecha_cambio,
            motivo
        FROM historial_tasa 
        ORDER BY fecha_cambio DESC 
        LIMIT 50
    ");
    $stmt->execute();
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'historial' => $historial
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener historial: ' . $e->getMessage()
    ]);
}
?>