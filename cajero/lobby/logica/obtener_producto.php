<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT 
                id_producto, 
                nombre_produc, 
                precio_venta,
                cantidad_total,
                (COALESCE(cantidad_total,0)) AS stock_disponible
            FROM inventario 
            WHERE activo = TRUE 
            ORDER BY nombre_produc ASC";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'productos' => $productos
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener productos: ' . $e->getMessage()
    ]);
}
?>