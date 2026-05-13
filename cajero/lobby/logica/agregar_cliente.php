<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $_POST['cedula'] ?? null;
    $nombre = $_POST['nombre'] ?? null;
    $apellido = $_POST['apellido'] ?? null;
    $telefono = $_POST['telefono'] ?? null;
    $alias = $_POST['alias'] ?? null;

    if ($cedula && $nombre && $apellido) {
        try {
            $sql = "INSERT INTO clientes (cedula, nombre, apellido, telefono, alias) VALUES (:cedula, :nombre, :apellido, :telefono, :alias)";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':cedula', $cedula, PDO::PARAM_STR);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':apellido', $apellido, PDO::PARAM_STR);
            $stmt->bindParam(':telefono', $telefono, PDO::PARAM_STR);
            $stmt->bindParam(':alias', $alias, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Cliente registrado con éxito.';
            } else {
                $response['message'] = 'Error al registrar el cliente.';
            }
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Error de entrada duplicada
                $response['message'] = 'La cédula ingresada ya existe.';
            } else {
                $response['message'] = 'Error de base de datos: ' . $e->getMessage();
            }
        }
    } else {
        $response['message'] = 'Todos los campos obligatorios deben ser completados.';
    }
} else {
    $response['message'] = 'Método de solicitud no válido.';
}

echo json_encode($response);
?>