<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre_produc'] ?? null;
    $cajas = $_POST['caja_produc'] ?? 0;
    $unidades_caja = $_POST['cantidad_caja'] ?? 0;
    $precio_caja = $_POST['precio_caja'] ?? 0;
    $precio_unidad = $_POST['precio_produc'] ?? null;

    if (!$nombre || !$precio_unidad) {
        $response['message'] = 'Nombre y precio por unidad son obligatorios.';
        echo json_encode($response);
        exit;
    }

    try {
        $sql = "INSERT INTO inventario (nombre_produc, caja_produc, cantidad_caja, precio_caja, precio_produc) VALUES (:nombre, :cajas, :unidades_caja, :precio_caja, :precio_unidad)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':cajas' => $cajas,
            ':unidades_caja' => $unidades_caja,
            ':precio_caja' => $precio_caja,
            ':precio_unidad' => $precio_unidad
        ]);

        $response['success'] = true;
        $response['message'] = 'Producto agregado con éxito.';

    } catch (PDOException $e) {
        $response['message'] = 'Error de base de datos: ' . $e->getMessage();
    }

} else {
    $response['message'] = 'Método de solicitud no válido.';
}

echo json_encode($response);
?>