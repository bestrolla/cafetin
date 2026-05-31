<?php
require_once __DIR__ . '/../../../BBDD/BBDD.php';
header('Content-Type: application/json');

function validarFecha($f) {
    return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $f);
}

try {
    $start = $_GET['start_date'] ?? null;
    $end = $_GET['end_date'] ?? null;
    $period = $_GET['period'] ?? 'dia'; // dia|semana|mes|anio
    $productoId = isset($_GET['producto_id']) && $_GET['producto_id'] !== '' ? (int)$_GET['producto_id'] : null;

    if (!$start || !$end || !validarFecha($start) || !validarFecha($end)) {
        echo json_encode(['success' => false, 'error' => 'Parámetros inválidos: start_date y end_date (YYYY-MM-DD)']);
        exit;
    }

    $params = [
        ':start' => $start . ' 00:00:00',
        ':end' => $end . ' 23:59:59'
    ];

    $productoFilterSql = '';
    if ($productoId) {
        $productoFilterSql = ' AND v.id_producto = :producto_id';
        $params[':producto_id'] = $productoId;
    }

    // Construir consulta según periodo
    $selectExpr = '';
    $groupExpr = '';
    $labelExpr = '';
    switch ($period) {
        case 'dia':
            $selectExpr = 'DATE(v.fecha_venta) AS bucket';
            $groupExpr = 'DATE(v.fecha_venta)';
            $labelExpr = 'bucket';
            break;
        case 'semana':
            // Usar YEARWEEK para agrupar por semana ISO (modo 1)
            $selectExpr = 'YEARWEEK(v.fecha_venta, 1) AS bucket';
            $groupExpr = 'YEARWEEK(v.fecha_venta, 1)';
            $labelExpr = 'bucket';
            break;
        case 'mes':
            $selectExpr = 'DATE_FORMAT(v.fecha_venta, "%Y-%m") AS bucket';
            $groupExpr = 'YEAR(v.fecha_venta), MONTH(v.fecha_venta)';
            $labelExpr = 'bucket';
            break;
        case 'anio':
            $selectExpr = 'YEAR(v.fecha_venta) AS bucket';
            $groupExpr = 'YEAR(v.fecha_venta)';
            $labelExpr = 'bucket';
            break;
        default:
            $selectExpr = 'DATE(v.fecha_venta) AS bucket';
            $groupExpr = 'DATE(v.fecha_venta)';
            $labelExpr = 'bucket';
            $period = 'dia';
            break;
    }

    // Sumamos cantidades vendidas (unidades) por bucket
    $sql = "SELECT $selectExpr, COALESCE(SUM(v.cantidad), 0) AS unidades
            FROM ventas v
            WHERE v.fecha_venta BETWEEN :start AND :end
            $productoFilterSql
            GROUP BY $groupExpr
            ORDER BY $groupExpr ASC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $series = [];
    $totalUnidades = 0;
    foreach ($rows as $r) {
        $labels[] = (string)$r[$labelExpr];
        $val = (int)($r['unidades'] ?? 0);
        $series[] = $val;
        $totalUnidades += $val;
    }

    echo json_encode([
        'success' => true,
        'period' => $period,
        'rango' => ['start_date' => $start, 'end_date' => $end],
        'labels' => $labels,
        'series' => $series,
        'total_unidades' => $totalUnidades
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error en gráfico: ' . $e->getMessage()]);
}
?>