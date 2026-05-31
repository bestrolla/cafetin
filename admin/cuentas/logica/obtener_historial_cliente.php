<?php
// Incluir sistema de control de acceso
require_once __DIR__ . '/../../../acces/auth_check.php';
require_once __DIR__ . '/../../../BBDD/BBDD.php';
$conexion = Conexion::getConnection();

if (!esAdmin()) {
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

header('Content-Type: application/json');

try {

    $id_cliente = $_GET['id_cliente'] ?? null;
    if (!$id_cliente) {
        echo json_encode(['error' => 'Parámetro requerido: id_cliente']);
        exit;
    }

    // Productos por fecha
    $sqlProd = "
        SELECT 
            DATE(c.fecha_cre) as fecha,
            c.id_credito,
            i.nombre_produc as producto,
            c.cantidad,
            c.total as subtotal
        FROM credito c
        INNER JOIN inventario i ON c.id_producto = i.id_producto
        WHERE c.id_cliente = :id_cliente 
          AND c.estado IN ('pendiente','parcial')
        ORDER BY c.fecha_cre ASC
    ";
    $stmtProd = $conexion->prepare($sqlProd);
    $stmtProd->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmtProd->execute();
    $rows = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar por fecha y calcular totales
    $historial = [];
    foreach ($rows as $r) {
        $f = $r['fecha'];
        if (!isset($historial[$f])) {
            $historial[$f] = [
                'fecha' => $f,
                'productos' => [],
                'total_factura' => 0,
                'total_abonado' => 0,
                'saldo_pendiente' => 0,
                'ids_credito' => []
            ];
        }
        $historial[$f]['productos'][] = [
            'producto' => $r['producto'],
            'cantidad' => (int)$r['cantidad'],
            'subtotal' => (float)$r['subtotal']
        ];
        $historial[$f]['ids_credito'][] = (int)$r['id_credito'];
        $historial[$f]['total_factura'] += (float)$r['subtotal'];
    }

    // Abonos por fecha (sumando abonos de créditos de ese cliente y fecha de creación)
    $sqlAbonos = "
        SELECT DATE(c.fecha_cre) as fecha, COALESCE(SUM(a.monto),0) as total_abonos
        FROM abonos a
        INNER JOIN credito c ON a.id_credito = c.id_credito
        WHERE c.id_cliente = :id_cliente
          AND c.estado IN ('pendiente','parcial')
        GROUP BY DATE(c.fecha_cre)
    ";
    $stmtAb = $conexion->prepare($sqlAbonos);
    $stmtAb->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmtAb->execute();
    $abRows = $stmtAb->fetchAll(PDO::FETCH_ASSOC);

    foreach ($abRows as $ab) {
        $f = $ab['fecha'];
        if (isset($historial[$f])) {
            $historial[$f]['total_abonado'] = (float)$ab['total_abonos'];
        }
    }

    // Calcular saldos
    foreach ($historial as $f => $h) {
        $historial[$f]['saldo_pendiente'] = $h['total_factura'] - $h['total_abonado'];
    }

    // Convertir a lista ordenada por fecha desc
    // fijar id_credito principal (mínimo) por fecha para acciones
    foreach ($historial as $f => $h) {
        $historial[$f]['id_credito_principal'] = !empty($h['ids_credito']) ? min($h['ids_credito']) : null;
    }

    $historialList = array_values($historial);
    usort($historialList, function($a, $b) { return strcmp($b['fecha'], $a['fecha']); });

    echo json_encode($historialList);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
}
?>