<?php
session_start();
require_once '../../../BBDD/BBDD.php';

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

if (empty($input)) {
    echo json_encode([
        'success' => false,
        'message' => 'No se recibieron configuraciones para guardar'
    ]);
    exit();
}

$usuario = $_SESSION['usuario'];

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Preparar statements para actualizar o insertar configuraciones
    $stmtUpdate = $pdo->prepare("
        UPDATE configuraciones 
        SET valor = ?, fecha_actualizacion = NOW(), usuario_actualizacion = ? 
        WHERE clave = ?
    ");
    $stmtInsert = $pdo->prepare("
        INSERT INTO configuraciones (clave, valor, descripcion, tipo, fecha_creacion, usuario_actualizacion, activo)
        VALUES (?, ?, '', 'texto', NOW(), ?, 1)
    ");
    
    $configuraciones_actualizadas = 0;
    
    // Actualizar cada configuración
    foreach ($input as $clave => $valor) {
        // Validar que la clave existe
        $stmt_check = $pdo->prepare("SELECT id FROM configuraciones WHERE clave = ? AND activo = 1");
        $stmt_check->execute([$clave]);
        
        if ($stmt_check->fetchColumn()) {
            $stmtUpdate->execute([$valor, $usuario, $clave]);
            $configuraciones_actualizadas++;
        } else {
            // Insertar nueva clave si no existe
            $stmtInsert->execute([$clave, $valor, $usuario]);
            $configuraciones_actualizadas++;
        }
    }
    
    // Confirmar transacción
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Se actualizaron $configuraciones_actualizadas configuraciones correctamente",
        'configuraciones_actualizadas' => $configuraciones_actualizadas
    ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar configuraciones: ' . $e->getMessage()
    ]);
}
?>