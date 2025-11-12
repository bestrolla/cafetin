<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

try {
    $id_venta = $_GET['id_venta'] ?? null;
    if (!$id_venta) {
        echo json_encode(['error' => 'Parámetro requerido: id_venta']);
        exit;
    }

    $sql = "SELECT 
                v.id_venta,
                v.id_cliente,
                i.nombre_produc AS producto,
                v.cantidad,
                v.total AS subtotal,
                v.fecha_venta,
                CONCAT(p_cli.nombre, ' ', p_cli.apellido) AS cliente,
                p_cli.nombre AS cliente_nombre,
                p_cli.apellido AS cliente_apellido,
                p_caj.nombre AS cajero_nombre,
                p_caj.apellido AS cajero_apellido
            FROM ventas v
            LEFT JOIN cliente cl ON v.id_cliente = cl.id_cliente
            LEFT JOIN usuario u_cli ON cl.id_usuario = u_cli.id_usuario
            LEFT JOIN persona p_cli ON u_cli.id_persona = p_cli.id_persona
            LEFT JOIN cajero c ON v.id_cajero = c.id_cajero
            LEFT JOIN usuario u_caj ON c.id_usuario = u_caj.id_usuario
            LEFT JOIN persona p_caj ON u_caj.id_persona = p_caj.id_persona
            LEFT JOIN inventario i ON v.id_producto = i.id_producto
            WHERE v.id_venta = :id_venta";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['error' => 'Venta no encontrada']);
        exit;
    }

    // Obtener todos los ítems del mismo cliente y misma fecha (día) para simular factura
    $sqlItems = "SELECT 
                    i.nombre_produc AS producto,
                    v.cantidad,
                    v.total AS subtotal,
                    v.fecha_venta
                FROM ventas v
                LEFT JOIN inventario i ON v.id_producto = i.id_producto
                WHERE v.id_cliente = :id_cliente
                  AND DATE(v.fecha_venta) = DATE(:fecha_venta)
                ORDER BY v.fecha_venta ASC";

    $stmtItems = $conexion->prepare($sqlItems);
    $stmtItems->bindParam(':id_cliente', $row['id_cliente'], PDO::PARAM_INT);
    $stmtItems->bindParam(':fecha_venta', $row['fecha_venta']);
    $stmtItems->execute();
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    $cajeroCompleto = trim(($row['cajero_nombre'] ?? '') . ' ' . ($row['cajero_apellido'] ?? '')) ?: null;
    $response = [
        'id_venta' => (int)$row['id_venta'],
        'cliente' => $row['cliente'] ?? trim(($row['cliente_nombre'] ?? '') . ' ' . ($row['cliente_apellido'] ?? '')),
        'cajero' => $cajeroCompleto,
        'fecha_venta' => $row['fecha_venta'] ?? null,
        'items' => array_map(function($it) {
            return [
                'producto' => $it['producto'] ?? 'N/A',
                'cantidad' => (int)($it['cantidad'] ?? 0),
                'subtotal' => (float)($it['subtotal'] ?? 0),
                'fecha_venta' => $it['fecha_venta'] ?? null,
            ];
        }, $items)
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener detalle de venta: ' . $e->getMessage()]);
}
?>