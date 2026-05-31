<?php
require_once __DIR__ . '/../../../acces/auth_check.php';
require_once __DIR__ . '/../../../BBDD/BBDD.php';
$conexion = Conexion::getConnection();

if (!esAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Acceso denegado. Solo administradores pueden procesar abonos generales.'
    ]);
    exit;
}

header('Content-Type: application/json');

try {
    $id_cliente = $_POST['id_cliente'] ?? null;
    $monto_abono = $_POST['monto_abono'] ?? null;
    $metodo_pago = isset($_POST['metodo_pago']) ? trim($_POST['metodo_pago']) : null;
    $observaciones = $_POST['observaciones'] ?? '';

    if (!$id_cliente || !$monto_abono) {
        throw new Exception('Faltan datos requeridos');
    }
    if ($monto_abono <= 0) {
        throw new Exception('El monto del abono debe ser mayor a 0');
    }
    $metodos_permitidos = ['efectivo','transferencia','tarjeta'];
    if (!$metodo_pago || !in_array($metodo_pago, $metodos_permitidos, true)) {
        throw new Exception('Debe seleccionar un método de pago válido');
    }

    $conexion->beginTransaction();

    // Obtener créditos pendientes/parciales del cliente con su saldo
    $sqlCreditos = "
        SELECT c.id_credito, c.total, DATE(c.fecha_cre) AS fecha, COALESCE(SUM(a.monto), 0) AS total_abonado
        FROM credito c
        LEFT JOIN abonos a ON a.id_credito = c.id_credito
        WHERE c.id_cliente = :id_cliente
          AND c.estado IN ('pendiente','parcial')
        GROUP BY c.id_credito
        ORDER BY c.fecha_cre ASC, c.id_credito ASC
    ";
    $stmt = $conexion->prepare($sqlCreditos);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt->execute();
    $creditos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$creditos) {
        throw new Exception('No hay créditos pendientes para este cliente');
    }

    $saldo_total = 0;
    foreach ($creditos as $c) {
        $saldo_total += ((float)$c['total'] - (float)$c['total_abonado']);
    }

    if ($monto_abono > $saldo_total) {
        throw new Exception('El monto del abono excede el saldo pendiente total');
    }

    $restante = $monto_abono;
    foreach ($creditos as $c) {
        if ($restante <= 0) break;
        $saldo_credito = (float)$c['total'] - (float)$c['total_abonado'];
        if ($saldo_credito <= 0) continue;
        $aplicar = min($restante, $saldo_credito);

        // Insertar abono para este crédito
        $sqlAbono = "INSERT INTO abonos (id_credito, monto, metodo_pago, observaciones) VALUES (?, ?, ?, ?)";
        $stmtAb = $conexion->prepare($sqlAbono);
        $stmtAb->execute([$c['id_credito'], $aplicar, $metodo_pago, $observaciones]);

        // Si se completó el crédito, marcar como pagado
        if ($aplicar >= $saldo_credito) {
            $sqlUpdate = "UPDATE credito SET estado = 'pagado' WHERE id_credito = ?";
            $stmtUp = $conexion->prepare($sqlUpdate);
            $stmtUp->execute([$c['id_credito']]);
        } else {
            // Asegurar estado parcial al menos
            $sqlUpdate = "UPDATE credito SET estado = 'parcial' WHERE id_credito = ? AND estado = 'pendiente'";
            $stmtUp = $conexion->prepare($sqlUpdate);
            $stmtUp->execute([$c['id_credito']]);
        }

        $restante -= $aplicar;
    }

    $conexion->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Abono general procesado exitosamente',
        'nuevo_saldo_total' => $saldo_total - $monto_abono
    ]);

} catch (Exception $e) {
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>