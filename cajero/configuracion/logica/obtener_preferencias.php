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

$usuario = $_SESSION['usuario'];

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    // Obtener preferencias del usuario
    $stmt = $pdo->prepare("
        SELECT 
            moneda_preferida,
            sonidos_notificacion,
            confirmacion_ventas,
            auto_imprimir
        FROM preferencias_usuario 
        WHERE usuario = ?
    ");
    $stmt->execute([$usuario]);
    $preferencias = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si no existen preferencias, crear valores por defecto
    if (!$preferencias) {
        $preferencias = [
            'moneda_preferida' => 'BS',
            'sonidos_notificacion' => '1',
            'confirmacion_ventas' => '1',
            'auto_imprimir' => '0'
        ];
        
        // Insertar preferencias por defecto
        $stmt = $pdo->prepare("
            INSERT INTO preferencias_usuario 
            (usuario, moneda_preferida, sonidos_notificacion, confirmacion_ventas, auto_imprimir, fecha_creacion) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $usuario,
            $preferencias['moneda_preferida'],
            $preferencias['sonidos_notificacion'],
            $preferencias['confirmacion_ventas'],
            $preferencias['auto_imprimir']
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'preferencias' => $preferencias
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener preferencias: ' . $e->getMessage()
    ]);
}
?>