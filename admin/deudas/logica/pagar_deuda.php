<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_credito = $_POST['id_credito'] ?? null;

    if (!$id_credito) {
        $response['message'] = 'ID de crédito es obligatorio.';
        echo json_encode($response);
        exit;
    }

    try {
        $sql = "UPDATE credito SET estado = 'pagado', fecha_pago = NOW() WHERE id_credito = :id_credito";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id_credito' => $id_credito]);

        $response['success'] = true;
        $response['message'] = 'Deuda marcada como pagada.';

    } catch (PDOException $e) {
        $response['message'] = 'Error de base de datos: ' . $e->getMessage();
    }

} else {
    $response['message'] = 'Método de solicitud no válido.';
}

echo json_encode($response);
?>