<?php
// ===============================================
// 📦 CONEXIÓN A LA BASE DE DATOS - cafetin
// Autor: Ángel
// ===============================================

class Conexion {
    private $driver = 'mysql';
    private $host = 'localhost';
    private $dbname = 'cafetin';
    private $username = 'root';
    private $password = '';
    private $sqlite_path;
    private $conexion;

    public function __construct() {
        $this->sqlite_path = __DIR__ . '/cafetin.db';
    }

    public function conectar() {
        try {
            if ($this->driver === 'sqlite') {
                $this->conexion = new PDO('sqlite:' . $this->sqlite_path);
                $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $this->conexion->exec('PRAGMA foreign_keys = ON');
                return $this->conexion;
            }

            $this->conexion = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->username,
                $this->password
            );
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $this->conexion;

        } catch (PDOException $e) {
            throw new Exception("❌ Error de conexión: " . $e->getMessage());
        }
    }

    public function usarSqlite($rutaDb = null) {
        $this->driver = 'sqlite';
        if ($rutaDb) {
            $this->sqlite_path = $rutaDb;
        }
    }

    public function desconectar() {
        $this->conexion = null;
    }
}

// Mantener compatibilidad con código existente que usa $conexion directamente
try {
    $conexionObj = new Conexion();
    if (isset($_ENV['CAFETIN_DB_DRIVER']) && $_ENV['CAFETIN_DB_DRIVER'] === 'sqlite') {
        $ruta = isset($_ENV['CAFETIN_SQLITE_PATH']) ? $_ENV['CAFETIN_SQLITE_PATH'] : null;
        $conexionObj->usarSqlite($ruta);
    } else {
        $defaultSqlitePath = __DIR__ . '/cafetin.db';
        if (extension_loaded('pdo_sqlite')) {
            if (isset($_ENV['CAFETIN_SQLITE_PATH']) && $_ENV['CAFETIN_SQLITE_PATH']) {
                $conexionObj->usarSqlite($_ENV['CAFETIN_SQLITE_PATH']);
            } else if (file_exists($defaultSqlitePath)) {
                $conexionObj->usarSqlite($defaultSqlitePath);
            }
        }
    }
    $conexion = $conexionObj->conectar();
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}
?>
