<?php
require_once __DIR__ . '/../../../BBDD/BBDD.php';
$conexion = Conexion::getConnection();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_producto'] ?? null;

    if (!$id) {
        $response['message'] = 'ID de producto es obligatorio.';
        echo json_encode($response);
        exit;
    }

    try {
        $sql = "UPDATE inventario SET activo = FALSE WHERE id_producto = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);

        $response['success'] = true;
        $response['message'] = 'Producto desactivado con éxito.';

    } catch (PDOException $e) {
        $response['message'] = 'Error de base de datos: ' . $e->getMessage();
    }

} else {
    $response['message'] = 'Método de solicitud no válido.';
}

echo json_encode($response);
?>