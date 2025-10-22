<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$response = ['success' => false, 'inventario' => [], 'message' => ''];

try {
    // Ser explícito con las columnas es mejor práctica
    $sql = "SELECT 
                id_producto, 
                nombre_produc, 
                caja_produc, 
                cantidad_caja, 
                precio_caja, 
                precio_produc, 
                precio_venta, -- Asegurarse que esta columna exista en la BBDD
                activo 
            FROM inventario 
            ORDER BY nombre_produc ASC";
            
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    
    $response['inventario'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['success'] = true;

} catch (PDOException $e) {
    // Si la columna precio_venta no existe, se capturará el error aquí
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
}

echo json_encode($response);
?>