<?php
require_once __DIR__ . '/../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_producto'] ?? null;
    $nombre = $_POST['nombre_produc'] ?? null;
    $cajas = $_POST['caja_produc'] ?? 0;
    $unidades_caja = $_POST['cantidad_caja'] ?? 0;
    $precio_caja = $_POST['precio_caja'] ?? 0;
    $precio_unidad = $_POST['precio_produc'] ?? null;
    $precio_venta = $_POST['precio_venta'] ?? null;
    $cantidad_total = $_POST['cantidad_total'] ?? null;
    $activo = isset($_POST['activo']) ? 1 : 0; // Manejar el estado desde el formulario

    if (!$id || !$nombre || !$precio_unidad || $precio_venta === null) {
        $response['message'] = 'ID, nombre, precio por unidad y precio de venta son obligatorios.';
        echo json_encode($response);
        exit;
    }

    if ($cantidad_total === null || $cantidad_total === '') {
        $cantidad_total = (float)$cajas * (float)$unidades_caja;
    }

    $cantidad_total = (float)$cantidad_total;

    try {
        $sql = "UPDATE inventario SET 
                    nombre_produc = :nombre, 
                    caja_produc = :cajas, 
                    cantidad_caja = :unidades_caja, 
                    precio_caja = :precio_caja, 
                    precio_produc = :precio_unidad, 
                    precio_venta = :precio_venta,
                    cantidad_total = :cantidad_total,
                    activo = :activo
                WHERE id_producto = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':nombre' => $nombre,
            ':cajas' => $cajas,
            ':unidades_caja' => $unidades_caja,
            ':precio_caja' => $precio_caja,
            ':precio_unidad' => $precio_unidad,
            ':precio_venta' => $precio_venta,
            ':cantidad_total' => $cantidad_total,
            ':activo' => $activo
        ]);

        $response['success'] = true;
        $response['message'] = 'Producto actualizado con éxito.';

    } catch (PDOException $e) {
        $response['message'] = 'Error de base de datos: ' . $e->getMessage();
    }

} else {
    $response['message'] = 'Método de solicitud no válido.';
}

echo json_encode($response);
?>