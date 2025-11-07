<?php
$host = 'localhost';
$dbname = 'cafetin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "UPDATE credito SET fecha_factura = fecha_cre WHERE fecha_factura IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    echo "Columna fecha_factura actualizada correctamente.";

} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>