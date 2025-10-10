<?php
// ===============================================
// 📦 CONEXIÓN A LA BASE DE DATOS - gestion_inventario
// Autor: Ángel
// ===============================================

// Parámetros de conexión
$host = 'localhost';
$dbname = 'gestion_inventario'; // cambia al nombre de tu base de datos
$username = 'root';
$password = ''; // cambia si tu MySQL tiene contraseña

try {
    // Conexión usando PDO
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Modo de errores: lanza excepciones (recomendado)
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Si todo va bien, no muestra nada (silencioso)
    // echo "✅ Conexión exitosa a la base de datos";

} catch (PDOException $e) {
    // Si hay error, muestra el mensaje
    echo "❌ Error de conexión: " . $e->getMessage();
    exit;
}
?>
