<?php
require_once __DIR__ . '/../../../BBDD/BBDD.php';
$conexion = Conexion::getConnection();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'historial' => []];

try {
    $id = isset($_GET['id_producto']) ? (int)$_GET['id_producto'] : 0;
    if ($id <= 0) throw new Exception('ID de producto inválido');

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