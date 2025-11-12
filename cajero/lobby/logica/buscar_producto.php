<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $busqueda = $_GET['q'] ?? '';
    
    try {
        $sql = "SELECT 
                    id_producto, 
                    nombre_produc, 
                    precio_venta,
                    cantidad_total,
                    (COALESCE(cantidad_total,0)) AS stock_disponible
                FROM inventario 
                WHERE activo = 1 
                AND (nombre_produc LIKE ? OR CAST(id_producto AS CHAR) LIKE ?)
                ORDER BY nombre_produc ASC";
        
        $stmt = $conexion->prepare($sql);
        $terminoBusqueda = "%$busqueda%";
        $stmt->execute([$terminoBusqueda, $terminoBusqueda]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'productos' => $productos
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al buscar productos: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>