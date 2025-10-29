<?php
// ===============================================
// 📦 CONEXIÓN A LA BASE DE DATOS - cafetin
// Autor: Ángel
// ===============================================

class Conexion {
    private $host = 'localhost';
    private $dbname = 'cafetin'; // cambia al nombre de tu base de datos
    private $username = 'root';
    private $password = ''; // cambia si tu MySQL tiene contraseña
    private $conexion;

    public function conectar() {
        try {
            // Conexión usando PDO
            $this->conexion = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8", 
                $this->username, 
                $this->password
            );

            // Modo de errores: lanza excepciones (recomendado)
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $this->conexion;

        } catch (PDOException $e) {
            // Si hay error, muestra el mensaje
            throw new Exception("❌ Error de conexión: " . $e->getMessage());
        }
    }

    public function desconectar() {
        $this->conexion = null;
    }
}

// Mantener compatibilidad con código existente que usa $conexion directamente
try {
    $conexionObj = new Conexion();
    $conexion = $conexionObj->conectar();
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}
?>
