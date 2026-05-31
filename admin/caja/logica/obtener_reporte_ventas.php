<?php
require_once __DIR__ . '/../../../BBDD/BBDD.php';
header('Content-Type: application/json');

function validarFecha($f) {
    return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $f);
}

try {
    $start = $_GET['start_date'] ?? null;
    $end = $_GET['end_date'] ?? null;
    if (!$start || !$end || !validarFecha($start) || !validarFecha($end)) {
        echo json_encode(['success' => false, 'error' => 'Parámetros inválidos: start_date y end_date (YYYY-MM-DD)']);
        exit;
    }

    $params = [
        ':start' => $start . ' 00:00:00',
        ':end' => $end . ' 23:59:59'
    ];

    // Total vendido (USD)
    $sqlTotal = "SELECT COALESCE(SUM(v.total),0) AS total_usd FROM ventas v WHERE v.fecha_venta BETWEEN :start AND :end";
    $stmtTotal = $conexion->prepare($sqlTotal);
    $stmtTotal->execute($params);
    $total = $stmtTotal->fetch(PDO::FETCH_ASSOC);

    // Agregación por producto
    $sqlAgg = "SELECT v.id_producto, i.nombre_produc, COALESCE(SUM(v.cantidad),0) AS cantidad, COALESCE(SUM(v.total),0) AS total_usd
               FROM ventas v
               LEFT JOIN inventario i ON v.id_producto = i.id_producto
               WHERE v.fecha_venta BETWEEN :start AND :end
               GROUP BY v.id_producto, i.nombre_produc";
    $stmtAgg = $conexion->prepare($sqlAgg);
    $stmtAgg->execute($params);
    $productos = $stmtAgg->fetchAll(PDO::FETCH_ASSOC);

    // Top y bottom por cantidad
    $top = null; $bottom = null;
    if (!empty($productos)) {
        // Ordenar en PHP por cantidad
        usort($productos, function($a, $b) { return ($b['cantidad'] <=> $a['cantidad']); });
        $top = $productos[0];
        $bottom = $productos[count($productos)-1];
    }

    echo json_encode([
        'success' => true,
        'rango' => ['start_date' => $start, 'end_date' => $end],
        'total_usd' => floatval($total['total_usd'] ?? 0),
        'top_producto' => $top,
        'bottom_producto' => $bottom,
        'productos' => $productos
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error en reporte: ' . $e->getMessage()]);
}
?>