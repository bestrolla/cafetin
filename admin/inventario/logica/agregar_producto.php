<?php
require_once __DIR__ . '/../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre_produc'] ?? null;
    $cajas = $_POST['caja_produc'] ?? 0;
    $unidades_caja = $_POST['cantidad_caja'] ?? 0;
    $precio_caja = $_POST['precio_caja'] ?? 0;
    $precio_unidad = $_POST['precio_produc'] ?? null;
    $precio_venta = $_POST['precio_venta'] ?? null; // Nuevo campo
    $cantidad_total = $_POST['cantidad_total'] ?? null;

    // Actualizar validación
    if (!$nombre || !$precio_unidad || $precio_venta === null) {
        $response['message'] = 'Nombre, precio por unidad y precio de venta son obligatorios.';
        echo json_encode($response);
        exit;
    }

    if ($cantidad_total === null || $cantidad_total === '') {
        $cantidad_total = (float)$cajas * (float)$unidades_caja;
    }

    $cantidad_total = (float)$cantidad_total;

    try {
        // Verificar si el producto ya existe
        $sql_check = "SELECT COUNT(*) FROM inventario WHERE nombre_produc = :nombre";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->execute(['nombre' => $nombre]);
        $count = $stmt_check->fetchColumn();

        if ($count > 0) {
            $response['message'] = 'Este producto ya está registrado. Por favor, elige un nombre diferente.';
            echo json_encode($response);
            exit;
        }

        // Si no existe, proceder con la inserción
        $sql_insert = "INSERT INTO inventario (nombre_produc, caja_produc, cantidad_caja, precio_caja, precio_produc, precio_venta, cantidad_total) VALUES (:nombre, :cajas, :unidades_caja, :precio_caja, :precio_unidad, :precio_venta, :cantidad_total)";
        $stmt_insert = $conexion->prepare($sql_insert);
        $stmt_insert->execute([
            ':nombre' => $nombre,
            ':cajas' => $cajas,
            ':unidades_caja' => $unidades_caja,
            ':precio_caja' => $precio_caja,
            ':precio_unidad' => $precio_unidad,
            ':precio_venta' => $precio_venta,
            ':cantidad_total' => $cantidad_total
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