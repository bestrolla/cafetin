<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'historial' => []];

try {
    $id = isset($_GET['id_producto']) ? (int)$_GET['id_producto'] : 0;
    if ($id <= 0) throw new Exception('ID de producto inválido');

    // Asegurar que la tabla exista (por si se consulta antes de agregar)
    $conexion->exec("CREATE TABLE IF NOT EXISTS historial_producto (
        id_historial INT AUTO_INCREMENT PRIMARY KEY,
        id_producto INT NOT NULL,
        fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        cajas_agregar INT NOT NULL,
        unidades_por_caja INT NOT NULL,
        unidades_sueltas_agregar INT NOT NULL,
        unidades_agregadas_total INT NOT NULL,
        precio_venta_usd DECIMAL(10,2) NOT NULL,
        precio_venta_bs DECIMAL(12,2) NOT NULL,
        tasa_dolar DECIMAL(10,2) NOT NULL,
        observacion VARCHAR(255) NULL,
        INDEX (id_producto)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $stmt = $conexion->prepare("SELECT 
        id_historial, fecha_registro, cajas_agregar, unidades_por_caja, unidades_sueltas_agregar, unidades_agregadas_total, precio_venta_usd, precio_venta_bs, tasa_dolar, observacion
        FROM historial_producto WHERE id_producto = :id ORDER BY fecha_registro DESC, id_historial DESC");
    $stmt->execute([':id' => $id]);
    $response['historial'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>