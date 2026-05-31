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
        $isVercel = false;
        if (!function_exists('isVercelRuntime')) {
            require_once __DIR__ . '/../acces/vercel_env.php';
        }
        $isVercel = isVercelRuntime();

        $host = $this->getEnvVar('MYSQL_HOST');
        $port = $this->getEnvVar('MYSQL_PORT');
        $dbname = $this->getEnvVar('MYSQL_DATABASE');
        $username = $this->getEnvVar('MYSQL_USER');
        $pass = $this->getEnvVar('MYSQL_PASSWORD');

        if ($isVercel) {
            $this->host = $host ?: 'sql103.infinityfree.com';
            $this->port = $port ?: '3306';
            $this->dbname = $dbname ?: 'if0_41909456_cafetin';
            $this->username = $username ?: 'if0_41909456';
            $this->password = $pass !== false ? $pass : 'udWAvVG9sN';
        } else {
            $this->host = $host ?: '127.0.0.1';
            $this->port = $port ?: '3306';
            $this->dbname = $dbname ?: 'cafetin';
            $this->username = $username ?: 'root';
            $this->password = $pass !== false ? $pass : '';
        }
    }

    private function getEnvVar(string $name, $default = false) {
        $value = getenv($name);
        if ($value !== false) {
            return $value;
        }
        if (isset($_ENV[$name])) {
            return $_ENV[$name];
        }
        if (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }
        return $default;
    }

    public static function getConnection(): PDO {
        return (new self())->conectar();
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
