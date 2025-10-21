<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT 
                cr.id_credito, 
                p_cli.nombre as cliente_nombre, 
                i.nombre_produc as producto_nombre, 
                cr.cantidad, 
                cr.total, 
                cr.fecha_cre, 
                cr.estado
            FROM credito cr
            LEFT JOIN cliente cl ON cr.id_cliente = cl.id_cliente
            LEFT JOIN usuario u_cli ON cl.id_usuario = u_cli.id_usuario
            LEFT JOIN persona p_cli ON u_cli.id_persona = p_cli.id_persona
            LEFT JOIN inventario i ON cr.id_producto = i.id_producto
            ORDER BY cr.fecha_cre DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $deudas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($deudas);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener las deudas: ' . $e->getMessage()]);
}
?>