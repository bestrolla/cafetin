<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    // Obtener la tasa actual del dólar desde la configuración
    $stmt = $pdo->prepare("
        SELECT valor, fecha_actualizacion 
        FROM configuraciones 
        WHERE clave = 'tasa_dolar' AND activo = 1
    ");
    $stmt->execute();
    $tasa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tasa) {
        echo json_encode([
            'success' => true,
            'tasa_cambio' => floatval($tasa['valor']),
            'fecha_actualizacion' => $tasa['fecha_actualizacion']
        ]);
    } else {
        // Si no hay tasa configurada, usar valor por defecto
        echo json_encode([
            'success' => true,
            'tasa_cambio' => 36.00,
            'fecha_actualizacion' => date('Y-m-d H:i:s')
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener tasa de cambio: ' . $e->getMessage()
    ]);
}
?>