<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT * FROM inventario ORDER BY nombre_produc ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($inventario);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener el inventario: ' . $e->getMessage()]);
}
?>