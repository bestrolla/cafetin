<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$response = ['success' => false, 'inventario' => [], 'message' => ''];

try {
    // Obtener parámetros de filtro
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : 'todo';
    
    // Construir la consulta base
    $sql = "SELECT 
                id_producto, 
                nombre_produc, 
                caja_produc, 
                cantidad_caja, 
                precio_caja, 
                precio_produc, 
                precio_venta,
                activo 
            FROM inventario";
    
    $conditions = [];
    $params = [];
    
    // Filtro por búsqueda de texto
    if (!empty($q)) {
        $conditions[] = "nombre_produc LIKE :q";
        $params[':q'] = '%' . $q . '%';
    }
    
    // Filtro por estado activo/inactivo
    if ($status === 'activo') {
        $conditions[] = "activo = 1";
    } elseif ($status === 'inactivo') {
        $conditions[] = "activo = 0";
    }
    // Si $status es 'todo', no agregamos condición (muestra todos)
    
    // Agregar condiciones WHERE si existen
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY nombre_produc ASC";
            
    $stmt = $conexion->prepare($sql);
    
    // Ejecutar con parámetros si los hay
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    
    $response['inventario'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['success'] = true;

} catch (PDOException $e) {
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
}

echo json_encode($response);
?>