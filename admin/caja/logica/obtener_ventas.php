<?php
require_once __DIR__ . '/../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

try {
    $sql = "SELECT 
                v.id_venta, 
                p_cli.nombre as cliente_nombre,
                p_cli.apellido as cliente_apellido,
                p_caj.nombre as cajero_nombre,
                p_caj.apellido as cajero_apellido, 
                i.nombre_produc as producto_nombre, 
                v.cantidad, 
                v.total, 
                v.fecha_venta
            FROM ventas v
            LEFT JOIN cliente cl ON v.id_cliente = cl.id_cliente
            LEFT JOIN usuario u_cli ON cl.id_usuario = u_cli.id_usuario
            LEFT JOIN persona p_cli ON u_cli.id_persona = p_cli.id_persona
            LEFT JOIN cajero c ON v.id_cajero = c.id_cajero
            LEFT JOIN usuario u_caj ON c.id_usuario = u_caj.id_usuario
            LEFT JOIN persona p_caj ON u_caj.id_persona = p_caj.id_persona
            LEFT JOIN inventario i ON v.id_producto = i.id_producto";

    $params = [];
    if ($startDate && $endDate) {
        $sql .= " WHERE v.fecha_venta BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $startDate . ' 00:00:00';
        $params[':end_date'] = $endDate . ' 23:59:59';
    }

    $sql .= " ORDER BY v.fecha_venta DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($ventas);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener las ventas: ' . $e->getMessage()]);
}
?>