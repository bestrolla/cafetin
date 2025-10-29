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
        'message' => 'No se recibieron preferencias para guardar'
    ]);
    exit();
}

$usuario = $_SESSION['usuario'];
$monedaPreferida = $input['moneda_preferida'] ?? 'BS';
$sonidosNotificacion = $input['sonidos_notificacion'] ?? '0';
$confirmacionVentas = $input['confirmacion_ventas'] ?? '1';
$autoImprimir = $input['auto_imprimir'] ?? '0';

// Validar moneda preferida
$monedasValidas = ['BS', 'USD', 'AMBAS'];
if (!in_array($monedaPreferida, $monedasValidas)) {
    echo json_encode([
        'success' => false,
        'message' => 'Moneda preferida no válida'
    ]);
    exit();
}

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    // Verificar si ya existen preferencias para el usuario
    $stmt = $pdo->prepare("SELECT id FROM preferencias_usuario WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $existe = $stmt->fetchColumn();
    
    if ($existe) {
        // Actualizar preferencias existentes
        $stmt = $pdo->prepare("
            UPDATE preferencias_usuario 
            SET 
                moneda_preferida = ?,
                sonidos_notificacion = ?,
                confirmacion_ventas = ?,
                auto_imprimir = ?,
                fecha_actualizacion = NOW()
            WHERE usuario = ?
        ");
        $stmt->execute([
            $monedaPreferida,
            $sonidosNotificacion,
            $confirmacionVentas,
            $autoImprimir,
            $usuario
        ]);
    } else {
        // Crear nuevas preferencias
        $stmt = $pdo->prepare("
            INSERT INTO preferencias_usuario 
            (usuario, moneda_preferida, sonidos_notificacion, confirmacion_ventas, auto_imprimir, fecha_creacion) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $usuario,
            $monedaPreferida,
            $sonidosNotificacion,
            $confirmacionVentas,
            $autoImprimir
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Preferencias guardadas correctamente'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar preferencias: ' . $e->getMessage()
    ]);
}
?>