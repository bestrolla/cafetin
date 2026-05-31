<?php
require_once __DIR__ . '/../../../acces/auth_check.php';
initSessionIfNeeded();
require_once __DIR__ . '/../../../BBDD/BBDD.php';

header('Content-Type: application/json');

// Verificar que el usuario sea cajero
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'cajero') {
    echo json_encode([
        'success' => false,
        'message' => 'Acceso denegado'
    ]);
    exit();
}

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    // Obtener configuraciones de empresa
    $stmt = $pdo->prepare("
        SELECT clave, valor 
        FROM configuraciones 
        WHERE clave IN ('nombre_empresa', 'direccion_empresa', 'telefono_empresa', 'email_empresa') 
        AND activo = 1
    ");
    $stmt->execute();
    $configuraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir a array asociativo
    $config = [];
    foreach ($configuraciones as $conf) {
        $config[$conf['clave']] = $conf['valor'];
    }
    
    echo json_encode([
        'success' => true,
        'configuracion' => $config
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener configuración: ' . $e->getMessage()
    ]);
}
?>