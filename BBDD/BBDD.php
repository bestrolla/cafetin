<?php
// ===============================================
// Conexión MySQL - cafetin (esquema: cafetin (10).sql)
// ===============================================

class Conexion {
    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;
    private $conexion;

    public function __construct() {
        $this->host = getenv('MYSQL_HOST') ?: 'sql103.infinityfree.com';
        $this->port = getenv('MYSQL_PORT') ?: '3306';
        $this->dbname = getenv('MYSQL_DATABASE') ?: 'if0_41909456_cafetin';
        $this->username = getenv('MYSQL_USER') ?: 'if0_41909456';
        $pass = getenv('MYSQL_PASSWORD');
        $this->password = $pass !== false ? $pass : 'udWAvVG9sN';
    }

    public function conectar() {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4";
            $this->conexion = new PDO($dsn, $this->username, $this->password);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $this->conexion;
        } catch (PDOException $e) {
            throw new Exception('Error de conexión: ' . $e->getMessage());
        }
    }

    public function desconectar() {
        $this->conexion = null;
    }
}

function normalizarTextoNombre($texto) {
    $texto = trim((string) $texto);
    if ($texto === '') {
        return $texto;
    }
    $texto = preg_replace('/\s+/u', ' ', $texto);
    if (function_exists('mb_convert_case')) {
        return mb_convert_case($texto, MB_CASE_TITLE, 'UTF-8');
    }
    return ucwords(strtolower($texto));
}

try {
    require_once __DIR__ . '/../acces/vercel_env.php';
    $conexionObj = new Conexion();
    $conexion = $conexionObj->conectar();
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}
