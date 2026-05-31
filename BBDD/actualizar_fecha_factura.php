<?php
require_once __DIR__ . '/BBDD.php';

try {
    $pdo = Conexion::getConnection();

    $sql = "UPDATE credito SET fecha_factura = fecha_cre WHERE fecha_factura IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    echo "Columna fecha_factura actualizada correctamente.";

} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>