<?php
// Incluir sistema de control de acceso
require_once __DIR__ . '/../../../acces/auth_check.php';

// Verificar que el usuario sea cajero
if (!esCajero()) {
    echo json_encode([
        'success' => false,
        'message' => 'Acceso denegado. Solo cajeros pueden procesar abonos.'
    ]);
    exit;
}

header('Content-Type: application/json');

try {
    // Verificar que se recibieron los datos necesarios
    $id_credito = $_POST['id_credito'] ?? null;
    $monto_abono = $_POST['monto_abono'] ?? null;
    $metodo_pago = isset($_POST['metodo_pago']) ? trim($_POST['metodo_pago']) : null;
    $observaciones = $_POST['observaciones'] ?? '';
    
    if (!$id_credito || !$monto_abono) {
        throw new Exception('Faltan datos requeridos');
    }
    
    if ($monto_abono <= 0) {
        throw new Exception('El monto del abono debe ser mayor a 0');
    }
    $metodos_permitidos = ['efectivo','transferencia','tarjeta'];
    if (!$metodo_pago || !in_array($metodo_pago, $metodos_permitidos, true)) {
        throw new Exception('Debe seleccionar un método de pago válido');
    }

    require_once __DIR__ . '/../../../BBDD/BBDD.php';
    $pdo = Conexion::getConnection();
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Verificar que el crédito existe y obtener información
    $sqlCredito = "SELECT c.*, COALESCE(SUM(a.monto), 0) as total_abonado
                   FROM credito c
                   LEFT JOIN abonos a ON c.id_credito = a.id_credito
                   WHERE c.id_credito = ?
                   GROUP BY c.id_credito";
    
    $stmtCredito = $pdo->prepare($sqlCredito);
    $stmtCredito->execute([$id_credito]);
    $credito = $stmtCredito->fetch(PDO::FETCH_ASSOC);
    
    if (!$credito) {
        throw new Exception('Crédito no encontrado');
    }
    
    $saldo_actual = $credito['total'] - $credito['total_abonado'];
    
    if ($monto_abono > $saldo_actual) {
        throw new Exception('El monto del abono excede el saldo pendiente');
    }
    
    // Insertar el abono
    $sqlAbono = "INSERT INTO abonos (id_credito, monto, metodo_pago, observaciones) 
                 VALUES (?, ?, ?, ?)";
    
    $stmtAbono = $pdo->prepare($sqlAbono);
    $stmtAbono->execute([$id_credito, $monto_abono, $metodo_pago, $observaciones]);
    
    // Verificar si la cuenta queda completamente pagada
    $nuevo_saldo = $saldo_actual - $monto_abono;
    
    if ($nuevo_saldo <= 0) {
        // Actualizar estado del crédito a pagado
        $sqlUpdate = "UPDATE credito SET estado = 'pagado' WHERE id_credito = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$id_credito]);
    }
    
    // Confirmar transacción
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Abono procesado exitosamente',
        'nuevo_saldo' => $nuevo_saldo,
        'estado' => $nuevo_saldo <= 0 ? 'pagado' : 'pendiente'
    ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>