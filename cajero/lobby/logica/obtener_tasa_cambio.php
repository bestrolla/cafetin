<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

try {
    // Por ahora usamos una tasa fija, puedes modificar para obtener de una tabla
    $tasa_cambio = 36.00;
    
    echo json_encode([
        'success' => true,
        'tasa_cambio' => $tasa_cambio
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener tasa de cambio: ' . $e->getMessage()
    ]);
}
?>