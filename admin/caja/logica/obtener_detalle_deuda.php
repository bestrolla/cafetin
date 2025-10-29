<?php
header('Content-Type: application/json');

require_once '../../../BBDD/BBDD.php';

try {
    // Obtener parámetros
    $id_cliente = $_GET['id_cliente'] ?? null;
    $fecha_factura = $_GET['fecha_factura'] ?? null;

    if (!$id_cliente || !$fecha_factura) {
        echo json_encode(['error' => 'Parámetros requeridos: id_cliente y fecha_factura']);
        exit;
    }

    // Consulta para obtener los productos de una factura específica (igual que el módulo de cuentas)
    $sql = "
        SELECT 
            c.id_credito,
            i.nombre_produc as producto,
            c.cantidad,
            c.total as subtotal,
            DATE(c.fecha_cre) as fecha_compra,
            TIME(c.fecha_cre) as hora_compra,
            c.fecha_cre,
            CONCAT(p.nombre, ' ', p.apellido) as cliente
        FROM credito c
        INNER JOIN cliente cl ON c.id_cliente = cl.id_cliente
        INNER JOIN usuario u ON cl.id_usuario = u.id_usuario
        INNER JOIN persona p ON u.id_persona = p.id_persona
        INNER JOIN inventario i ON c.id_producto = i.id_producto
        WHERE c.id_cliente = :id_cliente 
        AND DATE(c.fecha_cre) = :fecha_factura
        AND c.estado IN ('pendiente', 'parcial')
        ORDER BY DATE(c.fecha_cre) ASC, TIME(c.fecha_cre) ASC
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_cliente', $id_cliente);
    $stmt->bindParam(':fecha_factura', $fecha_factura);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener información del cliente
    $sqlCliente = "
        SELECT CONCAT(p.nombre, ' ', p.apellido) as cliente
        FROM cliente cl
        INNER JOIN usuario u ON cl.id_usuario = u.id_usuario
        INNER JOIN persona p ON u.id_persona = p.id_persona
        WHERE cl.id_cliente = :id_cliente
    ";
    
    $stmtCliente = $conexion->prepare($sqlCliente);
    $stmtCliente->bindParam(':id_cliente', $id_cliente);
    $stmtCliente->execute();
    $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

    // Obtener abonos para esta factura
    $sqlAbonos = "
        SELECT a.monto, a.metodo_pago, a.observaciones, a.fecha_abono
        FROM abonos a
        INNER JOIN credito c ON a.id_credito = c.id_credito
        WHERE c.id_cliente = :id_cliente 
        AND DATE(c.fecha_cre) = :fecha_factura
        ORDER BY a.fecha_abono DESC
    ";
    
    $stmtAbonos = $conexion->prepare($sqlAbonos);
    $stmtAbonos->bindParam(':id_cliente', $id_cliente);
    $stmtAbonos->bindParam(':fecha_factura', $fecha_factura);
    $stmtAbonos->execute();
    $abonos = $stmtAbonos->fetchAll(PDO::FETCH_ASSOC);

    // Calcular totales
    $total_factura = array_sum(array_column($productos, 'subtotal'));
    $total_abonado = array_sum(array_column($abonos, 'monto'));
    $saldo_pendiente = $total_factura - $total_abonado;

    $response = [
        'id_cliente' => $id_cliente,
        'cliente' => $cliente['cliente'] ?? 'Cliente no encontrado',
        'fecha_factura' => $fecha_factura,
        'productos' => $productos,
        'abonos' => $abonos,
        'resumen' => [
            'total_factura' => $total_factura,
            'total_abonado' => $total_abonado,
            'saldo_pendiente' => $saldo_pendiente
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener detalle: ' . $e->getMessage()]);
}
?>