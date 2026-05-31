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
    
    // Obtener todas las configuraciones activas
    $stmt = $pdo->prepare("SELECT clave, valor FROM configuraciones WHERE activo = 1");
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir a array asociativo
    $configuraciones = [];
    foreach ($resultados as $config) {
        $configuraciones[$config['clave']] = $config['valor'];
    }
    
    echo json_encode([
        'success' => true,
        'configuraciones' => $configuraciones
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener configuraciones: ' . $e->getMessage()
    ]);
}
?>