<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: application/json');

try {
    $sql = "SELECT id_producto, nombre_produc FROM inventario WHERE activo = 1 ORDER BY nombre_produc ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'productos' => $productos]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al obtener productos: ' . $e->getMessage()]);
}
?>